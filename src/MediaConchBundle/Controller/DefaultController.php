<?php

namespace MediaConchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use MediaConchBundle\Lib\DownloadInfo\MediaConchDownloadInfo;

#[Route('/MediaConch')]
class DefaultController extends AbstractController
{
    #[Route('', name: 'mc_home')]
    #[Template('@MediaConch/Default/index.html.twig')]
    public function indexAction(Request $request, MediaConchDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return [
            'downloadInfo' => $downloadInfo,
        ];
    }

    #[Route('/Team', name: 'mc_team')]
    #[Template('@MediaConch/Default/team.html.twig')]
    public function teamAction()
    {
        return [];
    }

    #[Route('/Community', name: 'mc_community')]
    #[Template('@MediaConch/Default/community.html.twig')]
    public function communityAction()
    {
        return [];
    }
}
