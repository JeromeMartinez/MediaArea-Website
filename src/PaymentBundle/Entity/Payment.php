<?php

namespace PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payment of an order with a payment provider.
 */
#[ORM\Table(name: 'order_payment')]
#[ORM\Entity]
class Payment
{
    /**
     * The payment is created, the payment provider was not called.
     */
    const STATE_NEW = 'new';

    /**
     * The user is at the payment provider, or the payment provider did not confirm the payment yet.
     */
    const STATE_PENDING = 'pending';

    const STATE_CAPTURED = 'captured';
    const STATE_CANCELED = 'canceled';
    const STATE_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\OneToOne(targetEntity: Order::class, inversedBy: 'payment')]
    private $order;

    /**
     * Name of the payment method, e.g. paypal_express_checkout.
     */
    #[ORM\Column(type: 'string', length: 100)]
    private $method;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 5)]
    private $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private $currency;

    #[ORM\Column(type: 'string', length: 20)]
    private $state = self::STATE_NEW;

    /**
     * Identifier of the transaction at the payment provider.
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $reference;

    /**
     * Code of the error given by the payment provider.
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $reasonCode;

    /**
     * Data exchanged with the payment provider, encrypted.
     */
    #[ORM\Column(type: 'text')]
    private $details = '';

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updatedAt;

    public function __construct(Order $order, string $method, $amount, string $currency)
    {
        $this->order = $order;
        $this->method = $method;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->createdAt = new \DateTime();
        $order->setPayment($this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function isCaptured(): bool
    {
        return self::STATE_CAPTURED === $this->state;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getReasonCode(): ?string
    {
        return $this->reasonCode;
    }

    public function setReasonCode(?string $reasonCode): self
    {
        $this->reasonCode = $reasonCode;

        return $this;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
}
