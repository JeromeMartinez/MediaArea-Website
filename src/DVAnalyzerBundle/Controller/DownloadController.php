<?php

namespace DVAnalyzerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/DVAnalyzer/Download', name: 'dv_download')]
    #[Template('@DVAnalyzer/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Windows', name: 'dv_download_windows')]
    #[Template('@DVAnalyzer/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Mac_OS', name: 'dv_download_mac')]
    #[Template('@DVAnalyzer/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Flatpak', name: 'dv_download_flatpak')]
    #[Template('@DVAnalyzer/Download/flatpak.html.twig')]
    public function flatpakAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Debian', name: 'dv_download_debian')]
    #[Template('@DVAnalyzer/Download/debian.html.twig')]
    public function debianAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Ubuntu', name: 'dv_download_ubuntu')]
    #[Template('@DVAnalyzer/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/RHEL', name: 'dv_download_rhel')]
    #[Template('@DVAnalyzer/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/CentOS', name: 'dv_download_centos')]
    #[Template('@DVAnalyzer/Download/centos.html.twig')]
    public function centosAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Fedora', name: 'dv_download_fedora')]
    #[Template('@DVAnalyzer/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/openSUSE', name: 'dv_download_opensuse')]
    #[Template('@DVAnalyzer/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/SLE', name: 'dv_download_sle')]
    #[Template('@DVAnalyzer/Download/sle.html.twig')]
    public function sleAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Mageia', name: 'dv_download_mageia')]
    #[Template('@DVAnalyzer/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Arch_Linux', name: 'dv_download_archlinux')]
    #[Template('@DVAnalyzer/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/Download/Source', name: 'dv_download_source')]
    #[Template('@DVAnalyzer/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
