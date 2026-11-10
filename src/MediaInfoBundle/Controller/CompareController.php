<?php

namespace MediaInfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class CompareController extends AbstractController
{
    #[Route('/MediaCompare', name: 'mi_compare')]
    #[Template('@MediaInfo/Compare/index.html.twig')]
    public function indexAction()
    {
        return [];
    }
}
