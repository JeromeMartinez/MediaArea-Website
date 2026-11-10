<?php

namespace UserBundle\Controller;

use AppBundle\Form\Type\RecaptchaType;
use AppBundle\Validator\Constraints\Recaptcha;
use MediaConchOnlineBundle\Mailer\UserMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Contracts\Translation\TranslatorInterface;
use UserBundle\Form\Type\ResettingFormType;
use UserBundle\Lib\TokenGenerator;
use UserBundle\Lib\UserManager;
use UserBundle\Security\LoginManager;

/**
 * Controller managing the resetting of the password.
 *
 * Derived from the resetting controller of FOSUserBundle (MIT license).
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResettingController extends AbstractController
{
    private $formFactory;
    private $userManager;
    private $tokenGenerator;
    private $loginManager;
    private $mailer;
    private $translator;
    private $firewallName;
    private $retryTtl;
    private $tokenTtl;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserManager $userManager,
        TokenGenerator $tokenGenerator,
        LoginManager $loginManager,
        UserMailer $mailer,
        TranslatorInterface $translator,
        string $firewallName,
        int $retryTtl,
        int $tokenTtl
    ) {
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->loginManager = $loginManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->firewallName = $firewallName;
        $this->retryTtl = $retryTtl;
        $this->tokenTtl = $tokenTtl;
    }

    /**
     * Request reset user password: show form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function requestAction(Request $request)
    {
        // Redirect logged in user to profile page
        if ($this->isGranted('ROLE_BASIC')) {
            return $this->redirectToRoute('fos_user_profile_show');
        }

        $form = $this->createRequestForm($request->query->get('email'));

        return $this->render('@User/Resetting/request.html.twig', [
            'email' => $request->query->get('email'),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Request reset user password: submit form and send email.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendEmailAction(Request $request)
    {
        $form = $this->createRequestForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@User/Resetting/request.html.twig', [
                'email' => $request->request->get('username'),
                'form' => $form->createView(),
            ]);
        }

        $username = $request->request->get('username');

        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (null !== $user && !$user->isPasswordRequestNonExpired($this->retryTtl)) {
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->mailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $this->userManager->updateUser($user);
        }

        return $this->redirectToRoute('fos_user_resetting_check_email', ['username' => $username]);
    }

    /**
     * Tell the user to check his email provider.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkEmailAction(Request $request)
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            // the user does not come from the sendEmail action
            return $this->redirectToRoute('fos_user_resetting_request');
        }

        return $this->render('@User/Resetting/check_email.html.twig', [
            'tokenLifetime' => ceil($this->retryTtl / 3600),
        ]);
    }

    /**
     * Reset user password.
     *
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function resetAction(Request $request, $token)
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        if (!$user->isPasswordRequestNonExpired($this->tokenTtl)) {
            return $this->redirectToRoute('fos_user_resetting_request');
        }

        $form = $this->formFactory->createNamed('fos_user_resetting_form', ResettingFormType::class, $user, [
            'validation_groups' => ['ResetPassword', 'Default'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $user->setEnabled(true);

            $this->userManager->updateUser($user);

            $this->addFlash('success', $this->translator->trans('resetting.flash.success', [], 'FOSUserBundle'));

            try {
                $this->loginManager->logInUser($this->firewallName, $user);

                $user->setLastLogin(new \DateTime());
                $this->userManager->updateUser($user);
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
            }

            return $this->redirectToRoute('fos_user_profile_show');
        }

        return $this->render('@User/Resetting/reset.html.twig', [
            'token' => $token,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Anonymous form (no name prefix), the username is read from the request itself.
     */
    private function createRequestForm($email = null)
    {
        return $this->formFactory->createNamedBuilder('', FormType::class, null, [
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ])
            ->setMethod('POST')
            ->add('username', TextType::class, [
                'label' => 'resetting.request.username',
                'translation_domain' => 'FOSUserBundle',
                'data' => $email,
                'required' => true,
            ])
            ->add('recaptcha', RecaptchaType::class, [
                'attr' => [
                    'options' => [
                        'theme' => 'light',
                        'type' => 'image',
                        'size' => 'invisible',
                        'bind' => 'btn-reset-cb',
                    ],
                ],
                'mapped' => false,
                'constraints' => [
                    new Recaptcha(),
                ],
            ])
            ->getForm();
    }
}
