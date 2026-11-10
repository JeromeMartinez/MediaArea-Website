<?php

namespace MediaTraceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class DefaultController extends AbstractController
{
    #[Route('/MediaTrace', name: 'mediatrace_home')]
    #[Template('@MediaTrace/Default/index.html.twig')]
    public function indexAction()
    {
        return array();
    }
}
