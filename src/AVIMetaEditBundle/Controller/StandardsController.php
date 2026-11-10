<?php

namespace AVIMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class StandardsController extends AbstractController
{
    #[Route('/AVIMetaEdit/listinfo', name: 'avi_standards_listinfo')]
    #[Template('@AVIMetaEdit/Standards/listinfo.html.twig')]
    public function listinfoAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/imit', name: 'avi_standards_imit')]
    #[Template('@AVIMetaEdit/Standards/imit.html.twig')]
    public function imitAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/xml_chunks', name: 'avi_standards_xml_chunks')]
    #[Template('@AVIMetaEdit/Standards/xmlChunks.html.twig')]
    public function xmlChunksAction()
    {
        return array();
    }
}
