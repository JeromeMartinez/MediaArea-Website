<?php

namespace DVAnalyzerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class DocController extends AbstractController
{
    #[Route('/DVAnalyzer/dv-metadata', name: 'dv_doc_metadata')]
    #[Template('@DVAnalyzer/Doc/metadata.html.twig')]
    public function metadataAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/what-does-it-analyze', name: 'dv_doc_analyze')]
    #[Template('@DVAnalyzer/Doc/analyze.html.twig')]
    public function analyzeAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/dv-video-error-concealment', name: 'dv_doc_video_errors')]
    #[Template('@DVAnalyzer/Doc/videoErrors.html.twig')]
    public function videoErrorsAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/audio-errors', name: 'dv_doc_audio_errors')]
    #[Template('@DVAnalyzer/Doc/audioErrors.html.twig')]
    public function audioErrorsAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/dif-incoherency', name: 'dv_doc_dif_incoherency')]
    #[Template('@DVAnalyzer/Doc/difIncoherency.html.twig')]
    public function difIncoherencyAction()
    {
        return array();
    }

    #[Route('/DVAnalyzer/stts-fluctuation', name: 'dv_doc_stts_fluctuation')]
    #[Template('@DVAnalyzer/Doc/sttsFluctuation.html.twig')]
    public function sttsFluctuationAction()
    {
        return array();
    }
}
