<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CookiesListener
{
    /**
     * @param RequestEvent $event
     */
    public function handleCookies(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (null !== $request->cookies->get('Donated')) {
            $event->getRequest()->attributes->set('donated', $request->cookies->get('Donated'));
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function setCookies(ResponseEvent $event)
    {
        // Check if donated cookie attribute has been set and add cookie to response
        if (null !== $cookie = $event->getRequest()->attributes->get('setDonatedCookie')) {
            $cookie = new Cookie('Donated', $cookie, '+5 years');
            $event->getResponse()->headers->setCookie($cookie);
        }
    }
}
