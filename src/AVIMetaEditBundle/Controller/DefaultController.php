<?php

namespace AVIMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use AVIMetaEditBundle\Lib\DownloadInfo\AVIMetaEditDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/AVIMetaEdit', name: 'avi_home')]
    #[Template('@AVIMetaEdit/Default/index.html.twig')]
    public function indexAction(Request $request, AVIMetaEditDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/AVIMetaEdit/License', name: 'avi_license')]
    #[Template('@AVIMetaEdit/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }
}
