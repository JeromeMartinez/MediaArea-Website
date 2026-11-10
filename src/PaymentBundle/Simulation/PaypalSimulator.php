<?php

namespace PaymentBundle\Simulation;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Stands for Paypal when the payments are simulated: it answers to the requests of the NVP API used by
 * Express Checkout, and keeps what the "buyer" chose on the page which replaces the one of Paypal.
 *
 * No money is involved, and nothing is sent to Paypal.
 */
class PaypalSimulator
{
    /**
     * The buyer pays.
     */
    const OUTCOME_PAY = 'pay';

    /**
     * The buyer gives up.
     */
    const OUTCOME_CANCEL = 'cancel';

    /**
     * The buyer accepts but Paypal refuses the payment.
     */
    const OUTCOME_DECLINE = 'decline';

    /**
     * The buyer accepts but Paypal holds the payment.
     */
    const OUTCOME_PENDING = 'pending';

    const OUTCOMES = [self::OUTCOME_PAY, self::OUTCOME_CANCEL, self::OUTCOME_DECLINE, self::OUTCOME_PENDING];

    const PAYER_ID = 'SIMULATEDPAYER';

    /**
     * Currencies accepted by Paypal.
     */
    const CURRENCIES = [
        'AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK',
        'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD',
    ];

    /**
     * Seconds during which a checkout is kept.
     */
    const LIFETIME = 86400;

    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Answer to a request of the NVP API.
     *
     * @param array $fields Fields of the request, with the name of the operation in METHOD
     *
     * @return array Fields of the response
     */
    public function request(array $fields): array
    {
        switch ($fields['METHOD'] ?? null) {
            case 'SetExpressCheckout':
                return $this->setExpressCheckout($fields);
            case 'GetExpressCheckoutDetails':
                return $this->getExpressCheckoutDetails($fields);
            case 'DoExpressCheckoutPayment':
                return $this->doExpressCheckoutPayment($fields);
            case 'GetTransactionDetails':
                return $this->getTransactionDetails($fields);
        }

        return $this->failure($fields, '81002', 'Unspecified Method', 'Method Specified is not Supported');
    }

    /**
     * Checkout started by SetExpressCheckout, for the page which replaces the one of Paypal.
     *
     * @return array|null Null when the token is not the one of a checkout
     */
    public function getCheckout(string $token): ?array
    {
        $item = $this->cache->getItem($this->getCacheKey($token));
        $checkout = $item->isHit() ? $item->get() : null;

        return is_array($checkout) ? $checkout : null;
    }

    /**
     * Record what the buyer does with a checkout.
     *
     * @return string URL the buyer is sent to, as Paypal does
     */
    public function choose(string $token, string $outcome): string
    {
        $checkout = $this->getCheckout($token);
        if (null === $checkout) {
            throw new \InvalidArgumentException('Unknown checkout.');
        }
        if (!in_array($outcome, self::OUTCOMES, true)) {
            throw new \InvalidArgumentException('Unknown outcome.');
        }
        if (null !== $checkout['transactionId']) {
            throw new \LogicException('The payment of this checkout is already made.');
        }

        if (self::OUTCOME_CANCEL === $outcome) {
            return $this->addQuery($checkout['cancelUrl'], ['token' => $token]);
        }

        $checkout['outcome'] = $outcome;
        $checkout['payerId'] = self::PAYER_ID;
        $this->save($checkout);

        return $this->addQuery($checkout['returnUrl'], ['token' => $token, 'PayerID' => self::PAYER_ID]);
    }

