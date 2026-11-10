<?php

namespace MediaInfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class OnlineController extends AbstractController
{
    #[Route('/MediaInfoOnline', name: 'mi_online')]
    #[Template('@MediaInfo/Online/index.html.twig')]
    public function indexAction()
    {
        return [];
    }
}
