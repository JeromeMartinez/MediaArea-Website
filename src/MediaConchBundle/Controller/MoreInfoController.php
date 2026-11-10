<?php

namespace MediaConchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use MediaConchBundle\Lib\Checks\Checks;

#[Route('/MoreInfo')]
class MoreInfoController extends AbstractController
{
    #[Route('', name: 'moreinfo_home')]
    #[Template('@MediaConch/MoreInfo/analyze.html.twig')]
    public function analyzeAction(Request $request, Checks $checks)
    {
        header('X-Robots-Tag: none');

        return $this->render('@MediaConch/Default/checks.html.twig', [
            'title' => 'MediaConch, more information',
            'items' => $checks->analyzeMediaInfoReport($request->query->get('mi')),
        ]);
    }
}
