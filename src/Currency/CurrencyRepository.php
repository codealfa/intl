<?php

namespace CommerceGuys\Intl\Currency;

use CommerceGuys\Intl\Locale;
use CommerceGuys\Intl\RepositoryLocaleTrait;
use CommerceGuys\Intl\Exception\UnknownCurrencyException;

/**
 * Manages currencies based on JSON definitions.
 */
class CurrencyRepository implements CurrencyRepositoryInterface
{
    use RepositoryLocaleTrait;

    /**
     * The path where per-locale definitions are stored.
     *
     * @var string
     */
    protected $definitionPath;

    /**
     * Base currency definitions.
     *
     * Contains data common to all locales, such as the currency numeric
     * code, number of fraction digits.
     *
     * @var array
     */
    protected $baseDefinitions = [];

    /**
     * Per-locale currency definitions.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * The available locales.
     *
     * @var array
     */
    protected $availableLocales = [
        'af', 'agq', 'ak', 'am', 'ar', 'asa', 'ast', 'az', 'bas', 'be', 'bez',
        'bg', 'bm', 'bn', 'br', 'brx', 'bs', 'bs-Cyrl', 'ca', 'ccp', 'ce',
        'cgg', 'chr', 'cs', 'cy', 'da', 'dav', 'de', 'de-CH', 'dje', 'dsb',
        'dz', 'ebu', 'ee', 'el', 'en', 'en-AG', 'en-AI', 'en-AT', 'en-AU',
        'en-BB', 'en-BE', 'en-BM', 'en-BS', 'en-BW', 'en-BZ', 'en-CA', 'en-CC',
        'en-CH', 'en-CK', 'en-CM', 'en-CX', 'en-CY', 'en-DE', 'en-DG', 'en-DK',
        'en-DM', 'en-ER', 'en-FI', 'en-FJ', 'en-FK', 'en-FM', 'en-GB', 'en-GD',
        'en-GG', 'en-GH', 'en-GI', 'en-GM', 'en-GY', 'en-HK', 'en-IE', 'en-IL',
        'en-IM', 'en-IN', 'en-IO', 'en-JE', 'en-JM', 'en-KE', 'en-KI', 'en-KN',
        'en-KY', 'en-LC', 'en-LR', 'en-LS', 'en-MG', 'en-MO', 'en-MS', 'en-MT',
        'en-MU', 'en-MW', 'en-MY', 'en-NA', 'en-NF', 'en-NG', 'en-NL', 'en-NR',
        'en-NU', 'en-NZ', 'en-PG', 'en-PH', 'en-PK', 'en-PN', 'en-PW', 'en-RW',
        'en-SB', 'en-SC', 'en-SD', 'en-SE', 'en-SG', 'en-SH', 'en-SI', 'en-SL',
        'en-SS', 'en-SX', 'en-SZ', 'en-TC', 'en-TK', 'en-TO', 'en-TT', 'en-TV',
        'en-TZ', 'en-UG', 'en-VC', 'en-VG', 'en-VU', 'en-WS', 'en-ZA', 'en-ZM',
        'en-ZW', 'es', 'es-AR', 'es-BO', 'es-BR', 'es-BZ', 'es-CL', 'es-CO',
        'es-CR', 'es-CU', 'es-DO', 'es-EC', 'es-GT', 'es-HN', 'es-MX', 'es-NI',
        'es-PA', 'es-PE', 'es-PR', 'es-PY', 'es-SV', 'es-US', 'es-UY', 'es-VE',
        'et', 'eu', 'ewo', 'fa', 'fa-AF', 'ff', 'fi', 'fil', 'fo', 'fr',
        'fr-CA', 'fur', 'fy', 'ga', 'gd', 'gl', 'gsw', 'gu', 'guz', 'ha', 'he',
        'hi', 'hr', 'hsb', 'hu', 'hy', 'id', 'is', 'it', 'ja', 'jmc', 'ka',
        'kab', 'kam', 'kde', 'kea', 'khq', 'ki', 'kk', 'kln', 'km', 'kn', 'ko',
        'ks', 'ksb', 'ksf', 'ksh', 'ky', 'lag', 'lb', 'lg', 'ln', 'lo', 'lt',
        'lu', 'luo', 'luy', 'lv', 'mas', 'mer', 'mfe', 'mg', 'mk', 'ml', 'mn',
        'mr', 'ms', 'mua', 'my', 'mzn', 'naq', 'nb', 'nd', 'ne', 'nl', 'nmg',
        'nn', 'nyn', 'or', 'pa', 'pl', 'pt', 'pt-AO', 'pt-CH', 'pt-CV',
        'pt-GQ', 'pt-GW', 'pt-LU', 'pt-MO', 'pt-MZ', 'pt-PT', 'pt-ST', 'pt-TL',
        'rm', 'rn', 'ro', 'rof', 'ru', 'rwk', 'saq', 'sbp', 'sd', 'seh', 'ses',
        'sg', 'shi', 'shi-Latn', 'si', 'sk', 'sl', 'sn', 'sq', 'sr', 'sr-Latn',
        'sv', 'sw', 'sw-CD', 'ta', 'te', 'teo', 'th', 'tk', 'tr', 'twq', 'tzm',
        'ug', 'uk', 'ur', 'ur-IN', 'uz', 'uz-Cyrl', 'vai', 'vai-Latn', 'vi',
        'vun', 'xog', 'yo', 'yo-BJ', 'zgh', 'zh', 'zh-Hans-HK', 'zh-Hant',
        'zh-Hant-HK', 'zh-Hant-MO', 'zu',
    ];

