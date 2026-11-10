<?php

namespace AppBundle\Form;

use AppBundle\Entity\Contact;
use AppBundle\Form\Type\RecaptchaType;
use AppBundle\Validator\Constraints\Recaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('company')
            ->add('email')
            ->add('subject')
            ->add('message', TextareaType::class, ['attr' => ['rows' => 10]])
            ->add('recaptcha', RecaptchaType::class, [
                'attr' => [
                    'options' => [
                        'theme' => 'light',
                        'type' => 'image',
                        'size' => 'invisible',
                        'bind' => 'btn-contact-send',
                    ],
                ],
                'mapped' => false,
                'constraints' => [new Recaptcha()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
