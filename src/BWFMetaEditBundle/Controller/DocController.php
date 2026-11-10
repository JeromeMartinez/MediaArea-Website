<?php

namespace BWFMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class DocController extends AbstractController
{
    #[Route('/BWFMetaEdit/tech_view_help', name: 'bwf_doc_tech')]
    #[Template('@BWFMetaEdit/Doc/tech.html.twig')]
    public function techAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/core_doc_help', name: 'bwf_doc_core')]
    #[Template('@BWFMetaEdit/Doc/core.html.twig')]
    public function coreAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/trace', name: 'bwf_doc_trace')]
    #[Template('@BWFMetaEdit/Doc/trace.html.twig')]
    public function traceAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/errors', name: 'bwf_doc_errors')]
    #[Template('@BWFMetaEdit/Doc/errors.html.twig')]
    public function errorsAction()
    {
        return array();
    }
}
