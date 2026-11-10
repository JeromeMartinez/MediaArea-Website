<?php

use Composer\InstalledVersions;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Twig\Extra\TwigExtraBundle\TwigExtraBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new AppBundle\AppBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Symfony\UX\TwigComponent\TwigComponentBundle(),
            new EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new JMS\Payment\CoreBundle\JMSPaymentCoreBundle(),
            new JMS\Payment\PaypalBundle\JMSPaymentPaypalBundle(),
            new KJ\Payment\StripeBundle\KJPaymentStripeBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new MediaTraceBundle\MediaTraceBundle(),
            new BWFMetaEditBundle\BWFMetaEditBundle(),
            new QCToolsBundle\QCToolsBundle(),
            new DVAnalyzerBundle\DVAnalyzerBundle(),
            new OllistdBundle\OllistdBundle(),
            new AVIMetaEditBundle\AVIMetaEditBundle(),
            new MediaInfoBundle\MediaInfoBundle(),
            new UserBundle\UserBundle(),
            new PaymentBundle\PaymentBundle(),
            new SupportUsBundle\SupportUsBundle(),
            new MOVMetaEditBundle\MOVMetaEditBundle(),
            new NoTimeToWaitBundle\NoTimeToWaitBundle(),
            new BlogBundle\BlogBundle(),
            new VoteBundle\VoteBundle(),
            new RAWcookedBundle\RAWcookedBundle(),
            new DVRescueBundle\DVRescueBundle(),
            new MediaConchBundle\MediaConchBundle(),
            new MediaBinBundle\MediaBinBundle(),
            new MediaConchOnlineBundle\MediaConchOnlineBundle(),
            new LeaveSDBundle\LeaveSDBundle(),
            new EmbARCBundle\EmbARCBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Symfony\Bundle\MakerBundle\MakerBundle();
            }
        }

        return $bundles;
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/app/config/config_'.$this->getEnvironment().'.yml');

        // The mailer_* parameters of parameters.yml are loaded at this point
        $loader->load(static function (ContainerBuilder $container): void {
            $container->setParameter('mailer_dsn', self::getMailerDsn(
                $container->getParameter('mailer_transport'),
                $container->getParameter('mailer_host'),
                $container->getParameter('mailer_user'),
                $container->getParameter('mailer_password')
            ));

            $doctrine = self::getDoctrineLegacyConfiguration();
            if ([] !== $doctrine) {
                $container->loadFromExtension('doctrine', $doctrine);
            }
        });
    }

    /**
     * Options of DoctrineBundle which exist only for the previous versions of DoctrineBundle, Doctrine DBAL
     * or Doctrine ORM: they select what the current versions always do.
     */
    private static function getDoctrineLegacyConfiguration(): array
    {
        $config = [];

        if (method_exists(Doctrine\DBAL\Connection::class, 'getEventManager')) {
            // Doctrine DBAL 3: nested transactions with savepoints, no type in the comment of the columns
            $config['dbal'] = ['use_savepoints' => true, 'disable_type_comments' => true];
        }

        if (class_exists(Doctrine\ORM\Mapping\Driver\AnnotationDriver::class)) {
            // Doctrine ORM 2: the fields are reported by the class which declares them, the proxies of the entities
            // are lazy ghost objects when Symfony has them (up to version 7)
            $config['orm'] = [
                'report_fields_where_declared' => true,
                'enable_lazy_ghost_objects' => trait_exists(Symfony\Component\VarExporter\LazyGhostTrait::class),
            ];
        }

        if (version_compare(InstalledVersions::getVersion('doctrine/doctrine-bundle') ?? '3.0', '3.0', '<')) {
            // No lookup of entities with the route placeholders, entities are lazy objects of PHP when possible
            $config['orm']['controller_resolver'] = ['auto_mapping' => false];
            if (\PHP_VERSION_ID >= 80400
                && method_exists(Doctrine\ORM\Configuration::class, 'enableNativeLazyObjects')
            ) {
                $config['orm']['enable_native_lazy_objects'] = true;
            }
        }

        return $config;
    }

    /**
     * Build the mailer DSN from the mailer_* parameters.
     */
    private static function getMailerDsn(?string $transport, ?string $host, ?string $user, ?string $password): string
    {
        switch ($transport) {
            case 'smtp':
                $credentials = '';
                if (null !== $user && '' !== $user) {
                    $credentials = rawurlencode($user);
                    if (null !== $password && '' !== $password) {
                        $credentials .= ':'.rawurlencode($password);
                    }
                    $credentials .= '@';
                }

                // Plain SMTP without STARTTLS negotiation, as with the former Swiftmailer configuration.
                // It can not be disabled before Symfony 7.1: the certificate of the server is then not checked,
                // a local server usually has not a valid one.
                $options = method_exists(EsmtpTransport::class, 'setAutoTls') ? 'auto_tls=false' : 'verify_peer=0';

                return 'smtp://'.$credentials.($host ?: '127.0.0.1').'?'.$options;
            case 'sendmail':
                return 'sendmail://default';
            case 'mail':
                return 'native://default';
            case null:
            case 'null':
                return 'null://null';
        }

        // Anything else is used as it is, e.g. a full DSN
        return $transport;
    }
}
