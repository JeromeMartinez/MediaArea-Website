<?php

namespace BWFMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class StandardsController extends AbstractController
{
    #[Route('/BWFMetaEdit/bext', name: 'bwf_standards_bext')]
    #[Template('@BWFMetaEdit/Standards/bext.html.twig')]
    public function bextAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/listinfo', name: 'bwf_standards_listinfo')]
    #[Template('@BWFMetaEdit/Standards/listinfo.html.twig')]
    public function listinfoAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/xml_chunks', name: 'bwf_standards_xml_chunks')]
    #[Template('@BWFMetaEdit/Standards/xmlChunks.html.twig')]
    public function xmlChunksAction()
    {
        return array();
    }
}
