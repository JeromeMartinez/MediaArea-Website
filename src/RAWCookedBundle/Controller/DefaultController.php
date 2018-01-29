<?php

namespace RAWCookedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/RAWCooked", name="rawcooked_home")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }
}