    private function setExpressCheckout(array $fields): array
    {
        $amount = $fields['PAYMENTREQUEST_0_AMT'] ?? null;
        if (!is_numeric($amount) || $amount <= 0) {
            return $this->failure($fields, '10525', 'Invalid Data', 'This transaction cannot be processed. The amount to be charged is zero.');
        }
        $currency = $fields['PAYMENTREQUEST_0_CURRENCYCODE'] ?? 'USD';
        if (!in_array($currency, self::CURRENCIES, true)) {
            return $this->failure($fields, '10605', 'Transaction refused because of an invalid argument.', 'Currency is not supported.');
        }
        if (empty($fields['RETURNURL'])) {
            return $this->failure($fields, '10471', 'Transaction refused because of an invalid argument.', 'ReturnURL is invalid.');
        }
        if (empty($fields['CANCELURL'])) {
            return $this->failure($fields, '10472', 'Transaction refused because of an invalid argument.', 'CancelURL is invalid.');
        }

        $checkout = [
            'token' => 'EC-SIMULATED'.strtoupper(bin2hex(random_bytes(8))),
            'amount' => number_format((float) $amount, 2, '.', ''),
            'currency' => $currency,
            'returnUrl' => $fields['RETURNURL'],
            'cancelUrl' => $fields['CANCELURL'],
            'outcome' => null,
            'payerId' => null,
            'transactionId' => null,
            'paymentStatus' => null,
            'orderTime' => null,
        ];
        $this->save($checkout);

        return $this->success($fields, ['TOKEN' => $checkout['token']]);
    }

    private function getExpressCheckoutDetails(array $fields): array
    {
        $checkout = $this->getCheckout((string) ($fields['TOKEN'] ?? ''));
        if (null === $checkout) {
            return $this->failure($fields, '10410', 'Invalid token', 'Invalid token.');
        }

        $details = [
            'TOKEN' => $checkout['token'],
            'CHECKOUTSTATUS' => null === $checkout['transactionId'] ? 'PaymentActionNotInitiated' : 'PaymentActionCompleted',
            'CURRENCYCODE' => $checkout['currency'],
            'AMT' => $checkout['amount'],
            'PAYMENTREQUEST_0_CURRENCYCODE' => $checkout['currency'],
            'PAYMENTREQUEST_0_AMT' => $checkout['amount'],
        ];
        // The buyer is known when he accepted the payment
        if (null !== $checkout['payerId']) {
            $details += [
                'PAYERID' => $checkout['payerId'],
                'EMAIL' => 'buyer@simulation.invalid',
                'PAYERSTATUS' => 'verified',
                'FIRSTNAME' => 'Simulated',
                'LASTNAME' => 'Buyer',
                'COUNTRYCODE' => 'FR',
            ];
        }
        if (null !== $checkout['transactionId']) {
            $details['PAYMENTREQUEST_0_TRANSACTIONID'] = $checkout['transactionId'];
        }

        return $this->success($fields, $details);
    }

    private function doExpressCheckoutPayment(array $fields): array
    {
        $checkout = $this->getCheckout((string) ($fields['TOKEN'] ?? ''));
        if (null === $checkout) {
            return $this->failure($fields, '10410', 'Invalid token', 'Invalid token.');
        }
        if (null === $checkout['payerId'] || ($fields['PAYERID'] ?? null) !== $checkout['payerId']) {
            return $this->failure($fields, '10406', 'Transaction refused because of an invalid argument.', 'The PayerID value is invalid.');
        }
        if (null !== $checkout['transactionId']) {
            return $this->failure($fields, '10415', 'Transaction refused because of an invalid argument.', 'A successful transaction has already been completed for this token.');
        }
        if ((float) ($fields['PAYMENTREQUEST_0_AMT'] ?? 0) != (float) $checkout['amount']) {
            return $this->failure($fields, '10413', 'Transaction refused because of an invalid argument.', 'The totals of the cart item amounts do not match order amounts.');
        }
        if (self::OUTCOME_DECLINE === $checkout['outcome']) {
            return $this->failure($fields, '10486', 'This transaction couldn\'t be completed.', 'This transaction couldn\'t be completed. Please redirect your customer to PayPal.');
        }

        $pending = self::OUTCOME_PENDING === $checkout['outcome'];
        $checkout['transactionId'] = 'SIMULATED-'.strtoupper(bin2hex(random_bytes(6)));
        $checkout['paymentStatus'] = $pending ? 'Pending' : 'Completed';
        $checkout['orderTime'] = gmdate('Y-m-d\TH:i:s\Z');
        $this->save($checkout);

        return $this->success($fields, [
            'TOKEN' => $checkout['token'],
            'SUCCESSPAGEREDIRECTREQUESTED' => 'false',
            'PAYMENTINFO_0_TRANSACTIONID' => $checkout['transactionId'],
            'PAYMENTINFO_0_TRANSACTIONTYPE' => 'expresscheckout',
            'PAYMENTINFO_0_PAYMENTTYPE' => 'instant',
            'PAYMENTINFO_0_ORDERTIME' => $checkout['orderTime'],
            'PAYMENTINFO_0_AMT' => $checkout['amount'],
            'PAYMENTINFO_0_FEEAMT' => '0.00',
            'PAYMENTINFO_0_TAXAMT' => '0.00',
            'PAYMENTINFO_0_CURRENCYCODE' => $checkout['currency'],
            'PAYMENTINFO_0_PAYMENTSTATUS' => $checkout['paymentStatus'],
            'PAYMENTINFO_0_PENDINGREASON' => $pending ? 'paymentreview' : 'None',
            'PAYMENTINFO_0_REASONCODE' => 'None',
            'PAYMENTINFO_0_ERRORCODE' => '0',
            'PAYMENTINFO_0_ACK' => 'Success',
        ]);
    }

