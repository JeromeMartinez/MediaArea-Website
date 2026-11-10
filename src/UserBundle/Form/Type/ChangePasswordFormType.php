<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\User;

class ChangePasswordFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => ['label' => 'form.new_password'],
                'second_options' => ['label' => 'form.new_password_confirmation'],
                'invalid_message' => 'fos_user.password.mismatch',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_token_id' => 'change_password',
        ]);
    }
}
