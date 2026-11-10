<?php

namespace UserBundle\Controller;

use AppBundle\Form\Type\RecaptchaType;
use AppBundle\Validator\Constraints\Recaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $authenticationUtils;
    private $tokenManager;
    private $formFactory;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        CsrfTokenManagerInterface $tokenManager,
        FormFactoryInterface $formFactory
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->tokenManager = $tokenManager;
        $this->formFactory = $formFactory;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function loginAction(Request $request)
    {
        // Redirect logged in user to profile page
        if ($this->isGranted('ROLE_BASIC')) {
            return $this->redirectToRoute('fos_user_profile_show');
        }

        // Store referer
        if ('GET' == $request->getMethod() && $request->headers->get('referer') != $request->getUri()) {
            $request->getSession()->set('loginRedirect', $request->headers->get('referer'));
        }

        return $this->renderLogin([
            // get the error if any, it is removed from the session
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
            // last username entered by the user
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'csrf_token' => $this->tokenManager->getToken('authenticate')->getValue(),
        ]);
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    protected function renderLogin(array $data)
    {
        $form = $this->formFactory->createNamed('', FormType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->add('recaptcha', RecaptchaType::class, [
            'attr' => [
                'options' => [
                    'theme' => 'light',
                    'type' => 'image',
                    'size' => 'invisible',
                    'bind' => 'btn-login-cb',
                    'callback' => 'onLoginReCaptchaSuccess',
                ],
            ],
            'mapped' => false,
            'constraints' => [
                new Recaptcha(),
            ],
        ]);

        $data['captcha_form'] = $form->createView();

        return $this->render('@User/Security/login.html.twig', $data);
    }
}
