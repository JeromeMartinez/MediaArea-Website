<?php

namespace MediaConchOnlineBundle\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    protected $parameters;

    public function __construct(
        MailerInterface $mailer,
        UrlGeneratorInterface $router,
        Environment $twig,
        LoggerInterface $logger,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(User $user)
    {
        $this->sendTemplate($user, $this->parameters['confirmation.template'], 'fos_user_registration_confirm');
    }

    public function sendResettingEmailMessage(User $user)
    {
        $this->sendTemplate($user, $this->parameters['resetting.template'], 'fos_user_resetting_reset');
    }

    /**
     * Send an email with a link to a route which takes the confirmation token of the user.
     */
    protected function sendTemplate(User $user, string $template, string $route)
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
            $url = $this->router->generate(
                $route,
                ['token' => $user->getConfirmationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } finally {
            // Restore context
            $context->setHost($tmp['host']);
            $context->setScheme($tmp['scheme']);
            $context->setBaseUrl($tmp['baseUrl']);
        }

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
