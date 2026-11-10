<?php

namespace AVIMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class DocController extends AbstractController
{
    #[Route('/AVIMetaEdit/tech_view_help', name: 'avi_doc_tech')]
    #[Template('@AVIMetaEdit/Doc/tech.html.twig')]
    public function techAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/core_doc_help', name: 'avi_doc_core')]
    #[Template('@AVIMetaEdit/Doc/core.html.twig')]
    public function coreAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/trace', name: 'avi_doc_trace')]
    #[Template('@AVIMetaEdit/Doc/trace.html.twig')]
    public function traceAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/errors', name: 'avi_doc_errors')]
    #[Template('@AVIMetaEdit/Doc/errors.html.twig')]
    public function errorsAction()
    {
        return array();
    }
}
