<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\LocaleSwitcher;

class LocaleListener implements EventSubscriberInterface
{
    protected $defaultLocale;
    protected $validLocales;
    protected $localeSwitcher;
    protected $routerContext;

    public function __construct(
        $defaultLocale,
        $validLocales,
        LocaleSwitcher $localeSwitcher,
        RequestContext $routerContext
    ) {
        $this->defaultLocale = $defaultLocale;
        $this->validLocales = explode('|', $validLocales);
        $this->localeSwitcher = $localeSwitcher;
        $this->routerContext = $routerContext;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            $locale = $this->normalizeLocale($request->getPreferredLanguage(), $this->validLocales);
            $this->setLocale($request, $locale);
            // save locale in session
            $request->getSession()->set('_locale', $locale);

            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $this->normalizeLocale($locale, $this->validLocales));
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $this->setLocale($request, $request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            // must be registered after the Locale listeners of Symfony: the one setting the locale of the request
            // from the route (priority 16) and the one giving this locale to the translator (priority 15)
            KernelEvents::REQUEST => array(array('onKernelRequest', 14)),
        );
    }

    /**
     * Change the locale of the request and of the services using it (translator...).
     *
     * The URLs generated without an explicit locale keep the locale they had before, the one of the route
     * if it has one else the default one: this is how the website always behaved.
     */
    protected function setLocale(Request $request, $locale)
    {
        $urlLocale = $this->routerContext->getParameter('_locale');

        $request->setLocale($locale);
        try {
            $this->localeSwitcher->setLocale($request->getLocale());
        } catch (\InvalidArgumentException $e) {
            $this->localeSwitcher->setLocale($request->getDefaultLocale());
        }

        $this->routerContext->setParameter('_locale', $urlLocale);
    }

    /**
     * Handle locale with 2 or 5 characters
     * @param string locale The locale to normalize
     * @param array validLocales The list of valid locales
     *
     * @return string
     */
    protected function normalizeLocale($locale, $validLocales)
    {
        $locale = (string) $locale;
        if (in_array($locale, $validLocales)) {
            return $locale;
        }

        if (in_array(substr($locale, 0, 2), $validLocales)) {
            return substr($locale, 0, 2);
        }

        return $this->defaultLocale;
    }
}
