<?php

namespace UserBundle\EventSubscriber;

use DeviceDetector\Parser\Bot as BotParser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use MediaConchOnlineBundle\Controller\PublicApiController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use UserBundle\Controller\GuestControllerInterface;
use UserBundle\Entity\GuestToken;
use UserBundle\Entity\User;
use UserBundle\Lib\TokenGenerator;
use UserBundle\Lib\UserManager;
use UserBundle\Lib\UserManipulator;
use UserBundle\Security\LoginManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestSubscriber implements EventSubscriberInterface
{
    protected $firewallName;
    protected $userManager;
    protected $userManipulator;
    protected $tokenGenerator;
    protected $loginManager;
    protected $em;
    protected $tokenStorage;
    protected $rememberMeHandler;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $firewallName,
        UserManager $userManager,
        UserManipulator $userManipulator,
        TokenGenerator $tokenGenerator,
        LoginManager $loginManager,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        RememberMeHandlerInterface $rememberMeHandler
    ) {
        $this->firewallName = $firewallName;
        $this->userManager = $userManager;
        $this->userManipulator = $userManipulator;
        $this->tokenGenerator = $tokenGenerator;
        $this->loginManager = $loginManager;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->rememberMeHandler = $rememberMeHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'guestAuthentication',
            KernelEvents::RESPONSE => 'guestCookie',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function guestAuthentication(ControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller) || !isset($controller[0])) {
            return;
        }
        $ctrl = $controller[0];

        // Public API user
        if ($ctrl instanceof PublicApiController) {
            $this->publicApiUser();
        } elseif (!$ctrl instanceof GuestControllerInterface) {
            return;
        }

        $user = $this->loginUser($event);

        // Check if user have at least ROLE_USER
        if (false === $user->hasRole('ROLE_USER')) {
            throw new AccessDeniedException();
        }
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        // Set guest cookie attribute to add cookie in response if user is a guest
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof User && $user->hasRole('ROLE_GUEST')) {
            // Only for guest users created after guestToken have been used
            if (null !== $user->getGuestToken()) {
                $event->getRequest()->attributes->set(
                    'guest_cookie',
                    $user->getUsername().':'.$user->getGuestToken()->getToken()
                );
            }
        }
    }

    public function guestCookie(ResponseEvent $event)
    {
        // Check if guest cookie attribute has been set and add cookie to response
        if (null !== $cookie = $event->getRequest()->attributes->get('guest_cookie')) {
            $cookie = new Cookie('guest', $cookie, strtotime('now + 1 month'));
            $event->getResponse()->headers->setCookie($cookie);
        }

        // Check if guest cookie remove attribute has been set and remove cookie
        if (true == $event->getRequest()->attributes->get('remove_guest_cookie')) {
            $cookie = new Cookie('guest', null);
            $event->getResponse()->headers->setCookie($cookie);
        }
    }

    protected function createGuestUser($event)
    {
        // Generate unique username
        do {
            $username = $this->generateRandomGuestUsername();
        } while (null !== $this->userManager->findUserByUsername($username));

        // Create user
        $user = $this->userManipulator->create(
            $username,
            $this->tokenGenerator->generateToken(),
            $username.'@mco',
            1,
            0
        );
        $user->setName(null);

        // Update last login date
        $user->setLastLogin(new \DateTime());

        // Add guest role
        $user->addRole('ROLE_GUEST');
        $this->userManager->updateUser($user);

        // Set token
        $token = $this->tokenGenerator->generateToken();
        $guestToken = new GuestToken();
        $guestToken->setUser($user);
        $guestToken->setToken($token);
        $this->em->persist($guestToken);
        $this->em->flush();
        $event->getRequest()->attributes->set('guest_cookie', $username.':'.$token);

        return $user;
    }

    protected function publicApiUser()
    {
        $username = 'publicAPI';

        // Create user if not exists
        if (null === $user = $this->userManager->findUserByUsername($username)) {
            $user = $this->userManipulator->create(
                $username,
                $this->tokenGenerator->generateToken(),
                $username.'@mco',
                1,
                0
            );
        }

        // Log in user
        $this->loginManager->logInUser($this->firewallName, $user);
    }

    protected function botUser()
    {
        // Bot user
        $username = 'guest';

        // Create user if not exists
        if (null === $user = $this->userManager->findUserByUsername($username)) {
            $user = $this->userManipulator->create(
                $username,
                $this->tokenGenerator->generateToken(),
                $username.'@mco',
                1,
                0
            );

            // Add guest role
            $user->addRole('ROLE_BOT');
            $this->userManager->updateUser($user);
        }

        return $user;
    }

    protected function guestUser($event)
    {
        // Read guest cookie
        $cookie = (string) $event->getRequest()->cookies->get('guest');
        if (false !== strpos($cookie, ':')) {
            list($username, $token) = explode(':', $cookie);
            if ('guest' == mb_substr($username, 0, 5) && preg_match('/^[a-z0-9]+$/', $username)) {
                // Get guest user token from DB
                $query = $this->em->createQueryBuilder()->select('t.token')
                    ->from(GuestToken::class, 't')
                    ->join('t.user', 'u')
                    ->where('u.username = :user')
                    ->setParameter('user', $username)
                    ->setMaxResults(1)
                    ->getQuery();
                try {
                    if (hash_equals((string) $query->getSingleScalarResult(), $token)) {
                        // Get user if token matches
                        return $this->userManager->findUserByUsername($username);
                    }
                } catch (NoResultException $e) {
                    // Do nothing guest user will be created after
                }
            }
        }

        return null;
    }

    protected function loginUser($event)
    {
        $token = $this->tokenStorage->getToken();
        $user = null === $token ? null : $token->getUser();
        if (!is_object($user) || !$user instanceof User) {
            $user = null;
            $rememberMe = false;

            // Check bots
            $botParser = new BotParser();
            $botParser->discardDetails();
            $botParser->setUserAgent((string) $event->getRequest()->headers->get('User-Agent'));

            if (null !== $botParser->parse()) {
                // Bot user
                $user = $this->botUser();
            } elseif ($event->getRequest()->cookies->has('guest')) {
                // Read guest cookie
                $user = $this->guestUser($event);
                $rememberMe = null !== $user;
            }

            // Create guest user if not done before
            if (null === $user) {
                $user = $this->createGuestUser($event);
                $rememberMe = true;
            }

            // Log in user
            $this->loginManager->logInUser($this->firewallName, $user);

            if ($rememberMe) {
                // The remember me cookie is added to the response by the security component
                $this->rememberMeHandler->createRememberMeCookie($user);
            }
        }

        return $user;
    }

    protected function generateRandomGuestUsername()
    {
        if (false == preg_match('/([0-9]{6})/', uniqid(mt_rand(), true), $matches)) {
            return $this->generateRandomGuestUsername();
        }

        return 'guest'.$matches[1];
    }
}
