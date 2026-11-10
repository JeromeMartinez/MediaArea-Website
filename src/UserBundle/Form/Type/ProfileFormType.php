<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\User;

class ProfileFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'form.email', 'translation_domain' => 'FOSUserBundle'])
            ->add('current_password', PasswordType::class, [
                'label' => 'form.current_password',
                'translation_domain' => 'FOSUserBundle',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new UserPassword(
                        message: 'fos_user.current_password.invalid',
                        groups: empty($options['validation_groups']) ? null : [reset($options['validation_groups'])],
                    ),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('username', null, [
                'label' => 'form.username',
                'translation_domain' => 'FOSUserBundle',
                'required' => false,
            ])
            ->add('name')
            ->add('country', CountryCustomType::class)
            ->add('language', LanguageCustomType::class)
            ->add('professional', ProfessionalType::class)
            ->add('companyName')
            ->add('newsletter');

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if (null !== $user && 0 == $user->getRealUserName()) {
                $form->add('oldname', HiddenType::class, ['mapped' => false, 'data' => $user->getUsername()]);
                $user->setUsername(null);
                $event->setData($user);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($form->isValid() && null !== $user) {
                // User have random username
                if (0 == $user->getRealUsername()) {
                    // User do not have fill his username
                    if (null === $user->getUsername()) {
                        $user->setUsername($form['oldname']->getData());
                    } else {
                        // User have fill his username
                        $user->setRealUserName(1);
                    }
                } elseif (null === $user->getUsername()) {
                    // User have remove his username
                    $user->setUsername(uniqid());
                    $user->setRealUserName(0);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_token_id' => 'profile',
        ]);
    }
}
