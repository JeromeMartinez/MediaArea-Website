<?php

namespace EmbARCBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/embARC/Download', name: 'embarc_download')]
    #[Template('@EmbARC/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/embARC/Download/Windows', name: 'embarc_download_windows')]
    #[Template('@EmbARC/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/embARC/Download/macOS', name: 'embarc_download_mac', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@EmbARC/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/embARC/Download/Linux', name: 'embarc_download_linux', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@EmbARC/Download/linux.html.twig')]
    public function linuxAction()
    {
        return array();
    }

    #[Route('/embARC/Download/Source', name: 'embarc_download_source')]
    #[Template('@EmbARC/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
