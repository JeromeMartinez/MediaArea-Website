<?php

namespace EmbARCBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use EmbARCBundle\Lib\DownloadInfo\EmbARCDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/embARC', name: 'embarc_home')]
    #[Template('@EmbARC/Default/index.html.twig')]
    public function indexAction(Request $request, EmbARCDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/embARC/License', name: 'embarc_license')]
    #[Template('@EmbARC/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }

    #[Route('/embARC/man', name: 'embarc_man')]
    #[Template('@EmbARC/Default/man.html.twig')]
    public function manAction()
    {
        return array();
    }
}
