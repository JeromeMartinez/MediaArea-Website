<?php

namespace UserBundle\Form\Type;

use AppBundle\Form\Type\RecaptchaType;
use AppBundle\Validator\Constraints\Recaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\User;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegistrationFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'form.email', 'translation_domain' => 'FOSUserBundle'])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => ['label' => 'form.password'],
                'second_options' => ['label' => 'form.password_confirmation'],
                'invalid_message' => 'fos_user.password.mismatch',
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
            ->add('companyUrl', TextType::class, ['mapped' => false, 'required' => false])
            ->add('companyName')
            ->add('newsletter')
            ->add('realUserName', HiddenType::class)
            ->add('recaptcha', RecaptchaType::class, [
                'attr' => [
                    'options' => [
                        'theme' => 'light',
                        'type' => 'image',
                        'size' => 'invisible',
                        'bind' => 'btn-register-cb',
                    ],
                ],
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new Recaptcha(),
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['companyUrl']) || !empty($data['companyUrl'])) {
                $form->addError(new FormError('Company URL error'));
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event): void {
            $user = $event->getData();
            $form = $event->getForm();

            if ($form->isValid() && null !== $user && null === $user->getUsername()) {
                $user->setUsername(\uniqid());
                $user->setRealUserName(0);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_token_id' => 'registration',
        ]);
    }
}
