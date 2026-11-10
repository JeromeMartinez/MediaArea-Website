<?php

namespace UserBundle\Controller;

use MediaConchOnlineBundle\Mailer\UserMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use UserBundle\Entity\User;
use UserBundle\Form\Type\RegistrationFormType;
use UserBundle\Lib\UserManager;
use UserBundle\Security\LoginManager;

/**
 * Controller managing the registration.
 *
 * Derived from the registration controller of FOSUserBundle (MIT license), the link which confirms the email
 * address is signed and checked by SymfonyCastsVerifyEmailBundle.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegistrationController extends AbstractController
{
    private $formFactory;
    private $userManager;
    private $tokenStorage;
    private $verifyEmailHelper;
    private $loginManager;
    private $mailer;
    private $translator;
    private $firewallName;
    private $confirmationEnabled;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        VerifyEmailHelperInterface $verifyEmailHelper,
        LoginManager $loginManager,
        UserMailer $mailer,
        TranslatorInterface $translator,
        string $firewallName,
        bool $confirmationEnabled
    ) {
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->loginManager = $loginManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->firewallName = $firewallName;
        $this->confirmationEnabled = $confirmationEnabled;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        // Redirect logged in user to profile page
        if ($this->isGranted('ROLE_BASIC')) {
            return $this->redirectToRoute('fos_user_profile_show');
        }

        // Redirect guest user to dedicated page
        if ($this->isGranted('ROLE_GUEST')) {
            return $this->redirectToRoute('user_guest_register');
        }

        // Store referer
        if ('GET' == $request->getMethod() && $request->headers->get('referer') != $request->getUri()) {
            $request->getSession()->set('registerRedirect', $request->headers->get('referer'));
        }

        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $form = $this->formFactory->createNamed('fos_user_registration_form', RegistrationFormType::class, $user, [
            'validation_groups' => ['UserRegistration'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->confirmationEnabled) {
                // The account is enabled once the email address is confirmed
                $user->setEnabled(false);

                $request->getSession()->set('fos_user_send_confirmation_email/email', $user->getEmail());

                $response = $this->redirectToRoute('fos_user_registration_check_email');
            } else {
                $response = $this->redirectToRoute('fos_user_registration_confirmed');
            }

            // Add basic role
            $user->addRole('ROLE_BASIC');

            $this->userManager->updateUser($user);

            if ($this->confirmationEnabled) {
                // The link of the email is signed with the identifier of the user, known now that he is saved
                $this->mailer->sendConfirmationEmailMessage($user);
            }

            $this->addFlash('success', $this->translator->trans('registration.flash.user_created', [], 'FOSUserBundle'));

            $this->authenticate($user);

            return $response;
        }

        return $this->render('@User/Registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Tell the user to check their email provider.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkEmailAction(Request $request)
    {
        $email = $request->getSession()->get('fos_user_send_confirmation_email/email');

        if (empty($email)) {
            return $this->redirectToRoute('fos_user_registration_register');
        }

        $request->getSession()->remove('fos_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        return $this->render('@User/Registration/check_email.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Receive the signed link of the confirmation email, login the user.
     *
     * @param Request $request
     * @param int     $id      Identifier of the user
     *
     * @return Response
     */
    public function confirmAction(Request $request, $id)
    {
        $user = $this->userManager->findUserBy(['id' => $id]);

        try {
            if (null === $user) {
                throw new InvalidSignatureException();
            }

            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                (string) $user->getId(),
                (string) $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            // The link is not the one sent to the user, or it has expired
            $this->addFlash('danger', $this->translator->trans($e->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('fos_user_security_login');
        }

        // The link logs in the user once: when his address is already confirmed, he has to log in
        if ($user->isEnabled() && !$user->hasRole('ROLE_GUEST')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $user->setEnabled(true);

        // Remove guest role
        if ($user->hasRole('ROLE_GUEST')) {
            $user->removeRole('ROLE_GUEST');
        }
        // Add basic role
        if (!$user->hasRole('ROLE_BASIC')) {
            $user->addRole('ROLE_BASIC');
        }

        $this->userManager->updateUser($user);

        $this->authenticate($user);

        return $this->redirectToRoute('fos_user_registration_confirmed');
    }

    /**
     * Tell the user his account is now confirmed.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function confirmedAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $url = $request->getSession()->get('registerRedirect');
        if (null !== $url) {
            return $this->redirect($url);
        }

        return $this->render('@User/Registration/confirmed.html.twig', [
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession($request),
        ]);
    }

    /**
     * Log in the user who has just been registered or confirmed.
     */
    private function authenticate(User $user): void
    {
        try {
            $this->loginManager->logInUser($this->firewallName, $user);

            $user->setLastLogin(new \DateTime());
            $this->userManager->updateUser($user);
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

    /**
     * @return string|null
     */
    private function getTargetUrlFromSession(Request $request)
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token || !method_exists($token, 'getFirewallName')) {
            return null;
        }

        $key = sprintf('_security.%s.target_path', $token->getFirewallName());

        if ($request->getSession()->has($key)) {
            return $request->getSession()->get($key);
        }

        return null;
    }
}
