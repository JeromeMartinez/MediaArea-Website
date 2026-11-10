<?php

namespace MediaConchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use MediaConchBundle\Lib\Checks\Severities;

#[Route('/Severities')]
class SeveritiesController extends AbstractController
{
    #[Route('', name: 'severities_home')]
    #[Template('@MediaConch/Severities/severities.html.twig')]
    public function severitiesAction(Severities $severities)
    {
        header('X-Robots-Tag: none');

        $items = $severities->listSeverities();
        return $this->render('@MediaConch/Default/checks.html.twig', [ 'items' => $items ]);
    }
    
    #[Route('/{severityId}', name: 'severities_1')]
    #[Template('@MediaConch/Severities/severity1.html.twig')]
    public function severity1Action($severityId, Severities $severities)
    {
        $items = $severities->listSeverityInfo($severityId);
        if (!$items) {
            throw $this->createNotFoundException('The severity is not found in our database');
        }
        return $this->render('@MediaConch/Default/checks.html.twig', [ 'items' => $items ]);
    }
}
