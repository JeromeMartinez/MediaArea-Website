<?php

namespace MediaConchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

#[Route('/MediaConch/Documentation/')]
class DocumentationController extends AbstractController
{
    #[Route('Fixity', name: 'mc_documentation_fixity')]
    #[Template('@MediaConch/Documentation/fixity.html.twig')]
    public function fixityAction()
    {
        return [];
    }

    #[Route('FAQ', name: 'mc_documentation_faq')]
    #[Template('@MediaConch/Documentation/faq.html.twig')]
    public function faqAction()
    {
        return [];
    }

    #[Route('HowToUse', name: 'mc_documentation_howtouse')]
    #[Template('@MediaConch/Documentation/howToUse.html.twig')]
    public function howToUseAction()
    {
        return [];
    }

    #[Route('DataFormat', name: 'mc_documentation_dataformat')]
    #[Template('@MediaConch/Documentation/dataFormat.html.twig')]
    public function dataFormatAction()
    {
        return [];
    }

    #[Route('Installation', name: 'mc_documentation_installation')]
    #[Template('@MediaConch/Documentation/installation.html.twig')]
    public function installationAction()
    {
        return [];
    }
}
