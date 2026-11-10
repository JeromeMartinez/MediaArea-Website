<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\IntlCallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class LanguageCustomType extends AbstractType
{
    private const PREFERRED_LANGUAGES = ['en_GB', 'fr', 'de', 'it', 'sv', 'nl', 'en_US', 'es'];

    /**
     * Regional variants and special codes which are not languages of ISO 639 but were in the list of the previous
     * versions of Symfony: users may have one of them in their profile.
     */
    private const OTHER_LANGUAGES = [
        'ar_001', 'de_AT', 'de_CH', 'en_AU', 'en_CA', 'en_GB', 'en_US', 'es_419', 'es_ES', 'es_MX', 'fa_AF', 'fr_CA',
        'fr_CH', 'mul', 'nds_NL', 'nl_BE', 'pt_BR', 'pt_PT', 'ro_MD', 'sr_ME', 'sw_CD', 'und', 'zh_Hans', 'zh_Hant',
        'zxx',
    ];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'choice_loader' => function (Options $options) {
                $locale = $options['choice_translation_locale'];

                return ChoiceList::loader(
                    $this,
                    new IntlCallbackChoiceLoader(static fn () => array_flip(self::getNames($locale))),
                    $locale
                );
            },
            // Preferred languages are in the order of the list and are not repeated in it
            'preferred_choices' => static fn ($choice) => in_array($choice, self::PREFERRED_LANGUAGES, true),
            'duplicate_preferred_choices' => false,
            'placeholder' => 'Choose your language',
            'required' => false,
        ));
    }

    public function getParent(): ?string
    {
        return LanguageType::class;
    }

    /**
     * Get the names of the languages of the list.
     *
     * @param string|null $displayLocale Locale of the names, the current one by default
     *
     * @return array Names indexed by language code and sorted
     */
    public static function getNames(?string $displayLocale = null): array
    {
        $displayLocale ??= \Locale::getDefault();

        $names = Languages::getNames($displayLocale);
        foreach (self::OTHER_LANGUAGES as $language) {
            $names[$language] = \Locale::getDisplayName($language, $displayLocale);
        }
        (new \Collator($displayLocale))->asort($names);

        return $names;
    }
}
