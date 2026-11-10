<?php

namespace BWFMetaEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class UsageController extends AbstractController
{
    #[Route('/BWFMetaEdit/options', name: 'bwf_usage_preferences')]
    #[Template('@BWFMetaEdit/Usage/preferences.html.twig')]
    public function preferencesAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/md5', name: 'bwf_usage_md5')]
    #[Template('@BWFMetaEdit/Usage/md5.html.twig')]
    public function md5Action()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/validation_rules_help', name: 'bwf_usage_validation_rules')]
    #[Template('@BWFMetaEdit/Usage/validationsRules.html.twig')]
    public function validationsRulesAction()
    {
        return array();
    }

    #[Route('/BWFMetaEdit/workflows', name: 'bwf_usage_workflows')]
    #[Template('@BWFMetaEdit/Usage/workflows.html.twig')]
    public function workflowsAction()
    {
        return array();
    }
}
