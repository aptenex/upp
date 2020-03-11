<?php /** @noinspection NotOptimalIfConditionsInspection */

namespace Aptenex\Upp\Los\Generator;

use Aptenex\Upp\Los\LosOptions;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Los\Lookup\LookupDirectorInterface;


interface LosGeneratorInterface
{

    public function generateLosRecords(LosOptions $options, LookupDirectorInterface $ld, PricingConfig $pricingConfig);
    public function getUpp();

}