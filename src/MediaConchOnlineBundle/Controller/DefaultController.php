<?php

namespace MediaConchOnlineBundle\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/MediaConchOnline')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'mco_home')]
    #[Template('@MediaConchOnline/Default/homepage.html.twig')]
    public function homepageAction()
    {
        return [];
    }
}
