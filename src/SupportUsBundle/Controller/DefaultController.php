<?php

namespace SupportUsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PaymentBundle\Entity\Order;
use PaymentBundle\Form\ChoosePaymentMethodType;
use PaymentBundle\Lib\IpToCountry;
use PaymentBundle\Lib\IpToCurrency;
use PaymentBundle\Lib\VatCalculator;
use SupportUsBundle\Lib\Corporate;
use SupportUsBundle\Lib\Individual;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;

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

    #[Route('/SupportUs', name: 'supportUs_about')]
    #[Template('@SupportUs/Default/about.html.twig')]
    public function aboutAction()
    {
        return ['noAds' => true];
    }

    #[Route('/SupportUs/Individual', name: 'supportUs_individual')]
    #[Template('@SupportUs/Default/individual.html.twig')]
    public function individualAction(Request $request)
    {
        $ipToCurrency = new IpToCurrency($request->getClientIp());
        $ipToCountry = new IpToCountry($request->getClientIp());
        $vat = new VatCalculator();
        $vat->setCountry($ipToCountry->getCountryIsoCode('FR'));

        $form = $this->paymentForm(
            $request,
            'payment_orders_paymentcreateindividual',
            'supportUs_individual',
            $ipToCurrency->getCurrency(),
            'individual'
        );

        if ($form instanceof RedirectResponse) {
            return $form;
        }

        return [
            'noAds' => true,
            'country' => $ipToCountry,
            'currency' => $ipToCurrency,
            'vatRate' => $vat->getVatRate(),
            'form' => $form->createView(),
            'individual' => new Individual(),
        ];
    }

    #[Route('/SupportUs/Corporate', name: 'supportUs_corporate')]
    #[Template('@SupportUs/Default/corporate.html.twig')]
    public function corporateAction(Request $request)
    {
        $ipToCurrency = new IpToCurrency($request->getClientIp());
        $ipToCountry = new IpToCountry($request->getClientIp());
        $vat = new VatCalculator();
        $vat->setCountry($ipToCountry->getCountryIsoCode('FR'));

        if (0 == $vat->getVatRate()) {
            $form = $this->paymentForm(
                $request,
                'payment_orders_paymentcreatecorporate',
                'supportUs_corporate',
                $ipToCurrency->getCurrency(),
                'corporate'
            );

            if ($form instanceof RedirectResponse) {
                return $form;
            }
        }

        return [
            'noAds' => true,
            'country' => $ipToCountry,
            'currency' => $ipToCurrency,
            'form' => isset($form) ? $form->createView() : false,
            'corporate' => new Corporate(),
        ];
    }

    #[Route('/SupportUs/Custom', name: 'supportUs_custom')]
    #[Template('@SupportUs/Default/custom.html.twig')]
    public function customAction(Request $request)
    {
        $ipToCountry = new IpToCountry($request->getClientIp());

        if (!in_array($request->query->get('currency'), ['AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'])) {
            $this->addFlash('danger', 'Currency error');
        } else {
            if (!$request->query->get('amount') || 1 > (int) $request->query->get('amount')) {
                $this->addFlash('danger', 'Amount error');
            } else {
                $form = $this->paymentForm(
                    $request,
                    'payment_orders_paymentcreatecustom',
                    'supportUs_custom',
                    $request->query->get('currency'),
                    'custom',
                    ['amount' => (int) $request->query->get('amount'), 'currency' => $request->query->get('currency')],
                    (int) $request->query->get('amount')
                );

                if ($form instanceof RedirectResponse) {
                    return $form;
                }
            }
        }

        return [
            'noAds' => true,
            'country' => $ipToCountry,
            'currency' => $request->query->get('currency'),
            'amount' => $request->query->get('amount'),
            'form' => isset($form) ? $form->createView() : false,
        ];
    }

    #[Route('/SupportUs/NoTimeToWait', name: 'supportUs_notimetowait')]
    #[Template('@SupportUs/Default/noTimeToWait.html.twig')]
    public function noTimeToWaitAction(Request $request)
    {
        $ipToCurrency = new IpToCurrency($request->getClientIp());
        $ipToCountry = new IpToCountry($request->getClientIp());
        $vat = new VatCalculator();
        $vat->setCountry($ipToCountry->getCountryIsoCode('FR'));

        $form = $this->paymentForm(
            $request,
            'payment_orders_paymentcreateindividual',
            'supportUs_individual',
            $ipToCurrency->getCurrency(),
            'individual'
        );

        if ($form instanceof RedirectResponse) {
            return $form;
        }

        return [
            'noAds' => true,
            'country' => $ipToCountry,
            'currency' => $ipToCurrency,
            'vatRate' => $vat->getVatRate(),
            'form' => $form->createView(),
            'individual' => new Individual(),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function paymentForm(
        Request $request,
        string $returnRoute,
        string $cancelRoute,
        string $currency,
        string $type = 'individual',
        array $cancelRouteParams = [],
        int $amount = 1
    ) {
        if ('POST' === $request->getMethod() &&
            null !== $payment = ($request->request->all()['ma_choose_payment_method'] ?? null)) {
            $amount = $payment['amount'];
            if (!is_numeric($amount) || $amount < 1) {
                throw new \Exception('Error Processing Request', 1);
            }

            $em = $this->doctrine->getManager();
            $order = new Order($amount);
            $order->setType($type);
            $em->persist($order);
            $em->flush();

            $returnUrl = $this->generateUrl(
                $returnRoute,
                ['id' => $order->getId(), 'routeParams' => $cancelRouteParams],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $cancerlUrl = $this->generateUrl(
                $cancelRoute,
                $cancelRouteParams,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $form = $this->createForm(ChoosePaymentMethodType::class, null, [
            'amount_field_type' => 'text',
            'amount' => $amount,
            'currency' => $currency,
            // 'default_method' => 'stripe_credit_card',
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

        $form->handleRequest($request);

        if ('POST' === $request->getMethod() && $form->isSubmitted() && $form->isValid()) {
            $ppc = $this->ppc;
            $ppc->createPaymentInstruction($instruction = $form->getData());

            $order->setPaymentInstruction($instruction);
            $em->persist($order);
            $em->flush();

            return $this->redirect(
                $this->generateUrl($returnRoute, ['id' => $order->getId(), 'routeParams' => $cancelRouteParams])
            );
        }

        return $form;
    }

    #[Route('/SupportUs/FAQ', name: 'supportUs_faq')]
    #[Template('@SupportUs/Default/faq.html.twig')]
    public function faqAction()
    {
        return ['noAds' => true];
    }

    #[Route('/SupportUs/Sponsors', name: 'supportUs_sponsors_list')]
    #[Template('@SupportUs/Default/sponsorsList.html.twig')]
    public function sponsorsListAction()
    {
        return ['noAds' => true];
    }

    #[Route('/SupportUs/Supporters', name: 'supportUs_supporters_list')]
    #[Template('@SupportUs/Default/supportersList.html.twig')]
    public function supportersListAction()
    {
        return ['noAds' => true];
    }
}
