<?php

namespace RAWcookedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use RAWcookedBundle\Lib\DownloadInfo\RAWcookedDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/RAWcooked', name: 'rawcooked_home')]
    #[Template('@RAWcooked/Default/index.html.twig')]
    public function indexAction(Request $request, RAWcookedDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/RAWcooked/License', name: 'rawcooked_license')]
    #[Template('@RAWcooked/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }

    #[Route('/RAWcooked/man', name: 'rawcooked_man')]
    #[Template('@RAWcooked/Default/man.html.twig')]
    public function manAction()
    {
        return array();
    }
}