    private function getTransactionDetails(array $fields): array
    {
        // The transaction gives the token of its checkout
        $item = $this->cache->getItem($this->getCacheKey((string) ($fields['TRANSACTIONID'] ?? '')));
        $token = $item->isHit() ? $item->get() : null;
        $checkout = is_string($token) ? $this->getCheckout($token) : null;
        if (null === $checkout) {
            return $this->failure($fields, '10004', 'Transaction refused because of an invalid argument.', 'The transaction id is not valid');
        }

        return $this->success($fields, [
            'TRANSACTIONID' => $checkout['transactionId'],
            'TRANSACTIONTYPE' => 'expresscheckout',
            'PAYMENTTYPE' => 'instant',
            'ORDERTIME' => $checkout['orderTime'],
            'AMT' => $checkout['amount'],
            'FEEAMT' => '0.00',
            'TAXAMT' => '0.00',
            'CURRENCYCODE' => $checkout['currency'],
            'PAYMENTSTATUS' => $checkout['paymentStatus'],
            'PENDINGREASON' => 'Pending' === $checkout['paymentStatus'] ? 'paymentreview' : 'None',
            'REASONCODE' => 'None',
            'PAYERID' => $checkout['payerId'],
        ]);
    }

    private function success(array $request, array $fields): array
    {
        return $fields + $this->getCommonFields($request, 'Success');
    }

    private function failure(array $request, string $code, string $shortMessage, string $longMessage): array
    {
        return $this->getCommonFields($request, 'Failure') + [
            'L_ERRORCODE0' => $code,
            'L_SHORTMESSAGE0' => $shortMessage,
            'L_LONGMESSAGE0' => $longMessage,
            'L_SEVERITYCODE0' => 'Error',
        ];
    }

    private function getCommonFields(array $request, string $ack): array
    {
        return [
            'TIMESTAMP' => gmdate('Y-m-d\TH:i:s\Z'),
            'CORRELATIONID' => bin2hex(random_bytes(6)),
            'ACK' => $ack,
            'VERSION' => (string) ($request['VERSION'] ?? ''),
            'BUILD' => 'simulation',
        ];
    }

    private function save(array $checkout): void
    {
        $item = $this->cache->getItem($this->getCacheKey($checkout['token']));
        $item->set($checkout);
        $item->expiresAfter(self::LIFETIME);
        $this->cache->save($item);

        // The transaction gives the token of its checkout
        if (null !== $checkout['transactionId']) {
            $item = $this->cache->getItem($this->getCacheKey($checkout['transactionId']));
            $item->set($checkout['token']);
            $item->expiresAfter(self::LIFETIME);
            $this->cache->save($item);
        }
    }

    private function getCacheKey(string $id): string
    {
        return 'payment.simulation.paypal.'.sha1($id);
    }

    private function addQuery(string $url, array $query): string
    {
        return $url.(false === strpos($url, '?') ? '?' : '&').http_build_query($query);
    }
}
