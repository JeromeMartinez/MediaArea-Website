<?php

namespace MediaBinBundle\Controller;

use UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Attribute\Route;
use MediaBinBundle\Entity\Bin;
use MediaBinBundle\Lib\KeyGenerator;
use MediaBinBundle\Lib\XMLValidator;
use MediaBinBundle\Lib\File\File;
use MediaBinBundle\Model\BinInterface;
use UserBundle\Controller\GuestControllerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[Route('/api/v1/MediaBin')]
class ApiController extends AbstractController implements GuestControllerInterface
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    #[Route('', name: 'mediabin_api_new', methods: ['PUT'])]
    public function newAction(Request $request, File $binFile)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            return new JsonResponse(['error' => 'User not logged'], 401);
        }

        // Get the XML
        $xml = base64_decode($request->request->get('xml'));
        // Get the expiration date
        $expirationDate = $this->getExpirationDate($request->request->get('expiration'));

        // Limit xml to 256kB
        if (256 * 1024 < strlen($xml)) {
            return new JsonResponse(['error' => 'XML size is to big'], 500);
        }

        // Check XML validity
        $xmlValidator = new XMLValidator();
        if (!$xmlValidator->isValid(zlib_decode($xml), BinInterface::FORMAT_MEDIAINFO)) {
            return new JsonResponse(['error' => 'The xml is not valid'], 500);
        }

        // Generate unique hash
        $binRepository = $this->doctrine->getRepository(Bin::class);
        do {
            $key = KeyGenerator::getKey();
        } while ($binRepository->findOneBy(['hash' => $key]));

        // Save MediaBin to DB
        $bin = new Bin();
        $bin
            ->setUser($this->getUser())
            ->setHash($key)
            ->setTitle($request->request->get('title'))
            ->setFormat(BinInterface::FORMAT_MEDIAINFO)
            ->setAnonymize((int) $request->request->get('anonymize') ? 1 : 0)
            ->setVisibility((int) $request->request->get('visibility') ? 1 : 0)
            ->setExpireAt($expirationDate);

        $em = $this->doctrine->getManager();
        $em->persist($bin);
        $em->flush();

        // Save MediaBin file
        $binFile->save($bin->getHash(), $xml);

        return new JsonResponse(['url' => $this->generateUrl(
            'mediabin_show',
            ['hash' => $bin->getHash()],
            UrlGeneratorInterface::ABSOLUTE_URL
        )]);
    }

    #[Route('/{hash}', name: 'mediabin_api_update', requirements: ['hash' => '[a-zA-Z0-9]{8}'], methods: ['POST'])]
    public function updateAction(Request $request, #[MapEntity(mapping: ['hash' => 'hash'])] Bin $bin)
    {
        if (true !== $checkOwner = $this->checkBinOwner($this->getUser(), $bin->getUser())) {
            return $checkOwner;
        }

        if (null !== $request->request->get('title')) {
            $bin->setTitle($request->request->get('title'));
        }

        if (null !== $request->request->get('anonymize')) {
            $bin->setAnonymize((int) $request->request->get('anonymize') ? 1 : 0);
        }

        if (null !== $request->request->get('visibility')) {
            $bin->setVisibility((int) $request->request->get('visibility') ? 1 : 0);
        }

        if ('false' != $request->request->get('expiration')) {
            $bin->setExpireAt($this->getExpirationDate($request->request->get('expiration')));
        }

        $em = $this->doctrine->getManager();
        $em->persist($bin);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route(
        '/cancel/{hash}',
        name: 'mediabin_api_cancel_expiration',
        requirements: ['hash' => '[a-zA-Z0-9]{8}'],
        methods: ['POST'],
    )]
    public function cancelExpirationAction(#[MapEntity(mapping: ['hash' => 'hash'])] Bin $bin)
    {
        if (true !== $checkOwner = $this->checkBinOwner($this->getUser(), $bin->getUser())) {
            return $checkOwner;
        }

        $bin->setExpireAt($this->getExpirationDate('1m'));

        $em = $this->doctrine->getManager();
        $em->persist($bin);
        $em->flush();

        return new JsonResponse(['success' => true, 'binExpiration' => $bin->getExpireAt()->format('Y-m-d H:i')]);
    }

    #[Route('/{hash}', name: 'mediabin_api_delete', requirements: ['hash' => '[a-zA-Z0-9]{8}'], methods: ['DELETE'])]
    public function deleteAction(#[MapEntity(mapping: ['hash' => 'hash'])] Bin $bin)
    {
        if (true !== $checkOwner = $this->checkBinOwner($this->getUser(), $bin->getUser())) {
            return $checkOwner;
        }

        $bin->setExpireAt(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($bin);
        $em->flush();

        return new JsonResponse(['success' => true, 'binExpiration' => $bin->getExpireAt()->format('Y-m-d H:i')]);
    }

    protected function checkBinOwner($user, $binOwner)
    {
        if (!is_object($user) || !$user instanceof User) {
            return new JsonResponse(['error' => 'User not logged'], 401);
        }

        if ($user != $binOwner) {
            return new JsonResponse(['error' => 'Only owner can modify his MediaBin'], 401);
        }

        return true;
    }

    protected function getExpirationDate($expiration)
    {
        $expirationDate = new \DateTime();

        switch ($expiration) {
            case '10m':
                $expirationDate->add(new \DateInterval('PT10M'));
                break;
            case '1h':
                $expirationDate->add(new \DateInterval('PT1H'));
                break;
            case '1d':
                $expirationDate->add(new \DateInterval('P1D'));
                break;
            case '1w':
                $expirationDate->add(new \DateInterval('P1W'));
                break;
            case '1m':
                $expirationDate->add(new \DateInterval('P1M'));
                break;
            case '-1':
                $expirationDate = null;
                break;
            case '1y':
            default:
                $expirationDate->add(new \DateInterval('P1Y'));
                break;
        }

        return $expirationDate;
    }
}
