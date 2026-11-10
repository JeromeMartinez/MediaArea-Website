<?php

namespace RAWcookedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DownloadController extends AbstractController
{
    #[Route('/RAWcooked/Download', name: 'rawcooked_download')]
    #[Template('@RAWcooked/Download/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Windows', name: 'rawcooked_download_windows')]
    #[Template('@RAWcooked/Download/windows.html.twig')]
    public function windowsAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Mac_OS', name: 'rawcooked_download_mac')]
    #[Template('@RAWcooked/Download/mac.html.twig')]
    public function macAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Debian', name: 'rawcooked_download_debian')]
    #[Template('@RAWcooked/Download/debian.html.twig')]
    public function debianAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Ubuntu', name: 'rawcooked_download_ubuntu')]
    #[Template('@RAWcooked/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/RHEL', name: 'rawcooked_download_rhel')]
    #[Template('@RAWcooked/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/CentOS', name: 'rawcooked_download_centos')]
    #[Template('@RAWcooked/Download/centos.html.twig')]
    public function centosAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/AlmaLinux', name: 'rawcooked_download_almalinux')]
    #[Template('@RAWcooked/Download/almalinux.html.twig')]
    public function almalinuxAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/RockyLinux', name: 'rawcooked_download_rockylinux')]
    #[Template('@RAWcooked/Download/rockylinux.html.twig')]
    public function rockylinuxAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Fedora', name: 'rawcooked_download_fedora')]
    #[Template('@RAWcooked/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/openSUSE', name: 'rawcooked_download_opensuse')]
    #[Template('@RAWcooked/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/SLE', name: 'rawcooked_download_sle')]
    #[Template('@RAWcooked/Download/sle.html.twig')]
    public function sleAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Mageia', name: 'rawcooked_download_mageia')]
    #[Template('@RAWcooked/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Arch_Linux', name: 'rawcooked_download_archlinux')]
    #[Template('@RAWcooked/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return array();
    }

    #[Route('/RAWcooked/Download/Source', name: 'rawcooked_download_source')]
    #[Template('@RAWcooked/Download/source.html.twig')]
    public function sourceAction()
    {
        return array();
    }
}
