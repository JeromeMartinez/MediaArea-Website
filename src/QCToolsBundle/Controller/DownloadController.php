<?php

namespace QCToolsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/QCTools/Download', name: 'qc_download')]
    #[Template('@QCTools/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Windows', name: 'qc_download_windows')]
    #[Template('@QCTools/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Mac_OS', name: 'qc_download_mac')]
    #[Template('@QCTools/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/AppImage', name: 'qc_download_appimage')]
    #[Template('@QCTools/Download/appimage.html.twig')]
    public function appimageAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Flatpak', name: 'qc_download_flatpak')]
    #[Template('@QCTools/Download/flatpak.html.twig')]
    public function flatpakAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Debian', name: 'qc_download_debian')]
    #[Template('@QCTools/Download/debian.html.twig')]
    public function debianAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Ubuntu', name: 'qc_download_ubuntu')]
    #[Template('@QCTools/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/RHEL', name: 'qc_download_rhel')]
    #[Template('@QCTools/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/CentOS', name: 'qc_download_centos')]
    #[Template('@QCTools/Download/centos.html.twig')]
    public function centosAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Fedora', name: 'qc_download_fedora')]
    #[Template('@QCTools/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/openSUSE', name: 'qc_download_opensuse')]
    #[Template('@QCTools/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/SLE', name: 'qc_download_sle')]
    #[Template('@QCTools/Download/sle.html.twig')]
    public function sleAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Mageia', name: 'qc_download_mageia')]
    #[Template('@QCTools/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Arch_Linux', name: 'qc_download_archlinux')]
    #[Template('@QCTools/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return array();
    }

    #[Route('/QCTools/Download/Source', name: 'qc_download_source')]
    #[Template('@QCTools/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
