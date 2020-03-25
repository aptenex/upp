<?php

namespace Aptenex\Upp;

use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Validation\Validator;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\PricingConfigParser;
use Aptenex\Upp\Calculation\PricingGenerator;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Transformer\TransformerInterface;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\ResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Translation\TestTranslator;

class Upp
{

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @param ResolverInterface $pricingResolver
     * @param TranslatorInterface $translator
     */
    public function __construct(ResolverInterface $pricingResolver, TranslatorInterface $translator)
    {
        $this->resolver = $pricingResolver;

        LanguageTools::$translator = $translator;
        LanguageTools::$translator->setLocale('en'); // Default
    }

    /**
     * @param PricingContext $context
     * @param array $config
     */
    public function assignDefaultCurrencyIfNone(PricingContext $context, array $config)
    {
        if (!empty($context->getCurrency())) {
            return;
        }

        $currencies = isset($config['data']) ? $config['data'] : [];

        if (count($currencies) !== 1) {
            return;
        }

        $currency = $currencies[0];
        $currencyCode = isset($currency['currency']) ? $currency['currency'] : '';

        // If there is only one config, we can use it as the default

        if (empty($currencyCode)) {
            return;
        }

        $context->setCurrency($currencyCode);

        // Update Meta
        $meta = $context->getMeta();
        $meta['assignedDefaultCurrency'] = true;
        $context->setMeta($meta);
    }
    
    /**
     * @param PricingContext $context
     * @param PricingConfig $config
     *
     * @return Calculation\FinalPrice
     */
    public function generatePrice(PricingContext $context, PricingConfig $config)
    {
        $pricingGenerator = new PricingGenerator();

        // We need to see if the translation exists otherwise default to 'en'

        // The only reason this CAN be null is due to being run in a threaded environment (pthreads)
        if (LanguageTools::$translator === null) {
            LanguageTools::$translator = new TestTranslator(); // Re-instantiate
        }

        if (LanguageTools::$translator->trans('TRANSLATION_TEST', [], 'upp', $context->getLocale()) === 'TRANSLATION_TEST') {
            // Translation file does not exist
            LanguageTools::$translator->setLocale('en');
        } else {
            LanguageTools::$translator->setLocale($context->getLocale());
        }

        return $pricingGenerator->generate($context, $config);
    }

    /**
     * @param array $config
     * @param StructureOptions|null $options
     *
     * @return PricingConfig|null
     * @throws InvalidPricingConfigException
     */
    public function parsePricingConfig(array $config, StructureOptions $options = null)
    {
        $parser = new PricingConfigParser($this->resolver, $options);

        $parsedConfig = $parser->parsePricingConfig($config);

        if ($options instanceof StructureOptions) {
            if ($options->hasExternalCommandDirector() && $parsedConfig instanceof PricingConfig) {
                $options->getExternalCommandDirector()->apply($parsedConfig);
            }
        }

        return $parsedConfig;
    }

    /**
     * @param PricingConfig $pricingConfig
     *
     * @return Validation\ValidationResult
     */
    public function validatePricingConfig(PricingConfig $pricingConfig)
    {
        $validator = new Validator();

        return $validator->validatePricingConfig($pricingConfig);
    }

    /**
     * @param PricingContext $pricingContext
     *
     * @return Validation\ValidationResult
     */
    public function validatePricingContext(PricingContext $pricingContext)
    {
        $validator = new Validator();

        return $validator->validatePricingContext($pricingContext);
    }

    /**
     * @param PricingConfig|array   $config
     * @param TransformerInterface  $transformer
     * @param StructureOptions|null $options
     *
     * @return array|null
     * @throws InvalidPricingConfigException
     */
    public function transformPricingConfig($config, TransformerInterface $transformer, StructureOptions $options = null)
    {
        if (is_array($config)) {
            $config = $this->parsePricingConfig($config, $options);
        }

        if (!$config instanceof PricingConfig) {
            throw new InvalidPricingConfigException("Could not parse the pricing config");
        }

        return $transformer->transform($config);
    }

}