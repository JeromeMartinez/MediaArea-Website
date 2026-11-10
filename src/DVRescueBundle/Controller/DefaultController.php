<?php

namespace DVRescueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use DVRescueBundle\Lib\DownloadInfo\DVRescueDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/DVRescue', name: 'dvrescue_home')]
    #[Template('@DVRescue/Default/index.html.twig')]
    public function indexAction(Request $request, DVRescueDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/DVRescue/License', name: 'dvrescue_license')]
    #[Template('@DVRescue/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }

    #[Route('/DVRescue/man', name: 'dvrescue_man')]
    #[Template('@DVRescue/Default/man.html.twig')]
    public function manAction()
    {
        return array();
    }
}
