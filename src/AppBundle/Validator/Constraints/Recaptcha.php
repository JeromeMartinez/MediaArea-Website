<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * The reCAPTCHA response sent with the current request must be valid.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Recaptcha extends Constraint
{
    public $message = 'This value is not a valid captcha.';

    public function __construct(?string $message = null, ?array $groups = null, $payload = null)
    {
        parent::__construct(null, $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
