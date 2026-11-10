<?php

namespace UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MediaConchOnlineBundle\Mailer\UserMailer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UserBundle\Entity\User;
use UserBundle\Form\Type\CountryCustomType;
use UserBundle\Form\Type\LanguageCustomType;
use UserBundle\Form\Type\SettingsFormType;
use UserBundle\Form\Type\GuestRegisterFormType;
use PaymentBundle\Entity\Invoice;
use UserBundle\Lib\Quotas\Quotas;
use UserBundle\Lib\Settings\SettingsManager;
use UserBundle\Lib\TokenGenerator;
use UserBundle\Lib\UserManager;
use VoteBundle\Entity\Vote;
use VoteBundle\Entity\Feature;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserController extends AbstractController
{
    private $em;
    private $userManager;
    private $tokenGenerator;
    private $mailer;

    public function __construct(
        EntityManagerInterface $em,
        UserManager $userManager,
        TokenGenerator $tokenGenerator,
        UserMailer $mailer
    ) {
        $this->em = $em;
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }

    #[Route('/settings', name: 'user_settings')]
    #[Template('@User/User/settings.html.twig')]
    public function settingsAction(Request $request, SettingsManager $settings)
    {
        $form = $this->createForm(SettingsFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $settings->setDefaultPolicy($data['policy']);
            $settings->setDefaultDisplay($data['display']);
            $settings->setDefaultVerbosity($data['verbosity']);

            $this->addFlash('success', 'Settings successfully saved');

            return $this->redirectToRoute('user_settings');
        }

        return ['form' => $form->createView()];
    }

    #[Route('/guest', name: 'user_guest_help')]
    #[Template('@User/User/guestHelp.html.twig')]
    public function guestHelpAction()
    {
        return [];
    }

    #[Route('/guest/register', name: 'user_guest_register')]
    public function guestRegisterAction(Request $request, Quotas $quotas)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User || !$user->hasRole('ROLE_GUEST')) {
            throw new AccessDeniedException('You do not have access to this section.');
        }

        // Store referer
        if ('GET' == $request->getMethod() && $request->headers->get('referer') != $request->getUri()) {
            $request->getSession()->set('registerRedirect', $request->headers->get('referer'));
        }

        $form = $this->createForm(GuestRegisterFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEnabled(true);

            $this->addFlash('success', 'You have been registered successfully');

            if ($this->getParameter('user.registration.confirmation.enabled')) {
                $request->getSession()->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';

                // Keep guest user enable to avoid redirect to login page
                if (null === $user->getConfirmationToken()) {
                    $user->setConfirmationToken($this->tokenGenerator->generateToken());
                }

                $this->mailer->sendConfirmationEmailMessage($user);
            } else {
                $route = 'fos_user_registration_confirmed';

                // Remove guest token cookie
                $request->attributes->set('remove_guest_cookie', true);

                // Switch role to basic
                $user->removeRole('ROLE_GUEST');
                $user->addRole('ROLE_BASIC');

                // Remove guest token in DB
                $token = $user->getGuestToken();
                $user->setGuestToken(null);
                $this->em->remove($token);

                // Set quotas for new user with mail activation disabled
                $quotas->resetQuotas();
            }

            // Update user
            $this->userManager->updateUser($user);

            return $this->redirectToRoute($route);
        }

        return $this->render('@User/Registration/guest_register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show the user.
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    #[Route('/profile/')]
    #[Template('@User/User/showProfile.html.twig')]
    public function showProfileAction(Quotas $quotas)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if ($user->getLanguage()) {
            $languages = LanguageCustomType::getNames();
            $language = $languages[$user->getLanguage()] ?? null;
        }

        if ($user->getCountry()) {
            $countries = CountryCustomType::getNames();
            $country = $countries[$user->getCountry()] ?? null;
        }

        $invoices = $this->em
            ->getRepository(Invoice::class)
            ->findBy(['user' => $this->getUser()], ['date' => 'DESC']);

        return [
            'user' => $user,
            'language' => $language ?? null,
            'country' => $country ?? null,
            'invoices' => $invoices,
            'data' => [
                'id' => '',
                'date' => '',
                'amount' => '',
                'vat' => '',
                'total' => '',
                'product' => '',
                'country' => '',
                'currency' => '',
            ],
            'userVotes' => $this->em->getRepository(Vote::class)->getTotalVotesByUser($user),
            'userVotesFeatures' => $this->em->getRepository(Feature::class)->getFeaturesVotedByUser($user),
            'quotas' => $quotas->getQuotasForProfile(),
        ];
    }
}
