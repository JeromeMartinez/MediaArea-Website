<?php

namespace KJ\Payment\StripeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class StripeCreditCardMinimumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'Your name',
                'required' => false,
            ))
            ->add('number', TextType::class, array(
                'label' => 'Card number',
                'attr' => array(
                    'maxlength' => 19,
                ),
                'required' => false,
            ))
            ->add('exp_month', ChoiceType::class, array(
                'label' => 'Card expiry',
                'choices' => array('01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12'),
                'placeholder' => 'MM',
                'attr' => array(
                    'class' => 'input-mini',
                ),
                'required' => false,
            ))
            ->add('exp_year', ChoiceType::class, array(
                'label' => ' ',
                'placeholder' => 'YYYY',
                'choices' => array_combine(range(date('Y'), date('Y') + 20), range(date('Y'), date('Y') + 20)),
                'attr' => array(
                    'class' => 'input-small',
                ),
                'required' => false,
            ))
            ->add('cvc', TextType::class, array(
                'label' => 'CVC',
                'attr' => array(
                    'class' => 'input-mini',
                    'maxlength' => 4,
                ),
                'required' => false,
            ));
    }
}
