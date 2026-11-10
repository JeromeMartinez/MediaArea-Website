<?php

namespace JMS\Payment\PaypalBundle\DependencyInjection;

use JMS\Payment\PaypalBundle\Client\Authentication\TokenAuthenticationStrategy;
use JMS\Payment\PaypalBundle\Client\Client;
use JMS\Payment\PaypalBundle\Form\ExpressCheckoutType;
use JMS\Payment\PaypalBundle\Plugin\ExpressCheckoutPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/*
 * Copyright 2010 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class JMSPaymentPaypalExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('payment.paypal.username', $config['username']);
        $container->setParameter('payment.paypal.password', $config['password']);
        $container->setParameter('payment.paypal.signature', $config['signature']);
        $container->setParameter('payment.paypal.express_checkout.return_url', $config['return_url']);
        $container->setParameter('payment.paypal.express_checkout.cancel_url', $config['cancel_url']);
        $container->setParameter('payment.paypal.express_checkout.notify_url', $config['notify_url']);
        $container->setParameter('payment.paypal.express_checkout.useraction', $config['useraction']);
        $container->setParameter('payment.paypal.debug', $config['debug']);

        // The services which were defined in Resources/config/services.xml
        $container->register('payment.paypal.authentication_strategy.token', TokenAuthenticationStrategy::class)
            ->addArgument('%payment.paypal.username%')
            ->addArgument('%payment.paypal.password%')
            ->addArgument('%payment.paypal.signature%');

        $container->setAlias('payment.paypal.authentication_strategy', 'payment.paypal.authentication_strategy.token');

        $container->setAlias('payment.plugin.paypal_express_checkout', ExpressCheckoutPlugin::class);
        $container->register(ExpressCheckoutPlugin::class, ExpressCheckoutPlugin::class)
            ->addArgument('%payment.paypal.express_checkout.return_url%')
            ->addArgument('%payment.paypal.express_checkout.cancel_url%')
            ->addArgument(new Reference('payment.paypal.client'))
            ->addArgument('%payment.paypal.express_checkout.notify_url%')
            ->addArgument('%payment.paypal.express_checkout.useraction%')
            ->addTag('payment.plugin');

        $container->register('payment.paypal.client', Client::class)
            ->addArgument(new Reference('payment.paypal.authentication_strategy'))
            ->addArgument('%payment.paypal.debug%');

        $container->register('payment.form.paypal_express_checkout_type', ExpressCheckoutType::class)
            ->addTag('payment.method_form_type')
            ->addTag('form.type', array('alias' => 'paypal_express_checkout'));
    }
}