    /**
     * Creates a CurrencyRepository instance.
     *
     * @param string $definitionPath The path to the currency definitions.
     *                               Defaults to 'resources/currency'.
     */
    public function __construct($definitionPath = null)
    {
        $this->definitionPath = $definitionPath ? $definitionPath : __DIR__ . '/../../resources/currency/';
    }

    /**
     * {@inheritdoc}
     */
    public function get($currencyCode, $locale = null, $fallbackLocale = null)
    {
        $locale = $locale ?: $this->getDefaultLocale();
        $fallbackLocale = $fallbackLocale ?: $this->getFallbackLocale();
        $locale = Locale::resolve($this->availableLocales, $locale, $fallbackLocale);
        $definitions = $this->loadDefinitions($locale);
        if (!isset($definitions[$currencyCode])) {
            throw new UnknownCurrencyException($currencyCode);
        }

        return $this->createCurrencyFromDefinition($currencyCode, $definitions[$currencyCode], $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($locale = null, $fallbackLocale = null)
    {
        $locale = $locale ?: $this->getDefaultLocale();
        $fallbackLocale = $fallbackLocale ?: $this->getFallbackLocale();
        $locale = Locale::resolve($this->availableLocales, $locale, $fallbackLocale);
        $definitions = $this->loadDefinitions($locale);
        $currencies = [];
        foreach ($definitions as $currencyCode => $definition) {
            $currencies[$currencyCode] = $this->createCurrencyFromDefinition($currencyCode, $definition, $locale);
        }

        return $currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($locale = null, $fallbackLocale = null)
    {
        $locale = $locale ?: $this->getDefaultLocale();
        $fallbackLocale = $fallbackLocale ?: $this->getFallbackLocale();
        $locale = Locale::resolve($this->availableLocales, $locale, $fallbackLocale);
        $definitions = $this->loadDefinitions($locale);
        $list = [];
        foreach ($definitions as $currencyCode => $definition) {
            $list[$currencyCode] = $definition['name'];
        }

        return $list;
    }

    /**
     * Loads the currency definitions for the provided locale.
     *
     * @param string $locale The desired locale.
     *
     * @return array
     */
    protected function loadDefinitions($locale)
    {
        if (!isset($this->definitions[$locale])) {
            $filename = $this->definitionPath . $locale . '.json';
            $this->definitions[$locale] = json_decode(file_get_contents($filename), true);

            // Make sure the base definitions have been loaded.
            if (empty($this->baseDefinitions)) {
                $this->baseDefinitions = json_decode(file_get_contents($this->definitionPath . 'base.json'), true);
            }
            // Merge-in base definitions.
            foreach ($this->definitions[$locale] as $currencyCode => $definition) {
                $this->definitions[$locale][$currencyCode] += $this->baseDefinitions[$currencyCode];
            }
        }

        return $this->definitions[$locale];
    }

    /**
     * Creates a currency object from the provided definition.
     *
     * @param string $currencyCode The currency code.
     * @param array  $definition   The currency definition.
     * @param string $locale       The locale of the currency definition.
     *
     * @return Currency
     */
    protected function createCurrencyFromDefinition($currencyCode, array $definition, $locale)
    {
        if (!isset($definition['symbol'])) {
            $definition['symbol'] = $currencyCode;
        }
        if (!isset($definition['fraction_digits'])) {
            $definition['fraction_digits'] = 2;
        }

        $currency = new Currency();
        $setValues = \Closure::bind(function ($currencyCode, $definition, $locale) {
            $this->currencyCode = $currencyCode;
            $this->name = $definition['name'];
            $this->numericCode = $definition['numeric_code'];
            $this->symbol = $definition['symbol'];
            $this->fractionDigits = $definition['fraction_digits'];
            $this->locale = $locale;
        }, $currency, '\CommerceGuys\Intl\Currency\Currency');
        $setValues($currencyCode, $definition, $locale);

        return $currency;
    }
}
