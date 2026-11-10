<?php

namespace MediaBinBundle\Controller;

use UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Attribute\Route;
use MediaBinBundle\Entity\Bin;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/api/v1/MediaBin/list')]
class ApiListingController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/byUser', name: 'mediabin_api_listing_user', methods: ['GET'])]
    public function byUserAction(Request $request, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            return new JsonResponse(['error' => 'User not logged'], 401);
        }

        (int) $start = $request->query->get('start', 0);
        if (0 > $start) {
            return new JsonResponse(['error' => 'Start parameter should be an integer >= 0'], 400);
        }

        (int) $limit = $request->query->get('limit', 5);
        if (1 > $limit) {
            return new JsonResponse(['error' => 'Limit parameter should be an integer >= 1'], 400);
        }

        $binRepository = $this->doctrine->getRepository(Bin::class);

        $list = [];
        $defaultBinTitle = $translator->trans('mediabin.create.title.placeholder');
        foreach ($binRepository->getUserBin($user, $start, $limit) as $bin) {
            $list[] = [
                'title' => $bin->getTitleListing($defaultBinTitle),
                 'hash' => $bin->getHash(),
             ];
        }

        return new JsonResponse(['list' => $list]);
    }

    #[Route('/latestsPublic', name: 'mediabin_api_listing_latests_public', methods: ['GET'])]
    public function latestsPublicAction(Request $request, TranslatorInterface $translator)
    {
        (int) $start = $request->query->get('start', 0);
        if (0 > $start) {
            return new JsonResponse(['error' => 'Start parameter should be an integer >= 0'], 400);
        }
        if (100 <= $start) {
            return new JsonResponse(['error' => 'Start parameter should be an integer < 100'], 400);
        }

        (int) $limit = $request->query->get('limit', 5);
        if (1 > $limit) {
            return new JsonResponse(['error' => 'Limit parameter should be an integer >= 1'], 400);
        }

        $binRepository = $this->doctrine->getRepository(Bin::class);

        $list = [];
        $defaultBinTitle = $translator->trans('mediabin.create.title.placeholder');
        foreach ($binRepository->getLatestsPublicBin($start, $limit) as $bin) {
            $list[] = [
                'title' => '' == $bin->getTitle() ? $defaultBinTitle : $bin->getTitle(),
                 'hash' => $bin->getHash(),
             ];
        }

        return new JsonResponse(['list' => $list]);
    }
}
