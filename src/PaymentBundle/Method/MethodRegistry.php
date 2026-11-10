<?php

namespace PaymentBundle\Method;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * The payment methods of the website, by name.
 */
class MethodRegistry
{
    private $methods = [];

    /**
     * @param iterable<PaymentMethodInterface> $methods
     */
    public function __construct(#[AutowireIterator('payment.method')] iterable $methods)
    {
        foreach ($methods as $method) {
            $this->methods[$method->getName()] = $method;
        }
    }

    /**
     * @return PaymentMethodInterface[] indexed by name
     */
    public function all(): array
    {
        return $this->methods;
    }

    public function has(string $name): bool
    {
        return isset($this->methods[$name]);
    }

    public function get(string $name): PaymentMethodInterface
    {
        if (!isset($this->methods[$name])) {
            throw new \InvalidArgumentException(sprintf('The payment method "%s" does not exist.', $name));
        }

        return $this->methods[$name];
    }
}
