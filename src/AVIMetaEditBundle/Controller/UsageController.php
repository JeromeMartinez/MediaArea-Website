<?php

namespace AVIMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class UsageController extends AbstractController
{
    #[Route('/AVIMetaEdit/options', name: 'avi_usage_preferences')]
    #[Template('@AVIMetaEdit/Usage/preferences.html.twig')]
    public function preferencesAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/md5', name: 'avi_usage_md5')]
    #[Template('@AVIMetaEdit/Usage/md5.html.twig')]
    public function md5Action()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/validation_rules_help', name: 'avi_usage_validation_rules')]
    #[Template('@AVIMetaEdit/Usage/validationsRules.html.twig')]
    public function validationsRulesAction()
    {
        return array();
    }

    #[Route('/AVIMetaEdit/workflows', name: 'avi_usage_workflows')]
    #[Template('@AVIMetaEdit/Usage/workflows.html.twig')]
    public function workflowsAction()
    {
        return array();
    }
}
