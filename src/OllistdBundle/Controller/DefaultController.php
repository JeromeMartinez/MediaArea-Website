<?php

namespace OllistdBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class DefaultController extends AbstractController
{
    #[Route('/ollistd', name: 'ollistd_home')]
    #[Template('@Ollistd/Default/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/ollistd/Contributing', name: 'ollistd_contributing')]
    #[Template('@Ollistd/Default/contributing.html.twig')]
    public function contributingAction()
    {
        return array();
    }

    #[Route('/ollistd/mp4ra_FFV1', name: 'ollistd_mp4ra_ffv1')]
    #[Template('@Ollistd/Default/mp4raFFV1.html.twig')]
    public function mp4raFFV1Action()
    {
        return array();
    }

    #[Route('/ollistd/MXF_FFV1', name: 'ollistd_mxf_ffv1')]
    #[Template('@Ollistd/Default/mXFFFV1.html.twig')]
    public function mXFFFV1Action()
    {
        return array();
    }

    #[Route('/ollistd/MXF_FLAC', name: 'ollistd_mxf_flac')]
    #[Template('@Ollistd/Default/mXFFLAC.html.twig')]
    public function mXFFLACAction()
    {
        return array();
    }
}
