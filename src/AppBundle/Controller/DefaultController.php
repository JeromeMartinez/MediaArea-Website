<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DefaultController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    #[Template('@App/Default/index.html.twig')]
    public function indexAction()
    {
        //return $this->redirectToRoute('mi_home', array('_locale' => substr($request->getLocale(), 0, 2)));
        return ['noAds' => true];
    }

    #[Route('/{_locale}/Repos', name: 'ma_repos', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@App/Default/repos.html.twig')]
    public function reposAction()
    {
        return [];
    }

    #[Route('/Repos', name: 'ma_repos_wo_locale')]
    public function reposActionWithoutLocale(Request $request)
    {
        return $this->redirectToRoute('ma_repos', ['_locale' => $request->getLocale()], 302);
    }

    #[Route('/Events', name: 'ma_events')]
    #[Template('@App/Default/events.html.twig')]
    public function eventsAction()
    {
        return ['noAds' => true];
    }

    #[Route('/Conduct', name: 'ma_conduct')]
    #[Template('@App/Default/conduct.html.twig')]
    public function conductAction()
    {
        return ['noAds' => true];
    }

    #[Route('/TeamRules', name: 'ma_team_rules')]
    #[Template('@App/Default/teamRules.html.twig')]
    public function teamRulesAction()
    {
        return [];
    }

    #[Route('/Legal', name: 'ma_legal')]
    #[Template('@App/Default/legal.html.twig')]
    public function legalAction()
    {
        return [];
    }

    #[Route('/{_locale}/DIVX', name: 'ma_divx', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@App/Default/divx.html.twig')]
    public function divxAction()
    {
        return [];
    }

    #[Route('/{_locale}/DX50', name: 'ma_dx50', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@App/Default/dx50.html.twig')]
    public function dx50Action()
    {
        return [];
    }

    #[Route('/{_locale}/XVID', name: 'ma_xvid', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@App/Default/xvid.html.twig')]
    public function xvidAction()
    {
        return [];
    }

    #[Route('/Services', name: 'ma_professional_services')]
    #[Template('@App/Default/professionalServices.html.twig')]
    public function professionalServicesAction()
    {
        return ['noAds' => true];
    }

    #[Route('/Privacy', name: 'ma_privacy')]
    #[Template('@App/Default/privacy.html.twig')]
    public function privacyAction()
    {
        return ['noAds' => true];
    }

    #[Route('/AudioChannelLayout', name: 'ma_audiochannellayout')]
    #[Template('@App/Default/audioChannelLayout.html.twig')]
    public function audioChannelLayoutAction()
    {
        return ['noAds' => true];
    }
}
