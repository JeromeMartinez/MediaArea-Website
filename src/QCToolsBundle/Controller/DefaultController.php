<?php

namespace QCToolsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use QCToolsBundle\Lib\DownloadInfo\QCToolsDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/QCTools', name: 'qc_home')]
    #[Template('@QCTools/Default/index.html.twig')]
    public function indexAction(Request $request, QCToolsDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/QCTools/License', name: 'qc_license')]
    #[Template('@QCTools/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }
}
