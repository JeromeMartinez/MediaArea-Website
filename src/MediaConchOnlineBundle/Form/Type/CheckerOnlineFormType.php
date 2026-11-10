<?php

namespace MediaConchOnlineBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckerOnlineFormType extends CheckerBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('file', UrlType::class, [
                'attr' => ['pattern' => '.{10,512}'],
                'label' => 'URL of file',
                'default_protocol' => 'http',
            ])
            ->add('check', SubmitType::class, ['attr' => ['class' => 'btn-warning'], 'label' => 'Check file']);
    }

    public function getBlockPrefix(): string
    {
        return 'checkerOnline';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
