<?php

namespace PaymentBundle\Method;

use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction;
use Payum\Core\GatewayInterface;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;
use PaymentBundle\Entity\Payment;
use PaymentBundle\Simulation\SimulatedPaypalApi;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Paypal Express Checkout, with the gateway of Payum for the NVP API of Paypal.
 */
class PaypalExpressCheckoutMethod implements PaymentMethodInterface
{
    private $httpClient;
    private $requestStack;
    private $username;
    private $password;
    private $signature;
    private $sandbox;
    private $simulatedApi;
    private $paymentSimulation;
    private $gateway;

    /**
     * @param SimulatedPaypalApi $simulatedApi      API which replaces the one of Paypal when the payments are simulated
     * @param bool               $paymentSimulation The payments are simulated: Paypal is not called
     */
    public function __construct(
        HttpClientInterface $httpClient,
        RequestStack $requestStack,
        ?string $username,
        #[\SensitiveParameter] ?string $password,
        #[\SensitiveParameter] ?string $signature,
        bool $sandbox,
        SimulatedPaypalApi $simulatedApi,
        bool $paymentSimulation
    ) {
        $this->httpClient = $httpClient;
        $this->requestStack = $requestStack;
        $this->username = $username;
        $this->password = $password;
        $this->signature = $signature;
        $this->sandbox = $sandbox;
        $this->simulatedApi = $simulatedApi;
        $this->paymentSimulation = $paymentSimulation;
    }

    public function getName(): string
    {
        return 'paypal_express_checkout';
    }

    public function getLabel(): string
    {
        return 'form.label.paypal_express_checkout'.($this->paymentSimulation ? '_simulation' : '');
    }

    public function getGateway(): GatewayInterface
    {
        if (null === $this->gateway) {
            // The request of Symfony is used, not the PHP globals
            $getHttpRequest = new GetHttpRequestAction();
            $getHttpRequest->setHttpRequestStack($this->requestStack);

            $config = [
                'username' => (string) $this->username,
                'password' => (string) $this->password,
                'signature' => (string) $this->signature,
                'sandbox' => $this->sandbox,
                'payum.http_client' => $this->httpClient,
                // Given so that Payum does not look for a HTTPlug client, which is not used
                'httplug.client' => $this->httpClient,
                'httplug.message_factory' => new GuzzleMessageFactory(),
                'payum.action.get_http_request' => $getHttpRequest,
            ];

            // The gateway is the same when the payments are simulated, only the API it calls changes
            if ($this->paymentSimulation) {
                $config['payum.api'] = $this->simulatedApi;
            }

            $this->gateway = (new PaypalExpressCheckoutGatewayFactory())->create($config);
        }

        return $this->gateway;
    }

    public function createDetails(Payment $payment, string $returnUrl): array
    {
        return [
            'PAYMENTREQUEST_0_AMT' => (float) $payment->getAmount(),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $payment->getCurrency(),
            'RETURNURL' => $returnUrl,
            // The gateway adds "cancelled=1" to this URL, the payment is then canceled
            'CANCELURL' => $returnUrl,
        ];
    }

    public function getReference(array $details): ?string
    {
        return $details['PAYMENTINFO_0_TRANSACTIONID'] ?? null;
    }

    public function getReasonCode(array $details): ?string
    {
        if (isset($details['L_ERRORCODE0'])) {
            return (string) $details['L_ERRORCODE0'];
        }

        // The payment is not completed and not pending: the status given by Paypal is the reason
        $status = $details['PAYMENTINFO_0_PAYMENTSTATUS'] ?? $details['CHECKOUTSTATUS'] ?? null;

        return null !== $status ? (string) $status : null;
    }
}
