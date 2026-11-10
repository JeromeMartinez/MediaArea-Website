<?php

namespace PaymentBundle\Form;

use PaymentBundle\Lib\IpToCurrency;
use PaymentBundle\Method\MethodRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Amount and payment method of an order.
 */
class ChoosePaymentMethodType extends AbstractType
{
    private $methods;
    private $requestStack;

    public function __construct(MethodRegistry $methods, RequestStack $requestStack)
    {
        $this->methods = $methods;
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ('text' == $options['amount_field_type']) {
            $builder->add('amount', TextType::class, [
                'mapped' => false,
                'data' => $options['amount'],
            ]);
        } else {
            $ipToCurrency = new IpToCurrency($this->requestStack->getCurrentRequest()->getClientIp());
            $builder->add('amount', ChoiceType::class, [
                'mapped' => false,
                'choices' => $ipToCurrency->getAmountChoices(),
                'data' => $ipToCurrency->getAmountDefault(),
            ]);
        }

        $choices = [];
        foreach ($options['allowed_methods'] as $name) {
            $choices[$this->methods->get($name)->getLabel()] = $name;
        }

        $builder->add('method', ChoiceType::class, [
            'choices' => $choices,
            'expanded' => true,
            'data' => $options['default_method'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'amount_field_type' => 'list',
            'amount' => null,
            'allowed_methods' => array_keys($this->methods->all()),
            'default_method' => static function (Options $options) {
                return $options['allowed_methods'][0] ?? null;
            },
        ]);

        $resolver->setAllowedTypes('amount_field_type', 'string');
        $resolver->setAllowedTypes('allowed_methods', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'ma_choose_payment_method';
    }
}
