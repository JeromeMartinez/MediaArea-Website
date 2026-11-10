<?php

namespace MediaConchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use MediaConchBundle\Lib\Checks\Checks;

#[Route('/Checks')]
class ChecksController extends AbstractController
{
    #[Route('', name: 'checks_home')]
    #[Template('@MediaConch/Checks/checks.html.twig')]
    public function checksAction(Checks $checks)
    {
        return $this->render('@MediaConch/Default/checks.html.twig', [ 'items' => $checks->listChecks() ]);
    }
    
    #[Route('/{checkId}', name: 'checks_1')]
    #[Template('@MediaConch/Checks/checks1.html.twig')]
    public function checks1Action($checkId, Checks $checks)
    {
        header('X-Robots-Tag: none');
        
        $items = $checks->listCheckInfo($checkId);
        if (!$items) {
            throw $this->createNotFoundException('The check is not found in our database');
        }
        return $this->render('@MediaConch/Default/checks.html.twig', [ 'items' => $items ]);
    }
}
