<?php

namespace PaymentBundle\Lib;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/**
 * Encrypts the data exchanged with the payment providers, which are stored with the payments.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DetailsCipher
{
    private $secret;
    private $key;

    /**
     * @param string $secret Key made by vendor/bin/generate-defuse-key
     */
    public function __construct(#[\SensitiveParameter] ?string $secret)
    {
        $this->secret = $secret;
    }

    public function encrypt(array $details): string
    {
        return Crypto::encrypt(json_encode($details), $this->getKey());
    }

    public function decrypt(string $data): array
    {
        if ('' === $data) {
            return [];
        }

        return json_decode(Crypto::decrypt($data, $this->getKey()), true) ?: [];
    }

    private function getKey(): Key
    {
        if (null === $this->key) {
            if (null === $this->secret || '' === $this->secret) {
                throw new \LogicException('The "payment_secret" parameter is not set, payments can not be stored.');
            }

            $this->key = Key::loadFromAsciiSafeString($this->secret);
        }

        return $this->key;
    }
}
