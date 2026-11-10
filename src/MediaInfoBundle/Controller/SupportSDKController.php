<?php

namespace MediaInfoBundle\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SupportSDKController extends AbstractController
{
    #[Route('/{_locale}/MediaInfo/Support/SDK', name: 'mi_support_sdk', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/SupportSDK/index.html.twig')]
    public function indexAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/SDK/ReadFirst',
        name: 'mi_support_sdk_readfirst',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/SupportSDK/readFirst.html.twig')]
    public function readFirstAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/SDK/Means',
        name: 'mi_support_sdk_means',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/SupportSDK/means.html.twig')]
    public function meansAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/SDK/Quick_Start',
        name: 'mi_support_sdk_quick_start',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/SupportSDK/quickStart.html.twig')]
    public function quickStartAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/SDK/More_Info',
        name: 'mi_support_sdk_more_info',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/SupportSDK/moreInfo.html.twig')]
    public function moreInfoAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/SDK/Buffers',
        name: 'mi_support_sdk_buffers',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/SupportSDK/buffers.html.twig')]
    public function buffersAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/SDK/Duplicate',
        name: 'mi_support_sdk_duplicate',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/SupportSDK/duplicate.html.twig')]
    public function duplicateAction()
    {
        return array();
    }

    #[Route(
        '/{_locale}/MediaInfo/Support/SDK/Filtering',
        name: 'mi_support_sdk_filtering',
        requirements: ['_locale' => '%app.locales%'],
    )]
    #[Template('@MediaInfo/SupportSDK/filtering.html.twig')]
    public function filteringAction()
    {
        return array();
    }
}
