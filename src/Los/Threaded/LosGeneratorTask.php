<?php

namespace Aptenex\Upp\Los\Threaded;

use Aptenex\Upp\Exception\CannotGenerateLosException;
use Aptenex\Upp\Los\LosRecords;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Los\Lookup\LookupDirectorInterface;
use Aptenex\Upp\Los\LosGenerator;
use Aptenex\Upp\Los\LosOptions;

/**
 * Class LosGeneratorThreaded
 *
 * See https://github.com/krakjoe/pthreads-autoloading-composer/blob/master/auto.php
 *
 * @package Aptenex\Upp\Los\Threaded
 */
class LosGeneratorTask extends \Threaded
{

    /**
     * @var LosGenerator
     */
    private $generator;

    /**
     * @var LosOptions
     */
    private $options;

    /**
     * @var LookupDirectorInterface
     */
    private $ld;

    /**
     * @var PricingConfig
     */
    private $config;

    /**
     * @var LosRecords
     */
    private $recordResults;

    /**
     * @param LosGenerator $generator
     * @param LosOptions $options
     * @param LookupDirectorInterface $ld
     * @param PricingConfig $config
     * @param array $results
     */
    public function __construct(
        LosGenerator $generator,
        LosOptions $options,
        LookupDirectorInterface $ld,
        PricingConfig $config
    ) {
        $this->generator = $generator;
        $this->options = $options;
        $this->ld = $ld;
        $this->config = $config;
    }

    public function run() {
        try {
            $results = $this->generator->generateLosRecords($this->options, $this->ld, $this->config);
        } catch (CannotGenerateLosException $e) {
            $results = $e;
        }

        $this->recordResults = $results;
    }

    /**
     * @return LosRecords|null
     */
    public function getRecords(): ?LosRecords
    {
        return $this->recordResults;
    }

}