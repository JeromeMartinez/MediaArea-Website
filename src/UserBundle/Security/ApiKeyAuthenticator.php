<?php

namespace UserBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authenticate the requests with the key of the X-apiKey header.
 */
class ApiKeyAuthenticator extends AbstractAuthenticator
{
    /**
     * The key is mandatory: every request is handled.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        // Get the X-apiKey header
        $apiKey = $request->headers->get('X-apiKey');

        if (!$apiKey) {
            throw new BadCredentialsException();
        }

        // The user is loaded by ApiKeyUserProvider
        return new SelfValidatingPassport(new UserBadge($apiKey));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['Error' => 'Authentication Failed.'], 401);
    }
}
