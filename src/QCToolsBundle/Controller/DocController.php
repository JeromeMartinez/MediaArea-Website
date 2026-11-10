<?php

namespace QCToolsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DocController extends AbstractController
{
    #[Route('/QCTools/Getting_Started', name: 'qc_doc_getting_started')]
    #[Template('@QCTools/Doc/gettingStarted.html.twig')]
    public function gettingStartedAction()
    {
        return array();
    }

    #[Route('/QCTools/How_To_Use', name: 'qc_doc_how_to_use')]
    #[Template('@QCTools/Doc/howToUse.html.twig')]
    public function howToUseAction()
    {
        return array();
    }

    #[Route('/QCTools/Filter_Descriptions', name: 'qc_doc_filter_descriptions')]
    #[Template('@QCTools/Doc/filterDescriptions.html.twig')]
    public function filterDescriptionsAction()
    {
        return array();
    }

    #[Route('/QCTools/Playback_Filters', name: 'qc_doc_playback_filters')]
    #[Template('@QCTools/Doc/playbackFilters.html.twig')]
    public function playbackFiltersAction()
    {
        return array();
    }

    #[Route('/QCTools/Recording', name: 'qc_doc_recording')]
    #[Template('@QCTools/Doc/recording.html.twig')]
    public function recordingAction()
    {
        return array();
    }

    #[Route('/QCTools/Seattle_Municipal_Archives_Manual', name: 'qc_doc_seattle')]
    #[Template('@QCTools/Doc/seattle.html.twig')]
    public function seattleAction()
    {
        return array();
    }
}
