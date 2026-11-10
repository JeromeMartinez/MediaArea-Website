<?php

namespace MOVMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/MOVMetaEdit/Download', name: 'mov_download')]
    #[Template('@MOVMetaEdit/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Windows', name: 'mov_download_windows')]
    #[Template('@MOVMetaEdit/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Mac_OS', name: 'mov_download_mac')]
    #[Template('@MOVMetaEdit/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Flatpak', name: 'mov_download_flatpak')]
    #[Template('@MOVMetaEdit/Download/flatpak.html.twig')]
    public function flatpakAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Debian', name: 'mov_download_debian')]
    #[Template('@MOVMetaEdit/Download/debian.html.twig')]
    public function debianAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Ubuntu', name: 'mov_download_ubuntu')]
    #[Template('@MOVMetaEdit/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/RHEL', name: 'mov_download_rhel')]
    #[Template('@MOVMetaEdit/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/CentOS', name: 'mov_download_centos')]
    #[Template('@MOVMetaEdit/Download/centos.html.twig')]
    public function centosAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/RockyLinux', name: 'mov_download_rockylinux')]
    #[Template('@MOVMetaEdit/Download/rockylinux.html.twig')]
    public function rockylinuxAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Fedora', name: 'mov_download_fedora')]
    #[Template('@MOVMetaEdit/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/openSUSE', name: 'mov_download_opensuse')]
    #[Template('@MOVMetaEdit/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/SLE', name: 'mov_download_sle')]
    #[Template('@MOVMetaEdit/Download/sle.html.twig')]
    public function sleAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Mageia', name: 'mov_download_mageia')]
    #[Template('@MOVMetaEdit/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Arch_Linux', name: 'mov_download_archlinux')]
    #[Template('@MOVMetaEdit/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return array();
    }

    #[Route('/MOVMetaEdit/Download/Source', name: 'mov_download_source')]
    #[Template('@MOVMetaEdit/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
