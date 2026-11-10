<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * reCAPTCHA v2 widget, rendered by the recaptcha_widget block of @App/Form/recaptcha_widget.html.twig.
 */
class RecaptchaType extends AbstractType
{
    const API_HOST = 'www.google.com';

    private $publicKey;
    private $language;

    public function __construct(string $publicKey, string $language)
    {
        $this->publicKey = $publicKey;
        $this->language = $language;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => false,
            'language' => $this->language,
            'attr' => [
                'options' => [
                    'theme' => 'light',
                    'type' => 'image',
                    'size' => 'normal',
                    'callback' => null,
                    'expiredCallback' => null,
                    'bind' => null,
                    'defer' => false,
                    'async' => false,
                    'badge' => null,
                ],
            ],
        ]);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars = array_replace($view->vars, [
            'recaptcha_api_host' => self::API_HOST,
            'public_key' => $this->publicKey,
            'url_challenge' => sprintf('https://%s/recaptcha/api.js?hl=%s', self::API_HOST, $options['language']),
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'recaptcha';
    }
}
