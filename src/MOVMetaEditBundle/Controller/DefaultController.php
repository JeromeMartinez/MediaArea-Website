<?php

namespace MOVMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use MOVMetaEditBundle\Lib\DownloadInfo\MOVMetaEditDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/MOVMetaEdit', name: 'mov_home')]
    #[Template('@MOVMetaEdit/Default/index.html.twig')]
    public function indexAction(Request $request, MOVMetaEditDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/MOVMetaEdit/License', name: 'mov_license')]
    #[Template('@MOVMetaEdit/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }
}
