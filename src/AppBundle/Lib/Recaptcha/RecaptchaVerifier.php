<?php

namespace AppBundle\Lib\Recaptcha;

use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;

/**
 * Check the reCAPTCHA response of a request with Google.
 */
class RecaptchaVerifier
{
    private $privateKey;

    public function __construct(#[\SensitiveParameter] string $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * @param bool $checkHostname Also check that the captcha was solved on the host of the request
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function verify(Request $request, bool $checkHostname = false): bool
    {
        $recaptcha = new ReCaptcha($this->privateKey);

        if ($checkHostname) {
            $recaptcha = $recaptcha->withExpectedHostname($request->getHost());
        }

        $answer = $request->request->get(ReCaptcha::RESPONSE_KEY) ?? $request->query->get(ReCaptcha::RESPONSE_KEY);

        return $recaptcha->verify((string) $answer, $request->getClientIp())->isSuccess();
    }
}
