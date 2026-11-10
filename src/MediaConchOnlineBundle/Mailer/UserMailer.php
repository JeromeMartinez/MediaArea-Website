<?php

namespace MediaConchOnlineBundle\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Twig\Environment;
use UserBundle\Entity\User;

/**
 * Send the emails of the user accounts (address confirmation, password reset).
 */
class UserMailer
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $logger;
    protected $verifyEmailHelper;
    protected $parameters;

    public function __construct(
        MailerInterface $mailer,
        UrlGeneratorInterface $router,
        Environment $twig,
        LoggerInterface $logger,
        VerifyEmailHelperInterface $verifyEmailHelper,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->parameters = $parameters;
    }

    /**
     * Send the link which confirms the email address of a user.
     *
     * The link is signed by SymfonyCastsVerifyEmailBundle for the identifier and the address of the user,
     * who has to be saved before.
     */
    public function sendConfirmationEmailMessage(User $user)
    {
        $url = $this->generateUrl(function () use ($user) {
            return $this->verifyEmailHelper->generateSignature(
                'fos_user_registration_confirm',
                (string) $user->getId(),
                (string) $user->getEmail(),
                ['id' => $user->getId()]
            )->getSignedUrl();
        });

        $this->sendTemplate($user, $this->parameters['confirmation.template'], $url);
    }

    /**
     * Send the link which resets the password of a user.
     *
     * @param string $token Token of the reset request, made by SymfonyCastsResetPasswordBundle
     */
    public function sendResettingEmailMessage(User $user, #[\SensitiveParameter] string $token)
    {
        $url = $this->generateUrl(function () use ($token) {
            return $this->router->generate(
                'fos_user_resetting_reset',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        });

        $this->sendTemplate($user, $this->parameters['resetting.template'], $url);
    }

    /**
     * Generate a URL with the host, the scheme and the base URL configured for the emails.
     *
     * @param callable $generate Generates the URL with the router
     */
    protected function generateUrl(callable $generate): string
    {
        // Store context and update it with config options
        $context = $this->router->getContext();
        $tmp = [
            'host' => $context->getHost(),
            'scheme' => $context->getScheme(),
            'baseUrl' => $context->getBaseUrl(),
        ];

        if (null !== $this->parameters['absolute_url_for_mail']['host']) {
            $context->setHost($this->parameters['absolute_url_for_mail']['host']);
        }
        if (null !== $this->parameters['absolute_url_for_mail']['scheme']) {
            $context->setScheme($this->parameters['absolute_url_for_mail']['scheme']);
        }
        if (null !== $this->parameters['absolute_url_for_mail']['baseUrl']) {
            $context->setBaseUrl($this->parameters['absolute_url_for_mail']['baseUrl']);
        }

        try {
            return $generate();
        } finally {
            // Restore context
            $context->setHost($tmp['host']);
            $context->setScheme($tmp['scheme']);
            $context->setBaseUrl($tmp['baseUrl']);
        }
    }

    /**
     * Send an email with a link.
     */
    protected function sendTemplate(User $user, string $template, string $url)
    {
        $rendered = $this->twig->render($template, [
            'user' => $user,
            'confirmationUrl' => $url,
        ]);

        $this->sendEmailMessage($rendered, (string) $user->getEmail());
    }

    protected function sendEmailMessage(string $renderedTemplate, string $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = array_shift($renderedLines);
        $body = implode("\n", $renderedLines);

        $message = (new Email())
            ->subject($subject)
            ->from(new Address($this->parameters['from_email']['address'], $this->parameters['from_email']['sender_name']))
            ->to($toEmail)
            ->text($body);

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            // As before, a delivery issue does not break the page
            $this->logger->error('Unable to send the email "{subject}": {error}', [
                'subject' => $subject,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
        }
    }
}
