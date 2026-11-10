<?php

namespace MediaConchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
#[Route('/MediaConch/Download')]
class DownloadController extends AbstractController
{
    #[Route('', name: 'mc_download')]
    #[Template('@MediaConch/Download/index.html.twig')]
    public function indexAction()
    {
        return [];
    }

    #[Route('/Windows', name: 'mc_download_windows')]
    #[Template('@MediaConch/Download/windows.html.twig')]
    public function windowsAction()
    {
        return [];
    }

    #[Route('/Mac_OS', name: 'mc_download_mac')]
    #[Template('@MediaConch/Download/mac.html.twig')]
    public function macAction()
    {
        return [];
    }

    #[Route('/AppImage', name: 'mc_download_appimage')]
    #[Template('@MediaConch/Download/appimage.html.twig')]
    public function appimageAction()
    {
        return [];
    }

    #[Route('/Flatpak', name: 'mc_download_flatpak')]
    #[Template('@MediaConch/Download/flatpak.html.twig')]
    public function flatpakAction()
    {
        return [];
    }

    #[Route('/Lambda', name: 'mc_download_lambda')]
    #[Template('@MediaConch/Download/lambda.html.twig')]
    public function lambdaAction()
    {
        return [];
    }


    #[Route('/JavaScript', name: 'mc_download_javascript')]
    #[Template('@MediaConch/Download/javascript.html.twig')]
    public function javascriptAction()
    {
        return [];
    }


    #[Route('/Snap', name: 'mc_download_snap')]
    #[Template('@MediaConch/Download/snap.html.twig')]
    public function snapAction()
    {
        return [];
    }

    #[Route('/Debian', name: 'mc_download_debian')]
    #[Template('@MediaConch/Download/debian.html.twig')]
    public function debianAction()
    {
        return [];
    }

    #[Route('/Raspbian', name: 'mc_download_raspbian')]
    #[Template('@MediaConch/Download/raspbian.html.twig')]
    public function raspbianAction()
    {
        return [];
    }

    #[Route('/Ubuntu', name: 'mc_download_ubuntu')]
    #[Template('@MediaConch/Download/ubuntu.html.twig')]
    public function ubuntuAction()
    {
        return [];
    }

    #[Route('/RHEL', name: 'mc_download_rhel')]
    #[Template('@MediaConch/Download/rhel.html.twig')]
    public function rhelAction()
    {
        return [];
    }

    #[Route('/RockyLinux', name: 'mc_download_rockylinux')]
    #[Template('@MediaConch/Download/rockylinux.html.twig')]
    public function rockylinuxAction()
    {
        return [];
    }

    #[Route('/CentOS', name: 'mc_download_centos')]
    #[Template('@MediaConch/Download/centos.html.twig')]
    public function centosAction()
    {
        return [];
    }

    #[Route('/Fedora', name: 'mc_download_fedora')]
    #[Template('@MediaConch/Download/fedora.html.twig')]
    public function fedoraAction()
    {
        return [];
    }

    #[Route('/openSUSE', name: 'mc_download_opensuse')]
    #[Template('@MediaConch/Download/opensuse.html.twig')]
    public function opensuseAction()
    {
        return [];
    }

    #[Route('/SLE', name: 'mc_download_sle')]
    #[Template('@MediaConch/Download/sle.html.twig')]
    public function sleAction()
    {
        return [];
    }

    #[Route('/Mageia', name: 'mc_download_mageia')]
    #[Template('@MediaConch/Download/mageia.html.twig')]
    public function mageiaAction()
    {
        return [];
    }

    #[Route('/Arch_Linux', name: 'mc_download_archlinux')]
    #[Template('@MediaConch/Download/archlinux.html.twig')]
    public function archlinuxAction()
    {
        return [];
    }

    #[Route('/Source', name: 'mc_download_source')]
    #[Template('@MediaConch/Download/source.html.twig')]
    public function sourceAction()
    {
        return [];
    }

    #[Route('/Linuxbrew', name: 'mc_download_linuxbrew')]
    #[Template('@MediaConch/Download/linuxbrew.html.twig')]
    public function linuxbrewAction()
    {
        return [];
    }

    #[Route('/Snapshots', name: 'mc_download_snapshots')]
    #[Template('@MediaConch/Download/snapshots.html.twig')]
    public function snapshotsAction()
    {
        return [];
    }
}
