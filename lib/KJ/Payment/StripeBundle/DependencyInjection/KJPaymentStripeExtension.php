<?php

namespace KJ\Payment\StripeBundle\DependencyInjection;

use KJ\Payment\StripeBundle\Client\Client;
use KJ\Payment\StripeBundle\Form\StripeCreditCardType;
use KJ\Payment\StripeBundle\Plugin\StripeCreditCardPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KJPaymentStripeExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('payment.stripe.api_key', $config['api_key']);
        $container->setParameter('payment.stripe.api_version', $config['api_version']);

        // The services which were defined in Resources/config/services.xml
        // The class of the form type can be changed with this parameter (e.g. StripeCreditCardMinimumType)
        if (!$container->hasParameter('payment.form.stripe_credit_card_type.class')) {
            $container->setParameter('payment.form.stripe_credit_card_type.class', StripeCreditCardType::class);
        }

        $container->register('payment.plugin.stripe_credit_card', StripeCreditCardPlugin::class)
            ->addArgument(new Reference('validator'))
            ->addArgument(new Reference('payment.stripe.client'))
            ->addTag('payment.plugin');

        $container->register('payment.form.stripe_credit_card_type', '%payment.form.stripe_credit_card_type.class%')
            ->addTag('payment.method_form_type')
            ->addTag('form.type', array('alias' => 'stripe_credit_card'));

        $container->register('payment.stripe.client', Client::class)
            ->addArgument('%payment.stripe.api_key%')
            ->addArgument('%payment.stripe.api_version%');
    }
}
