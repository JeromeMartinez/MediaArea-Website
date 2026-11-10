<?php

namespace BWFMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/BWFMetaEdit/Download', name: 'bwf_download')]
    #[Template('@BWFMetaEdit/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Windows', name: 'bwf_download_windows')]
    #[Template('@BWFMetaEdit/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Mac_OS', name: 'bwf_download_mac')]
    #[Template('@BWFMetaEdit/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Flatpak', name: 'bwf_download_flatpak')]
    #[Template('@BWFMetaEdit/Download/flatpak.html.twig')]
    public function flatpakAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Debian', name: 'bwf_download_debian')]
    #[Template('@BWFMetaEdit/Download/debian.html.twig')]
    public function debianAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Raspbian', name: 'bwf_download_raspbian')]
    #[Template('@BWFMetaEdit/Download/raspbian.html.twig')]
    public function raspbianAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Ubuntu', name: 'bwf_download_ubuntu')]
    #[Template('@BWFMetaEdit/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/RHEL', name: 'bwf_download_rhel')]
    #[Template('@BWFMetaEdit/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/AlmaLinux', name: 'bwf_download_almalinux')]
    #[Template('@BWFMetaEdit/Download/almalinux.html.twig')]
    public function almalinuxAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/RockyLinux', name: 'bwf_download_rockylinux')]
    #[Template('@BWFMetaEdit/Download/rockylinux.html.twig')]
    public function rockylinuxAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/CentOS', name: 'bwf_download_centos')]
    #[Template('@BWFMetaEdit/Download/centos.html.twig')]
    public function centosAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Fedora', name: 'bwf_download_fedora')]
    #[Template('@BWFMetaEdit/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/openSUSE', name: 'bwf_download_opensuse')]
    #[Template('@BWFMetaEdit/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/SLE', name: 'bwf_download_sle')]
    #[Template('@BWFMetaEdit/Download/sle.html.twig')]
    public function sleAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Mageia', name: 'bwf_download_mageia')]
    #[Template('@BWFMetaEdit/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Arch_Linux', name: 'bwf_download_archlinux')]
    #[Template('@BWFMetaEdit/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/Download/Source', name: 'bwf_download_source')]
    #[Template('@BWFMetaEdit/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
