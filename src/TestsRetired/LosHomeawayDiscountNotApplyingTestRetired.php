<?php

namespace Aptenex\Upp\TestsRetired;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Los\Transformer\BookingComRecordTransformer;
use Aptenex\Upp\Los\Transformer\ElasticSearchTransformer;
use Aptenex\Upp\Los\Transformer\HomeAwayRecordTransformer;
use Aptenex\Upp\Util\TestUtils;
use Pool;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\DateUtils;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Translation\TestTranslator;
use Aptenex\Upp\Util\ArrayUtils;
use Aptenex\Upp\Los\LosOptions;
use Aptenex\Upp\Los\Generator\LosGenerator;
use Aptenex\Upp\Los\LosRecordMerger;
use Aptenex\Upp\Los\Auto\Autoloader;
use Aptenex\Upp\Los\Threaded\LosGeneratorTask;
use Aptenex\Upp\Los\Lookup\LookupDirectorFactory;
use Aptenex\Upp\Los\Transformer\TransformOptions;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Los\Transformer\AirbnbRecordTransformer;

class LosHomeawayDiscountNotApplyingTestRetired extends TestCase
{

    private $losDebugData = '
            {
                "losOptions": {
                    "startDate": "2020-07-09",
                    "endDate": "2020-08-02",
                    "currency": "USD",
                    "defaultMinStay": 0,
                    "defaultMaxStay": 30,
                    "forceFullGeneration": true,
                    "forceAllAvailabilitiesGeneration": true,
                    "forceDebugOnDate": null,
                    "debugMode": false,
                    "pricingContextMode": ["CALCULATION_MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES", "CALCULATION_MODE_LOS_EXCLUDE_DAMAGE_DEPOSIT"]
                },
                "listingSchema": {
                    "unitAvailability": {
                        "availabilityDefault": "N",
                        "changeoverDefault": "3",
                        "configuration": {
                            "availability": "NNYNNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY",
                            "changeover": "3333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333",
                            "maxStay": "30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30",
                            "minStay": "2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,4,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,5,5,5,5,5,5,5,5,5,5,5,5,5,5,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0",
                            "unitsAvailable": "0,0,1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1"
                        },
                        "dateRange": {
                            "endDate": "2023-07-09",
                            "startDate": "2020-07-09"
                        },
                        "instantBookableDefault": "Y",
                        "maxStayDefault": 30,
                        "minPriorNotifyDefault": 2,
                        "minStayDefault": 0
                    },
                    "listing": {
                        "sleeps": 10,
                        "maxOccupancy": 12
                    }
                },
                "pricingSchema": {
                    "name": "Pricing",
                    "version": "0.0.1",
                    "schema": "property-pricing",
                    "meta": [],
                    "flags": {
                        "HAS_PER_GUEST_PERIOD_STRATEGY": 300
                    },
                    "data": [{
                        "currency": "USD",
                        "defaults": {
                            "minimumNights": 0,
                            "maximumNights": "",
                            "perPetPerStay": 0,
                            "perPetPerNight": 0,
                            "perPetSplitMethod": null,
                            "damageDeposit": null,
                            "bookableType": "instant_bookable",
                            "damageDepositCalculationMethod": "fixed",
                            "damageDepositSplitMethod": null,
                            "daysRequiredInAdvanceForBooking": 2,
                            "balanceDaysBeforeArrival": 45,
                            "depositSplitPercentage": 30,
                            "periodSelectionStrategy": "DEFAULT",
                            "extraNightAlterationStrategyUseGlobalNights": false,
                            "partialWeekAlterationStrategyUseGlobalNights": false,
                            "modifiersUseCategorizedCalculationOrder": false
                        },
                        "taxes": [{
                            "name": "VAT",
                            "type": "TYPE_VAT",
                            "uuid": "ce55e959-dfec-4b00-b958-563259c07f41",
                            "description": null,
                            "amount": 0.07,
                            "calculationMethod": "percentage",
                            "includeBasePrice": true,
                            "includeExtras": true,
                            "extrasWhitelist": []
                        }],
                        "periods": [{
                            "id": 40,
                            "description": "Low Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-04-20",
                                "endDate": "2020-09-24",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 320,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 320,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 200,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 260,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 320,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 375,
                                                "guests": "11"
                                            }, {
                                                "amount": 430,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "2",
                            "bookableType": null
                        }, {
                            "id": 41,
                            "description": "High Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-09-25",
                                "endDate": "2020-10-05",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 420,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "minimumNights": "4",
                            "bookableType": null
                        }, {
                            "id": 42,
                            "description": "Low Season ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-10-06",
                                "endDate": "2020-12-19",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 320,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 320,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 200,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 260,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 320,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 375,
                                                "guests": "11"
                                            }, {
                                                "amount": 430,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "2",
                            "bookableType": null
                        }, {
                            "id": 43,
                            "description": "Peak Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-12-20",
                                "endDate": "2021-01-06",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 630,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "minimumNights": "7",
                            "bookableType": null
                        }, {
                            "id": 44,
                            "description": "Mid Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-01-07",
                                "endDate": "2021-02-06",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 420,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 420,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 300,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 360,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 420,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 475,
                                                "guests": "11"
                                            }, {
                                                "amount": 530,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": null
                        }, {
                            "id": 45,
                            "description": "High Season  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-02-07",
                                "endDate": "2021-02-20",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 460,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "minimumNights": "5",
                            "bookableType": null
                        }, {
                            "id": 46,
                            "description": "Mid Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-02-21",
                                "endDate": "2021-03-26",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 420,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 420,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 300,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 360,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 420,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 475,
                                                "guests": "11"
                                            }, {
                                                "amount": 530,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": null
                        }, {
                            "id": 47,
                            "description": "High Season  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-03-27",
                                "endDate": "2021-04-18",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 460,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "minimumNights": "5",
                            "bookableType": null
                        }, {
                            "id": 48,
                            "description": "Low Season ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-04-19",
                                "endDate": "2021-07-02",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 320,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 320,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 200,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 260,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 320,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 375,
                                                "guests": "11"
                                            }, {
                                                "amount": 430,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "2",
                            "bookableType": null
                        }, {
                            "id": 49,
                            "description": "Mid Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-07-03",
                                "endDate": "2021-08-28",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 420,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 420,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 300,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 360,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 420,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 475,
                                                "guests": "11"
                                            }, {
                                                "amount": 530,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": null
                        }, {
                            "id": 50,
                            "description": "Low Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-08-29",
                                "endDate": "2021-09-30",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 320,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 320,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 200,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 260,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 320,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 375,
                                                "guests": "11"
                                            }, {
                                                "amount": 430,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "2",
                            "bookableType": null
                        }, {
                            "id": 51,
                            "description": "High Season ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-10-01",
                                "endDate": "2021-10-09",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 460,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "minimumNights": "4",
                            "bookableType": null
                        }, {
                            "id": 52,
                            "description": "Low Season ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-10-10",
                                "endDate": "2021-12-21",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 320,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [{
                                            "amount": 320,
                                            "damageDepositOverride": null,
                                            "days": [],
                                            "guests": [{
                                                "amount": 200,
                                                "guests": "1-6"
                                            }, {
                                                "amount": 260,
                                                "guests": "7-8"
                                            }, {
                                                "amount": 320,
                                                "guests": "9-10"
                                            }, {
                                                "amount": 375,
                                                "guests": "11"
                                            }, {
                                                "amount": 430,
                                                "guests": "12"
                                            }],
                                            "matchAmount": 0,
                                            "night": "1+"
                                        }]
                                    }
                                }
                            },
                            "minimumNights": "2",
                            "bookableType": null
                        }, {
                            "id": 53,
                            "description": "Peak Season",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-12-22",
                                "endDate": "2022-01-07",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "nightly",
                                "amount": 630,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "minimumNights": "7",
                            "bookableType": null
                        }],
                        "modifiers": [{
                            "id": null,
                            "description": "Service Charge",
                            "priority": null,
                            "conditionOperand": "AND",
                            "conditions": [],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.1,
                                "taxable": true,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": ["ce55e959-dfec-4b00-b958-563259c07f41"],
                                "calculationMethod": "percentage",
                                "calculationOperand": "addition",
                                "calculationOperator": "addition",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "service_charge",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "total"
                        }, {
                            "id": null,
                            "description": "Summer 2020 50% off",
                            "priority": null,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-06-15",
                                "endDate": "2020-08-31",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.5,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "percentage",
                                "calculationOperand": "subtraction",
                                "calculationOperator": "subtraction",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "discount",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "base"
                        }, {
                            "id": null,
                            "description": "Long stays 12 days -10%",
                            "priority": null,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-09-01",
                                "endDate": "2019-12-20",
                                "arrivalDays": [],
                                "departureDays": []
                            }, {
                                "type": "nights",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "minimum": 12,
                                "maximum": null
                            }, {
                                "type": "booking_days",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "minimum": 21,
                                "maximum": 119
                            }, {
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2021-01-09",
                                "endDate": "2021-12-20",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.1,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "percentage",
                                "calculationOperand": "subtraction",
                                "calculationOperator": "subtraction",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "discount",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "base"
                        }, {
                            "id": null,
                            "description": "Last minute",
                            "priority": null,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "booking_days",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "minimum": null,
                                "maximum": 20
                            }, {
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-09-01",
                                "endDate": "2021-01-08",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.15,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "percentage",
                                "calculationOperand": "subtraction",
                                "calculationOperator": "subtraction",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "discount",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "base"
                        }, {
                            "id": null,
                            "description": "10% Early Bird",
                            "priority": null,
                            "conditionOperand": "AND",
                            "conditions": [{
                                "type": "booking_days",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "minimum": 120,
                                "maximum": null
                            }, {
                                "type": "date",
                                "inverse": false,
                                "modifyRatePerUnit": false,
                                "startDate": "2020-09-01",
                                "endDate": "2021-01-08",
                                "arrivalDays": [],
                                "departureDays": []
                            }],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.1,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "percentage",
                                "calculationOperand": "subtraction",
                                "calculationOperator": "subtraction",
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "discount",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "base"
                        }]
                    }]
                },
                "currency": "USD"
            }
        ';

    public const PARSED_CONFIG_EATS_AND_RETREATS_TEST = 'eatsandretreats-los-large-calculation-test';

    public function testHomeawayDiscountNotApplying()
    {
        $testData = json_decode($this->losDebugData, true);

        $upp = new Upp(
            new HashMapPricingResolver(ArrayAccess::get('mixins', [], [])),
            new TestTranslator()
        );

        $losGenerator = new LosGenerator($upp);

        $losData = $testData['losOptions'];

        $losOptions = new LosOptions(
            $losData['currency'],
            new \DateTime($losData['startDate']),
            new \DateTime($losData['endDate'])
        );

        $losOptions->setBookingDate(new \DateTime('2020-07-06'));
        $losOptions->setDefaultMinStay($losData['defaultMinStay']);
        $losOptions->setDefaultMaxStay($losData['defaultMaxStay']);
        $losOptions->setForceFullGeneration($losData['forceFullGeneration']);
        $losOptions->setForceAllAvailabilitiesGeneration($losData['forceAllAvailabilitiesGeneration']);
        $losOptions->setDebugMode($losData['debugMode']);
        $losOptions->setPricingContextCalculationMode($losData['pricingContextMode']);

        $ld = LookupDirectorFactory::newFromRentalData($testData['listingSchema'], $losOptions);
        $parsed = $upp->parsePricingConfig($testData['pricingSchema'], new StructureOptions());

        $losRecords = $losGenerator->generateLosRecords($losOptions, $ld, $parsed);

        $transformer = new HomeAwayRecordTransformer();
        $output = json_encode($transformer->transform($losRecords, new TransformOptions()), JSON_PRETTY_PRINT);

        $this->assertStringEqualsFile(__DIR__ . '/Resources/los_homeaway_discount_not_applying.generated.txt', $output);
    }


}