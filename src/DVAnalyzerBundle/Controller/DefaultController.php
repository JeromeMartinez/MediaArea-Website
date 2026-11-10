<?php

namespace DVAnalyzerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use DVAnalyzerBundle\Lib\DownloadInfo\DVAnalyzerDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/DVAnalyzer', name: 'dv_home')]
    #[Template('@DVAnalyzer/Default/index.html.twig')]
    public function indexAction(Request $request, DVAnalyzerDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/DVAnalyzer/License', name: 'dv_license')]
    #[Template('@DVAnalyzer/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }
}
