<?php

namespace UserBundle\EventSubscriber;

use AppBundle\Lib\Recaptcha\RecaptchaVerifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginCaptchaSubscriber implements EventSubscriberInterface
{
    private $verifier;
    private $router;

    public function __construct(RecaptchaVerifier $verifier, RouterInterface $router)
    {
        $this->verifier = $verifier;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Before the firewall
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ('POST' !== $request->getMethod() || 'fos_user_security_check' !== $request->attributes->get('_route')) {
            return;
        }

        if ($request->attributes->get('_login_captcha_checked')) {
            return;
        }
        $request->attributes->set('_login_captcha_checked', true);

        if ($this->verifier->verify($request, true)) {
            return;
        }

        $session = $request->getSession();
        $session->set(SecurityRequestAttributes::LAST_USERNAME, $request->request->get('_username', ''));
        $session->set(
            SecurityRequestAttributes::AUTHENTICATION_ERROR,
            new CustomUserMessageAuthenticationException('This value is not a valid captcha.')
        );

        $event->setResponse(new RedirectResponse($this->router->generate('fos_user_security_login')));
    }
}
