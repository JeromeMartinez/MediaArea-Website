<?php

namespace NoTimeToWaitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DefaultController extends AbstractController
{
    #[Route('/NoTimeToWait', name: 'notimetowait_home')]
    #[Template('@NoTimeToWait/Default/index.html.twig')]
    public function indexAction()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait1', name: 'notimetowait_1')]
    #[Template('@NoTimeToWait/Default/nttw1.html.twig')]
    public function nttw1Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait2', name: 'notimetowait_2')]
    #[Template('@NoTimeToWait/Default/nttw2.html.twig')]
    public function nttw2Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait3', name: 'notimetowait_3')]
    #[Template('@NoTimeToWait/Default/nttw3.html.twig')]
    public function nttw3Action()
    {
        return ['noAds' => true];
    }

    #[Route('/QCWorkshop2018', name: 'qcworkshop2018')]
    #[Template('@NoTimeToWait/Default/qcworkshop2018.html.twig')]
    public function qcworkshop2018Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait4', name: 'notimetowait_4')]
    #[Template('@NoTimeToWait/Default/nttw4.html.twig')]
    public function nttw4Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait5', name: 'notimetowait_5')]
    #[Template('@NoTimeToWait/Default/nttw5.html.twig')]
    public function nttw5Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait6', name: 'notimetowait_6')]
    #[Template('@NoTimeToWait/Default/nttw6.html.twig')]
    public function nttw6Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait7', name: 'notimetowait_7')]
    #[Template('@NoTimeToWait/Default/nttw7.html.twig')]
    public function nttw7Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait8', name: 'notimetowait_8')]
    #[Template('@NoTimeToWait/Default/nttw8.html.twig')]
    public function nttw8Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait9', name: 'notimetowait_9')]
    #[Template('@NoTimeToWait/Default/nttw9.html.twig')]
    public function nttw9Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait10', name: 'notimetowait_10')]
    #[Template('@NoTimeToWait/Default/nttw10.html.twig')]
    public function nttw10Action()
    {
        return ['noAds' => true];
    }

    #[Route('/NoTimeToWait11', name: 'notimetowait_11')]
    #[Template('@NoTimeToWait/Default/nttw11.html.twig')]
    public function nttw11Action()
    {
        return ['noAds' => true];
    }
}
