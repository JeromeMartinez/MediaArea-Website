<?php

namespace MediaInfoBundle\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SupportController extends AbstractController
{
    #[Route('/{_locale}/MediaInfo/Support', name: 'mi_support', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Support/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route('/{_locale}/MediaInfo/Support/FAQ', name: 'mi_support_faq', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Support/faq.html.twig')]
    public function faqAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/Fields',
        name: 'mi_support_fields',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/Support/fields.html.twig')]
    public function fieldsAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/Formats',
        name: 'mi_support_formats',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/Support/formats.html.twig')]
    public function formatsAction()
    {
        return array();
    }

    #[Route('/{_locale}/MediaInfo/Support/Tags', name: 'mi_support_tags', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Support/tags.html.twig')]
    public function tagsAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/Build_From_Sources',
        name: 'mi_support_build',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/Support/build.html.twig')]
    public function buildAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/Build_From_Sources/MediaInfoLib',
        name: 'mi_support_build_mil',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/Support/buildMediaInfoLib.html.twig')]
    public function buildMediaInfoLibAction()
    {
        return array();
    }
}
