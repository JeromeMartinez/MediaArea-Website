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
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use UserBundle\Form\Type\ResettingFormType;
use UserBundle\Lib\UserManager;
use UserBundle\Security\LoginManager;

/**
 * Controller managing the resetting of the password.
 *
 * The reset requests and their tokens are handled by SymfonyCastsResetPasswordBundle, the pages are the ones
 * the website had with FOSUserBundle.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResettingController extends AbstractController
{
    private $formFactory;
    private $userManager;
    private $resetPasswordHelper;
    private $loginManager;
    private $mailer;
    private $translator;
    private $firewallName;
    private $retryTtl;

    /**
     * @param int $retryTtl Delay before a new reset email can be requested, in seconds
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserManager $userManager,
        ResetPasswordHelperInterface $resetPasswordHelper,
        LoginManager $loginManager,
        UserMailer $mailer,
        TranslatorInterface $translator,
        string $firewallName,
        int $retryTtl
    ) {
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->loginManager = $loginManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->firewallName = $firewallName;
        $this->retryTtl = $retryTtl;
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

        if (null !== $user) {
            try {
                $token = $this->resetPasswordHelper->generateResetToken($user);

                $this->mailer->sendResettingEmailMessage($user, $token->getToken());
            } catch (ResetPasswordExceptionInterface $e) {
                // An email was sent to the user a short time ago: no other one, and the same page is displayed
                // so that it does not tell if the user exists
            }
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
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser((string) $token);
        } catch (ExpiredResetPasswordTokenException $e) {
            return $this->redirectToRoute('fos_user_resetting_request');
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $form = $this->formFactory->createNamed('fos_user_resetting_form', ResettingFormType::class, $user, [
            'validation_groups' => ['ResetPassword', 'Default'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEnabled(true);

            $this->userManager->updateUser($user);

            // The link is used: it does not work any more, as the other ones sent to the user
            $this->resetPasswordHelper->removeResetRequest((string) $token);

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
