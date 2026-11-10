<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Lib\Recaptcha\RecaptchaVerifier;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RecaptchaValidator extends ConstraintValidator
{
    private $verifier;
    private $requestStack;

    public function __construct(RecaptchaVerifier $verifier, RequestStack $requestStack)
    {
        $this->verifier = $verifier;
        $this->requestStack = $requestStack;
    }

    /**
     * The value of the field is not used: the response of the widget is read from the request.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Recaptcha) {
            throw new UnexpectedTypeException($constraint, Recaptcha::class);
        }

        $request = $this->requestStack->getMainRequest();

        if (null === $request || !$this->verifier->verify($request)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
