<?php

namespace AVIMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/AVIMetaEdit/Download', name: 'avi_download')]
    #[Template('@AVIMetaEdit/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Windows', name: 'avi_download_windows')]
    #[Template('@AVIMetaEdit/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Mac_OS', name: 'avi_download_mac')]
    #[Template('@AVIMetaEdit/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Flatpak', name: 'avi_download_flatpak')]
    #[Template('@AVIMetaEdit/Download/flatpak.html.twig')]
    public function flatpakAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Debian', name: 'avi_download_debian')]
    #[Template('@AVIMetaEdit/Download/debian.html.twig')]
    public function debianAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Ubuntu', name: 'avi_download_ubuntu')]
    #[Template('@AVIMetaEdit/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/RHEL', name: 'avi_download_rhel')]
    #[Template('@AVIMetaEdit/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/CentOS', name: 'avi_download_centos')]
    #[Template('@AVIMetaEdit/Download/centos.html.twig')]
    public function centosAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Fedora', name: 'avi_download_fedora')]
    #[Template('@AVIMetaEdit/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/openSUSE', name: 'avi_download_opensuse')]
    #[Template('@AVIMetaEdit/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/SLE', name: 'avi_download_sle')]
    #[Template('@AVIMetaEdit/Download/sle.html.twig')]
    public function sleAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Mageia', name: 'avi_download_mageia')]
    #[Template('@AVIMetaEdit/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Arch_Linux', name: 'avi_download_archlinux')]
    #[Template('@AVIMetaEdit/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/Download/Source', name: 'avi_download_source')]
    #[Template('@AVIMetaEdit/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
