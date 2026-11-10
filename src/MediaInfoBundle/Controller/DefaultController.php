<?php

namespace MediaInfoBundle\Controller;

use AppBundle\Form\Type\RecaptchaType;
use AppBundle\Validator\Constraints\Recaptcha;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PaymentBundle\Entity\Order;
use PaymentBundle\Form\ChoosePaymentMethodType;
use PaymentBundle\Lib\IpToCountry;
use PaymentBundle\Lib\IpToCurrency;
use MediaInfoBundle\Lib\DownloadInfo\MediaInfoDownloadInfo;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultController extends AbstractController
{
    private $doctrine;
    private $ppc;

    public function __construct(ManagerRegistry $doctrine, PluginControllerInterface $ppc)
    {
        $this->doctrine = $doctrine;
        $this->ppc = $ppc;
    }

    #[Route('/{_locale}/MediaInfo', name: 'mi_home', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Default/about.html.twig')]
    public function aboutAction(Request $request, MediaInfoDownloadInfo $downloadInfo)
    {
        // Donated request
        if (null !== $request->query->get('Donated')) {
            $request->attributes->set('setDonatedCookie', 'Y');

            return $this->redirectToRoute('mi_home', array('_locale' => $request->getLocale()), 302);
        }

        // New version request (increment counter if not a donor)
        if (null !== $request->query->get('NewVersionRequested')) {
            if ('Y' != $request->attributes->get('donated')) {
                if (ctype_digit($request->attributes->get('donated'))) {
                    $request->attributes->set('setDonatedCookie', $request->attributes->get('donated') + 1);
                } else {
                    $request->attributes->set('setDonatedCookie', 1);
                }
            }

            return $this->redirectToRoute('mi_home', array('_locale' => $request->getLocale()), 302);
        }

        // Download infos
        $downloadInfo->setUserAgent($request->headers->get('User-Agent'));
        $downloadInfo->parse();

        // RTL header
        if ('fa' == $request->getLocale()) {
            $rtl = true;
        }

        return array(
            'downloadInfo' => $downloadInfo,
            'rtl' => isset($rtl),
        );
    }

    #[Route('/{_locale}/MediaInfo/Screenshots', name: 'mi_screenshots', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Default/screenshots.html.twig')]
    public function screenshotsAction()
    {
        return array();
    }

    #[Route('/{_locale}/MediaInfo/Donate', name: 'mi_donate', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Default/donate.html.twig')]
    public function donateAction(Request $request)
    {
        if ('POST' === $request->getMethod() &&
            null !== $payment = ($request->request->all()['ma_choose_payment_method'] ?? null)) {
            $amount = $payment['amount'];
            if (!is_numeric($amount) || $amount < 1) {
                throw new \Exception('Error Processing Request', 1);
            }

            $em = $this->doctrine->getManager();
            $order = new Order($amount);
            $em->persist($order);
            $em->flush();

            $returnUrl = $this->generateUrl(
                'payment_orders_paymentcreate',
                ['id' => $order->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $cancerlUrl = $this->generateUrl(
                'mi_donate',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $ipToCurrency = new IpToCurrency($request->getClientIp());

        $form = $this->createForm(ChoosePaymentMethodType::class, null, [
            'amount' => $amount ?? 1,
            'currency' => $ipToCurrency->getCurrency(),
            //'default_method' => 'stripe_credit_card',
            'default_method' => 'paypal_express_checkout',
            'predefined_data' => [
                'adyen_credit_card' => [
                    'return_url' => $returnUrl ?? '',
                ],
                'paypal_express_checkout' => [
                    'return_url' => $returnUrl ?? '',
                    'cancel_url' => $cancerlUrl ?? '',
                    'useraction' => 'commit',
                ],
            ],
            'method_options' => [
                'paypal_express_checkout' => [
                    'label' => false,
                ],
                /*
                'stripe_credit_card' => [
                    'data' => ['name' => (null !== $this->getUser()) ? $this->getUser()->getNameForDisplay() : ''],
                ],
                */
            ],
            'allowed_methods' => ['paypal_express_checkout'/*, 'stripe_credit_card'*/],
        ]);

        $form->add('recaptcha', RecaptchaType::class, [
            'attr' => [
                'options' => [
                    'theme' => 'light',
                    'type' => 'image',
                    'size' => 'invisible',
                    'bind' => 'btn-pay-cb',
                ],
            ],
            'mapped' => false,
            'constraints' => [
                new Recaptcha(),
            ],
        ]);

        $form->handleRequest($request);

        if ('POST' === $request->getMethod() && $form->isSubmitted() && $form->isValid()) {
            $ppc = $this->ppc;
            $ppc->createPaymentInstruction($instruction = $form->getData());

            $order->setPaymentInstruction($instruction);
            $em->persist($order);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_orders_paymentcreate', [
                'id' => $order->getId(),
            ]));
        }

        $ipToCountry = new IpToCountry($request->getClientIp());

        return [
            'noAds' => true,
            'form' => $form->createView(),
            'country' => $ipToCountry->getCountryName(false),
            'currency' => $ipToCurrency->getCurrency(),
        ];
    }

    #[Route('/{_locale}/MediaInfo/Testimonials', name: 'mi_testimonials', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Default/testimonials.html.twig')]
    public function testimonialsAction()
    {
        return array('noAds' => true);
    }

    #[Route('/{_locale}/MediaInfo/License', name: 'mi_license', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Default/license.html.twig')]
    public function licenseAction()
    {
        return array();
    }

    #[Route('/{_locale}/MediaInfo/Prices', name: 'mi_prices', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Default/prices.html.twig')]
    public function pricesAction()
    {
        return array('noAds' => true);
    }

    #[Route('/{_locale}/MediaInfo/Bundled', name: 'mi_bundled', requirements: ['_locale' => '%app.locales%'])]
    #[Template('@MediaInfo/Default/bundled.html.twig')]
    public function bundledAction()
    {
        return array();
    }

    #[Route('/MediaInfo/ChangeLog', name: 'mi_changelog')]
    #[Template('@MediaInfo/Default/changelog.html.twig')]
    public function changelogAction()
    {
        return array();
    }
}
