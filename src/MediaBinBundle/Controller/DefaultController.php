<?php

namespace MediaBinBundle\Controller;

use UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use MediaBinBundle\Entity\Bin;
use MediaBinBundle\Lib\File\File;
use MediaBinBundle\Lib\MediaInfoCLI;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/MediaBin')]
class DefaultController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('', name: 'mediabin_home')]
    #[Template('@MediaBin/Default/index.html.twig')]
    public function indexAction()
    {
        $itemsPerPage = 10;
        $binRepository = $this->doctrine->getRepository(Bin::class);

        $user = $this->getUser();
        if (is_object($user) && $user instanceof User) {
            $userBin = $binRepository->getUserBin($user, 0, $itemsPerPage);
        }

        return [
            'latestsPublicBin' => $binRepository->getLatestsPublicBin(0, $itemsPerPage),
            'latestsPublicBinCount' => $binRepository->getLatestsPublicBin(0, $itemsPerPage)->count(),
            'userBin' => $userBin ?? null,
            'userBinCount' => isset($userBin) ? $userBin->count() : null,
            'itemsPerPage' => $itemsPerPage,
        ];
    }

    #[Route('/{hash}', name: 'mediabin_show', requirements: ['hash' => '[a-zA-Z0-9]{8}'])]
    #[Template('@MediaBin/Default/show.html.twig')]
    public function showAction(
        #[MapEntity(mapping: ['hash' => 'hash'])] Bin $bin,
        MediaInfoCLI $mediainfo,
        File $file
    ) {
        // Check if MediaBin is expired
        if ($bin->hasExpired()) {
            // Owners can access to their MediaBin even if it has expired
            $user = $this->getUser();
            if (!is_object($user) || !$user instanceof User || $user != $bin->getUser()) {
                throw new GoneHttpException();
            }
        }

        // Load XML
        try {
            $xml = base64_encode($file->get($bin->getHash()));
        } catch (\Exception $e) {
            throw new ServiceUnavailableHttpException();
        }

        if (1 == $bin->getAnonymize()) {
            // Get anonymized MediaInfo report
            $mediainfo->getReportFromXML($file->getFileNameWithPath($bin->getHash()), 1, 1);
            $xml = $mediainfo->getOutput();
        }

        // Get MediaInfo report to display before javascript is loaded
        $mediainfo->getReportFromXML($file->getFileNameWithPath($bin->getHash()), $bin->getAnonymize());

        return ['bin' => $bin, 'xml' => $xml, 'report' => $mediainfo->getOutput()];
    }
}
