<?php

namespace DVRescueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/DVRescue/Download', name: 'dvrescue_download')]
    #[Template('@DVRescue/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Windows', name: 'dvrescue_download_windows')]
    #[Template('@DVRescue/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Mac_OS', name: 'dvrescue_download_mac')]
    #[Template('@DVRescue/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Debian', name: 'dvrescue_download_debian')]
    #[Template('@DVRescue/Download/debian.html.twig')]
    public function debianAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Ubuntu', name: 'dvrescue_download_ubuntu')]
    #[Template('@DVRescue/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/RHEL', name: 'dvrescue_download_rhel')]
    #[Template('@DVRescue/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/CentOS', name: 'dvrescue_download_centos')]
    #[Template('@DVRescue/Download/centos.html.twig')]
    public function centosAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Fedora', name: 'dvrescue_download_fedora')]
    #[Template('@DVRescue/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/openSUSE', name: 'dvrescue_download_opensuse')]
    #[Template('@DVRescue/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/SLE', name: 'dvrescue_download_sle')]
    #[Template('@DVRescue/Download/sle.html.twig')]
    public function sleAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Mageia', name: 'dvrescue_download_mageia')]
    #[Template('@DVRescue/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Arch_Linux', name: 'dvrescue_download_archlinux')]
    #[Template('@DVRescue/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return array();
    }

    #[Route('/DVRescue/Download/Source', name: 'dvrescue_download_source')]
    #[Template('@DVRescue/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
