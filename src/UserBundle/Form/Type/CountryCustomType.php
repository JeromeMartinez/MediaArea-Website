<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\IntlCallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class CountryCustomType extends AbstractType
{
    private const PREFERRED_COUNTRIES = ['GB', 'FR', 'DE', 'IT', 'SE', 'NL', 'BE', 'CH', 'US', 'ES'];

    /**
     * Regions which are not countries of ISO 3166-1 but were in the list of the previous versions of Symfony:
     * users may have one of them in their profile.
     */
    private const OTHER_REGIONS = ['AC', 'DG', 'EA', 'IC', 'TA'];

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
            // Preferred countries are in the order of the list and are not repeated in it
            'preferred_choices' => static fn ($choice) => in_array($choice, self::PREFERRED_COUNTRIES, true),
            'duplicate_preferred_choices' => false,
            'placeholder' => 'Choose your country',
            'required' => false,
        ));
    }

    public function getParent(): ?string
    {
        return CountryType::class;
    }

    /**
     * Get the names of the countries of the list.
     *
     * @param string|null $displayLocale Locale of the names, the current one by default
     *
     * @return array Names indexed by country code and sorted
     */
    public static function getNames(?string $displayLocale = null): array
    {
        $displayLocale ??= \Locale::getDefault();

        $names = Countries::getNames($displayLocale);
        foreach (self::OTHER_REGIONS as $region) {
            $names[$region] = \Locale::getDisplayRegion('und_'.$region, $displayLocale);
        }
        (new \Collator($displayLocale))->asort($names);

        return $names;
    }
}
