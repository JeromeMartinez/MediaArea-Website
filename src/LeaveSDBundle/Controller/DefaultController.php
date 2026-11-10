<?php

namespace LeaveSDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use LeaveSDBundle\Lib\DownloadInfo\LeaveSDDownloadInfo;

class DefaultController extends AbstractController
{
    #[Route('/LeaveSD', name: 'leavesd_home')]
    #[Template('@LeaveSD/Default/index.html.twig')]
    public function indexAction(Request $request, LeaveSDDownloadInfo $downloadInfo)
    {
        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        return array(
            'downloadInfo' => $downloadInfo,
        );
    }

    #[Route('/LeaveSD/License', name: 'leavesd_license')]
    #[Template('@LeaveSD/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }

    #[Route('/LeaveSD/man', name: 'leavesd_man')]
    #[Template('@LeaveSD/Default/man.html.twig')]
    public function manAction()
    {
        return array();
    }
}
