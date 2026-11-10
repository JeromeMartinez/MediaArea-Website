<?php

namespace MediaConchOnlineBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckerRepositoryFormType extends CheckerBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('check', SubmitType::class, ['attr' => ['class' => 'btn-warning'], 'label' => 'Check files']);
    }

    public function getBlockPrefix(): string
    {
        return 'checkerRepository';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
