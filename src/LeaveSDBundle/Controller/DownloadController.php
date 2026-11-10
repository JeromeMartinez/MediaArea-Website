<?php

namespace LeaveSDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/LeaveSD/Download', name: 'leavesd_download')]
    #[Template('@LeaveSD/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/LeaveSD/Download/Windows', name: 'leavesd_download_windows')]
    #[Template('@LeaveSD/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/LeaveSD/Download/Source', name: 'leavesd_download_source')]
    #[Template('@LeaveSD/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
