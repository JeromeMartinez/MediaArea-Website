<?php

namespace JMS\Payment\CoreBundle\DependencyInjection;

use JMS\Payment\CoreBundle\Command\GenerateKeyCommand;
use JMS\Payment\CoreBundle\Cryptography\DefusePhpEncryptionService;
use JMS\Payment\CoreBundle\Entity\Credit;
use JMS\Payment\CoreBundle\Entity\ExtendedDataType;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Form\ChoosePaymentMethodType;
use JMS\Payment\CoreBundle\Form\Transformer\ChoosePaymentMethodTransformer;
use JMS\Payment\CoreBundle\PluginController\EntityPluginController;
use JMS\Payment\CoreBundle\PluginController\PluginController;
use JMS\Payment\CoreBundle\PluginController\Result;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
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

class JMSPaymentCoreExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine', array(
            'dbal' => array(
                'types' => array(
                    ExtendedDataType::NAME => ExtendedDataType::class,
                ),
            ),
        ));
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->registerServices($container);

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs
        );

        $container->setParameter('payment.encryption.enabled', $config['encryption']['enabled']);

        if ($config['encryption']['enabled']) {
            $container->setParameter('payment.encryption', $config['encryption']['provider']);
            $container->setParameter('payment.encryption.secret', $config['encryption']['secret']);
            $container->setParameter('payment.encryption.defuse_php_encryption.secret', $config['encryption']['secret']);
        } else {
            $container->removeAlias('payment.encryption.defuse_php_encryption');
            $container->removeDefinition(DefusePhpEncryptionService::class);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new Configuration($this->getAlias());
    }

    /**
     * The services which were defined in Resources/config/payment.xml and commands.xml.
     */
    private function registerServices(ContainerBuilder $container)
    {
        $container->setParameter('payment.encryption.defuse_php_encryption.secret', '');

        $container->setAlias('payment.orm.entity_manager', 'doctrine.orm.default_entity_manager');

        $container->setAlias('payment.plugin_controller', PluginController::class)->setPublic(true);
        $container->setAlias(PluginController::class, EntityPluginController::class);
        $container->setAlias('payment.plugin_controller.entity', EntityPluginController::class);

        $container->register(EntityPluginController::class, EntityPluginController::class)
            ->addArgument(new Reference('payment.orm.entity_manager'))
            ->addArgument(array(
                'result_class' => Result::class,
                'payment_instruction_class' => PaymentInstruction::class,
                'payment_class' => Payment::class,
                'financial_transaction_class' => FinancialTransaction::class,
                'credit_class' => Credit::class,
            ))
            ->addArgument(new Reference('event_dispatcher'));

        $container->setAlias('payment.encryption.defuse_php_encryption', DefusePhpEncryptionService::class);
        $container->register(DefusePhpEncryptionService::class, DefusePhpEncryptionService::class)
            ->setPublic(true)
            ->addArgument('%payment.encryption.defuse_php_encryption.secret%')
            ->addTag('payment.encryption', array('alias' => 'defuse_php_encryption'));

        $container->register('payment.form.choose_payment_method_transformer', ChoosePaymentMethodTransformer::class);

        $container->register('payment.form.choose_payment_method_type', ChoosePaymentMethodType::class)
            ->addArgument(new Reference('payment.plugin_controller'))
            ->addTag('form.type', array('alias' => 'jms_choose_payment_method'))
            ->addMethodCall('setDataTransformer', array(new Reference('payment.form.choose_payment_method_transformer')));

        $container->register(GenerateKeyCommand::class, GenerateKeyCommand::class)
            ->addTag('console.command', array('command' => 'jms_payment_core:generate-key'));
    }
}
