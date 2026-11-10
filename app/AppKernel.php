<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Intl\Countries;

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

    public function boot(): void
    {
        // Kosovo has no ISO 3166-1 code, keep it in the countries with the code it uses (XK)
        Countries::withUserAssigned(true);

        parent::boot();
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
        });
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

                // Plain SMTP without STARTTLS negotiation, as with the former Swiftmailer configuration
                return 'smtp://'.$credentials.($host ?: '127.0.0.1').'?auto_tls=false';
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
