<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use UserBundle\Entity\User;
use UserBundle\Form\Type\ChangePasswordFormType;
use UserBundle\Form\Type\ProfileFormType;
use UserBundle\Lib\UserManager;

/**
 * Controller managing the edition of the user profile and password.
 *
 * Derived from the profile and change password controllers of FOSUserBundle (MIT license).
 */
class ProfileController extends AbstractController
{
    private $formFactory;
    private $userManager;
    private $translator;

    public function __construct(
        FormFactoryInterface $formFactory,
        UserManager $userManager,
        TranslatorInterface $translator
    ) {
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    /**
     * Edit the user.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        // Guest user cannot edit their profile
        if ($user->hasRole('ROLE_GUEST')) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->formFactory->createNamed('fos_user_profile_form', ProfileFormType::class, $user, [
            'validation_groups' => ['UserProfile'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateUser($user);

            $this->addFlash('success', $this->translator->trans('profile.flash.updated', [], 'FOSUserBundle'));

            return $this->redirectToRoute('fos_user_profile_show');
        }

        return $this->render('@User/Profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Change user password.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->formFactory->createNamed(
            'fos_user_change_password_form',
            ChangePasswordFormType::class,
            $user,
            ['validation_groups' => ['ChangePassword', 'Default']]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateUser($user);

            $this->addFlash('success', $this->translator->trans('change_password.flash.success', [], 'FOSUserBundle'));

            return $this->redirectToRoute('fos_user_profile_show');
        }

        return $this->render('@User/ChangePassword/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
