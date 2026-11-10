<?php

namespace BWFMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use BWFMetaEditBundle\Lib\DownloadInfo\BWFMetaEditDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/BWFMetaEdit', name: 'bwf_home')]
    #[Template('@BWFMetaEdit/Default/index.html.twig')]
    public function indexAction(Request $request, BWFMetaEditDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/BWFMetaEdit/License', name: 'bwf_license')]
    #[Template('@BWFMetaEdit/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }
}
