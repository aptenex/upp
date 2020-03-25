<?php

namespace Tests;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Los\Transformer\ElasticSearchTransformer;
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

class LosRecordTest extends TestCase
{

    public function testLosRecords()
    {
        $rentalSchemaData = '
            {
                "supportedLocales": [
                    "en",
                    "ru"
                ],              
                "flags": {
                    "isActive": false
                },
                "name": "Villa Louise Builder",
                "listing": {
                    "type": "LISTING_TYPE_BUILDING",
                    "beds": 0,
                    "sleeps": 3,
                    "maxOccupancy": 16,
                    "bedrooms": 1,
                    "bathrooms": 0
                },
                "unitAvailability": {
                    "dateRange": {
                        "startDate": "2019-02-28",
                        "endDate": "2022-02-28"
                    },
                    "changeoverDefault": 3,
                    "minPriorNotifyDefault": 0,
                    "minStayDefault": 0,
                    "instantBookableDefault": "Y",
                    "configuration": {
                        "changeover": "23323222332320233232220323222332322233232223323022332322200232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333",
                        "availability": "NNNNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN",
                        "minStay": "0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0",
                        "maxStay": "30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30"
                    }
                }
            }
        ';

        $pricingConfig = '
            {
                "name": "Pricing",
                "schema": "property-pricing",
                "version": "0.0.1",
                "meta": [],
                "data": [
                    {
                        "currency": "GBP",
                        "defaults": {
                            "damageDeposit": 0,
                            "damageDepositCalculationMethod": "fixed",
                            "perPetPerStay": 0,
                            "perPetPerNight": 0,
                            "perPetSplitMethod": "ON_TOTAL",
                            "minimumNights": 0,
                            "balanceDaysBeforeArrival": 0,
                            "depositSplitPercentage": 0,
                            "daysRequiredInAdvanceForBooking": 0,
                            "extraNightAlterationStrategyUseGlobalNights": false
                        },
                        "taxes": [
                            {
                                "name": "Test Tax",
                                "uuid": "677188f0-d2c2-45df-bdf0-35a0e1bd5388",
                                "type": "TYPE_TAX",
                                "amount": 0.05,
                                "calculationMethod": "percentage",
                                "includeBasePrice": true
                            }
                        ],
                        "periods": [
                            {
                                "description": "Example Period",
                                "priority": 500,
                                "conditionOperand": "AND",
                                "conditions": [
                                    {
                                        "type": "date",
                                        "modifyRatePerUnit": false,
                                        "startDate": "2019-02-01",
                                        "endDate": "2021-02-01",
                                        "arrivalDays": [],
                                        "departureDays": []
                                    }
                                ],
                                "minimumNights": "0",
                                "bookableType": null,
                                "rate": {
                                    "type": "nightly",
                                    "amount": 100,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "equals",
                                    "applicableTaxes": []
                                }
                            }
                        ],
                        "modifiers": [
                            {
                                "type": "cleaning",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "description": "Cleaning",
                                "conditionOperand": "AND",
                                "conditions": [],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 100,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": []
                                }
                            },
                            {
                                "type": "card_fee",
                                "hidden": true,
                                "splitMethod": "ON_TOTAL",
                                "description": "Card Fee",
                                "conditionOperand": "AND",
                                "conditions": [],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 0.015,
                                    "calculationMethod": "percentage",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": []
                                }
                            },
                            {
                                "type": "modifier",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "description": "Per Guest Per Night",
                                "conditionOperand": "AND",
                                "conditions": [
                                    {
                                        "type": "guests",
                                        "modifyRatePerUnit": true,
                                        "minimum": 8
                                    },
                                    {
                                        "type": "nights",
                                        "modifyRatePerUnit": true
                                    }
                                ],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 25,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": []
                                }
                            }
                        ]
                    }
                ]
            }
        ';

        $schema = json_decode($rentalSchemaData, true);
        $pricing = json_decode($pricingConfig, true);

        $upp = new Upp(
            new HashMapPricingResolver(ArrayUtils::getNestedArrayValue('mixins', $pricing, [])),
            new TestTranslator()
        );

        $losGenerator = new LosGenerator($upp);

        $losOptions = new LosOptions(
            'GBP',
            new \DateTime('2019-02-01'),
            new \DateTime('2019-05-31')
        );

        $losOptions->setBookingDate(new \DateTime('2019-01-01'));

        $losOptions->setForceFullGeneration(false);

        // The test rates are generated without a fee that is always applied. This option should remove these
        $losOptions->setPricingContextCalculationMode([PricingContext::CALCULATION_MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES]);

        $ld = LookupDirectorFactory::newFromRentalData($schema, $losOptions);
        $parsed = $upp->parsePricingConfig($pricing, new StructureOptions());

        $losRecords1 = $losGenerator->generateLosRecords($losOptions, $ld, $parsed);
        $losRecords = (new LosRecordMerger())->merge([$losRecords1]);
        

        $options = new TransformOptions();
        
        $transformer = new AirbnbRecordTransformer();
        $output = json_encode($transformer->transform($losRecords, $options), JSON_PRETTY_PRINT);

        $this->assertStringEqualsFile(__DIR__ . '/Resources/los_test_01.txt', $output);
    }

    public function testLosEsTransformer()
    {
        $rentalSchemaData = '
            {
                "supportedLocales": [
                    "en",
                    "ru"
                ],              
                "flags": {
                    "isActive": false
                },
                "name": "Villa Louise Builder",
                "listing": {
                    "type": "LISTING_TYPE_BUILDING",
                    "beds": 0,
                    "sleeps": 3,
                    "maxOccupancy": 16,
                    "bedrooms": 1,
                    "bathrooms": 0
                },
                "unitAvailability": {
                    "dateRange": {
                        "startDate": "2019-02-28",
                        "endDate": "2022-02-28"
                    },
                    "changeoverDefault": 3,
                    "minPriorNotifyDefault": 0,
                    "minStayDefault": 0,
                    "instantBookableDefault": "Y",
                    "configuration": {
                        "changeover": "23323222332320233232220323222332322233232223323022332322200232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333",
                        "availability": "NNNNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN",
                        "minStay": "0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0",
                        "maxStay": "30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30"
                    }
                }
            }
        ';

        $pricingConfig = '
            {
                "name": "Pricing",
                "schema": "property-pricing",
                "version": "0.0.1",
                "meta": [],
                "data": [
                    {
                        "currency": "GBP",
                        "defaults": {
                            "damageDeposit": 0,
                            "damageDepositCalculationMethod": "fixed",
                            "perPetPerStay": 0,
                            "perPetPerNight": 0,
                            "perPetSplitMethod": "ON_TOTAL",
                            "minimumNights": 0,
                            "balanceDaysBeforeArrival": 0,
                            "depositSplitPercentage": 0,
                            "daysRequiredInAdvanceForBooking": 0,
                            "extraNightAlterationStrategyUseGlobalNights": false
                        },
                        "taxes": [
                            {
                                "name": "Test Tax",
                                "uuid": "677188f0-d2c2-45df-bdf0-35a0e1bd5388",
                                "type": "TYPE_TAX",
                                "amount": 0.05,
                                "calculationMethod": "percentage",
                                "includeBasePrice": true
                            }
                        ],
                        "periods": [
                            {
                                "description": "Example Period",
                                "priority": 500,
                                "conditionOperand": "AND",
                                "conditions": [
                                    {
                                        "type": "date",
                                        "modifyRatePerUnit": false,
                                        "startDate": "2019-02-01",
                                        "endDate": "2021-02-01",
                                        "arrivalDays": [],
                                        "departureDays": []
                                    }
                                ],
                                "minimumNights": "0",
                                "bookableType": null,
                                "rate": {
                                    "type": "nightly",
                                    "amount": 100,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "equals",
                                    "applicableTaxes": []
                                }
                            }
                        ],
                        "modifiers": [
                            {
                                "type": "cleaning",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "description": "Cleaning",
                                "conditionOperand": "AND",
                                "conditions": [],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 100,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": []
                                }
                            },
                            {
                                "type": "card_fee",
                                "hidden": true,
                                "splitMethod": "ON_TOTAL",
                                "description": "Card Fee",
                                "conditionOperand": "AND",
                                "conditions": [],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 0.015,
                                    "calculationMethod": "percentage",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": []
                                }
                            },
                            {
                                "type": "modifier",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "description": "Per Guest Per Night",
                                "conditionOperand": "AND",
                                "conditions": [
                                    {
                                        "type": "guests",
                                        "modifyRatePerUnit": true,
                                        "minimum": 8
                                    },
                                    {
                                        "type": "nights",
                                        "modifyRatePerUnit": true
                                    }
                                ],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 25,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": []
                                }
                            }
                        ]
                    }
                ]
            }
        ';

        $schema = json_decode($rentalSchemaData, true);
        $pricing = json_decode($pricingConfig, true);

        $upp = new Upp(
            new HashMapPricingResolver(ArrayUtils::getNestedArrayValue('mixins', $pricing, [])),
            new TestTranslator()
        );

        $losGenerator = new LosGenerator($upp);

        $losOptions = new LosOptions(
            'GBP',
            new \DateTime('2019-02-01'),
            new \DateTime('2019-05-31')
        );

        $losOptions->setBookingDate(new \DateTime('2019-01-01'));

        $losOptions->setForceFullGeneration(false);

        // The test rates are generated without a fee that is always applied. This option should remove these
        $losOptions->setPricingContextCalculationMode([PricingContext::CALCULATION_MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES]);

        $ld = LookupDirectorFactory::newFromRentalData($schema, $losOptions);
        $parsed = $upp->parsePricingConfig($pricing, new StructureOptions());

        $losRecords1 = $losGenerator->generateLosRecords($losOptions, $ld, $parsed);
        $losRecords = (new LosRecordMerger())->merge([$losRecords1]);


        $options = new TransformOptions();
        $options->setIndexRecordsByDate(true);
        $transformer = new ElasticSearchTransformer();
        $output = json_encode($transformer->transform($losRecords, $options), JSON_PRETTY_PRINT);

        $this->assertStringEqualsFile(__DIR__ . '/Resources/los_test_es_transformer.txt', $output);
    }

    public function testLosRecordsWithLimitedUnitAvailability()
    {
        $rentalSchemaData = '
            {
              "$locale": "en_GB",
              "$providerListingId": "a737423cbbfaf73f8c06940f32f5616622da7ae8100e6db1803e34a4f9019075ac9e5f3752a58cc4da7b2e21743429b6d9cab83c1f518abe996219cd7e3b2488",
              "$schema": "https://raw.githubusercontent.com/aptenex/listing-schema/master/src/listing.json",
              "flags": {
                "isActive": true,
                "isDeleted": false,
                "isInstantBookable": false
              },
              "listing": {
                "arrangement": "ENTIRE_LISTING",
                "bathrooms": 2,
                "bedrooms": 2,
                "housekeeping": "NON_PROVIDED",
                "maxOccupancy": 5,
                "meals": "NON_PROVIDED",
                "sleeps": 4,
                "type": "LISTING_TYPE_VILLA"
              },
              "name": "Villa Masa",
              "pricing": {
                "static": {
                  "currency": "THB",
                  "ranges": [
                    {
                      "endDate": "2019-08-31",
                      "name": "Summer Hot Season 2019",
                      "standardNightPrice": 4200,
                      "startDate": "2019-07-01"
                    },
                    {
                      "endDate": "2019-10-31",
                      "name": "Summer Season 2019 2",
                      "standardNightPrice": 3200,
                      "startDate": "2019-09-01"
                    },
                    {
                      "endDate": "2019-12-19",
                      "name": "November 2019",
                      "standardNightPrice": 6000,
                      "startDate": "2019-11-01"
                    },
                    {
                      "endDate": "2020-02-10",
                      "name": "Peak Season",
                      "standardNightPrice": 9500,
                      "startDate": "2019-12-20"
                    }
                  ]
                },
                "visual": {
                  "currency": "THB",
                  "nightlyHigh": "9500",
                  "nightlyLow": "2700"
                }
              },
              "unitAvailability": {
                "availabilityDefault": "N",
                "changeoverDefault": "3",
                "configuration": {
                  "availability": "NNNNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN"
                },
                "dateRange": {
                  "endDate": "2022-06-30",
                  "startDate": "2019-07-01"
                },
                "instantBookableDefault": "N",
                "maxStayDefault": 30,
                "minPriorNotifyDefault": 0,
                "minStayDefault": 0
              }
            }
        ';

        $pricingConfig = '
            {
              "data": [
                {
                  "currency": "THB",
                  "defaults": {
                    "balanceDaysBeforeArrival": 42,
                    "damageDeposit": "10000",
                    "damageDepositCalculationMethod": "fixed",
                    "damageDepositSplitMethod": "ON_DEPOSIT",
                    "daysRequiredInAdvanceForBooking": 0,
                    "depositSplitPercentage": "20",
                    "extraNightAlterationStrategyUseGlobalNights": false,
                    "minimumNights": "2"
                  },
                  "modifiers": [
                    {
                      "conditionOperand": "AND",
                      "conditions": [],
                      "description": "Cleaning",
                      "hidden": false,
                      "rate": {
                        "amount": 0,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "addition",
                        "type": "adjustment"
                      },
                      "splitMethod": "ON_TOTAL",
                      "type": "cleaning"
                    },
                    {
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "dates": [],
                          "strategy": "STRATEGY_MATCH",
                          "type": "multi_date"
                        },
                        {
                          "inverse": false,
                          "maximum": 5,
                          "minimum": 0,
                          "modifyRatePerUnit": false,
                          "type": "booking_days"
                        }
                      ],
                      "description": "Last Minute 10 Discount",
                      "hidden": false,
                      "rate": {
                        "amount": 0.1,
                        "applicableTaxes": [],
                        "calculationMethod": "percentage",
                        "calculationOperand": "subtraction",
                        "taxable": false,
                        "type": "adjustment"
                      },
                      "splitMethod": "ON_TOTAL",
                      "type": "modifier"
                    }
                  ],
                  "periods": [
                    {
                      "bookableType": null,
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "arrivalDays": [],
                          "departureDays": [],
                          "endDate": "2019-08-31",
                          "modifyRatePerUnit": false,
                          "startDate": "2019-07-01",
                          "type": "date"
                        }
                      ],
                      "description": "Summer Hot Season 2019",
                      "minimumNights": 2,
                      "priority": 500,
                      "rate": {
                        "amount": 4200,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "type": "nightly"
                      }
                    },
                    {
                      "bookableType": null,
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "arrivalDays": [],
                          "departureDays": [],
                          "endDate": "2019-10-31",
                          "modifyRatePerUnit": false,
                          "startDate": "2019-09-01",
                          "type": "date"
                        }
                      ],
                      "description": "Summer Season 2019 2",
                      "minimumNights": 2,
                      "priority": 500,
                      "rate": {
                        "amount": 3200,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "type": "nightly"
                      }
                    },
                    {
                      "bookableType": null,
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "arrivalDays": [],
                          "departureDays": [],
                          "endDate": "2019-12-19",
                          "modifyRatePerUnit": false,
                          "startDate": "2019-11-01",
                          "type": "date"
                        }
                      ],
                      "description": "November 2019",
                      "minimumNights": 2,
                      "priority": 500,
                      "rate": {
                        "amount": 6000,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "type": "nightly"
                      }
                    },
                    {
                      "bookableType": null,
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "arrivalDays": [],
                          "departureDays": [],
                          "endDate": "2020-02-10",
                          "modifyRatePerUnit": false,
                          "startDate": "2019-12-20",
                          "type": "date"
                        }
                      ],
                      "description": "Peak Season",
                      "minimumNights": 7,
                      "priority": 500,
                      "rate": {
                        "amount": 9500,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "type": "nightly"
                      }
                    }
                  ],
                  "taxes": []
                }
              ],
              "meta": [],
              "name": "Pricing",
              "schema": "property-pricing",
              "version": "0.0.1"
            }
        ';

        $schema = json_decode($rentalSchemaData, true);
        $pricing = json_decode($pricingConfig, true);

        $upp = new Upp(
            new HashMapPricingResolver(ArrayUtils::getNestedArrayValue('mixins', $pricing, [])),
            new TestTranslator()
        );

        $losGenerator = new LosGenerator($upp);

        $losOptions = new LosOptions(
            'THB',
            new \DateTime('2019-07-01'),
            new \DateTime('2020-06-30')
        );

        $losOptions->setBookingDate(new \DateTime('2019-06-01'));

        $losOptions->setForceFullGeneration(false);

        // The test rates are generated without a fee that is always applied. This option should remove these
        $losOptions->setPricingContextCalculationMode([PricingContext::CALCULATION_MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES]);

        $ld = LookupDirectorFactory::newFromRentalData($schema, $losOptions);
        $parsed = $upp->parsePricingConfig($pricing, new StructureOptions());

        $losRecords1 = $losGenerator->generateLosRecords($losOptions, $ld, $parsed);
        $losRecords = (new LosRecordMerger())->merge([$losRecords1]);

        $options = new TransformOptions();
        $transformer = new AirbnbRecordTransformer();
        $output = json_encode($transformer->transform($losRecords, $options), JSON_PRETTY_PRINT);

        $this->assertStringEqualsFile(__DIR__ . '/Resources/los_test_limited_unit_availability.txt', $output);
    }

    public function testLosRecordsWithLimitedUnitAvailability2()
    {
        $rentalSchemaData = '
            {
                "listing": {
                    "maxOccupancy": 10
                },
                "unitAvailability": {
                    "availabilityDefault": "N",
                    "changeoverDefault": "3",
                    "configuration": {
                      "availability": "NNNNNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN"
                    },
                    "dateRange": {
                      "endDate": "2022-06-30",
                      "startDate": "2019-07-01"
                    },
                    "instantBookableDefault": "N",
                    "maxStayDefault": 30,
                    "minPriorNotifyDefault": 0,
                    "minStayDefault": 0
                  }
            }
        ';

        $pricingConfig = '
            {
              "data": [
                {
                  "currency": "THB",
                  "defaults": {
                    "balanceDaysBeforeArrival": 42,
                    "damageDeposit": "200",
                    "damageDepositCalculationMethod": "fixed",
                    "damageDepositSplitMethod": "ON_DEPOSIT",
                    "daysRequiredInAdvanceForBooking": 0,
                    "depositSplitPercentage": "NULL",
                    "extraNightAlterationStrategyUseGlobalNights": false,
                    "minimumNights": "2"
                  },
                  "modifiers": [
                    {
                      "conditionOperand": "AND",
                      "conditions": [],
                      "description": "Cleaning",
                      "hidden": false,
                      "rate": {
                        "amount": 0,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "addition",
                        "type": "adjustment"
                      },
                      "splitMethod": "ON_TOTAL",
                      "type": "cleaning"
                    },
                    {
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "maximum": 9,
                          "minimum": 9,
                          "modifyRatePerUnit": true,
                          "type": "guests"
                        },
                        {
                          "maximum": null,
                          "minimum": 0,
                          "modifyRatePerUnit": true,
                          "type": "nights"
                        }
                      ],
                      "description": "Extra Guests",
                      "hidden": false,
                      "rate": {
                        "amount": 1000,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "addition",
                        "type": "adjustment"
                      },
                      "splitMethod": "ON_TOTAL",
                      "type": "modifier"
                    }
                  ],
                  "periods": [
                    {
                      "bookableType": null,
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "arrivalDays": [],
                          "departureDays": [],
                          "endDate": "2020-04-30",
                          "modifyRatePerUnit": false,
                          "startDate": "2020-01-16",
                          "type": "date"
                        }
                      ],
                      "description": "High Season",
                      "minimumNights": 2,
                      "priority": 500,
                      "rate": {
                        "amount": 17000,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "type": "nightly"
                      }
                    },
                    {
                      "bookableType": null,
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "arrivalDays": [],
                          "departureDays": [],
                          "endDate": "2020-06-16",
                          "modifyRatePerUnit": false,
                          "startDate": "2020-05-01",
                          "type": "date"
                        }
                      ],
                      "description": "Low Season",
                      "minimumNights": 2,
                      "priority": 500,
                      "rate": {
                        "amount": 16000,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "type": "nightly"
                      }
                    },
                    {
                      "bookableType": null,
                      "conditionOperand": "AND",
                      "conditions": [
                        {
                          "arrivalDays": [],
                          "departureDays": [],
                          "endDate": "2020-07-31",
                          "modifyRatePerUnit": false,
                          "startDate": "2020-06-16",
                          "type": "date"
                        }
                      ],
                      "description": "Mid Season",
                      "minimumNights": 2,
                      "priority": 500,
                      "rate": {
                        "amount": 16500,
                        "applicableTaxes": [],
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "type": "nightly"
                      }
                    }
                  ],
                  "taxes": []
                }
              ],
              "meta": [],
              "name": "Pricing",
              "schema": "property-pricing",
              "version": "0.0.1"
            }
        ';

        $schema = json_decode($rentalSchemaData, true);
        $pricing = json_decode($pricingConfig, true);

        $upp = new Upp(
            new HashMapPricingResolver(ArrayUtils::getNestedArrayValue('mixins', $pricing, [])),
            new TestTranslator()
        );

        $losGenerator = new LosGenerator($upp);

        $losOptions = new LosOptions(
            'THB',
            new \DateTime('2020-04-01'),
            new \DateTime('2020-06-30')
        );

        $losOptions->setForceFullGeneration(false);

        // The test rates are generated without a fee that is always applied. This option should remove these
        $losOptions->setPricingContextCalculationMode([
            PricingContext::CALCULATION_MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES,
            PricingContext::CALCULATION_MODE_LOS_EXCLUDE_DAMAGE_DEPOSIT
        ]);

        $ld = LookupDirectorFactory::newFromRentalData($schema, $losOptions);
        $parsed = $upp->parsePricingConfig($pricing, new StructureOptions());

        $losRecords1 = $losGenerator->generateLosRecords($losOptions, $ld, $parsed);

        $losRecords = (new LosRecordMerger())->merge([$losRecords1]);

        $options = new TransformOptions();
        $transformer = new AirbnbRecordTransformer();
        $output = json_encode($transformer->transform($losRecords, $options), JSON_PRETTY_PRINT);

        $this->assertStringEqualsFile(__DIR__ . '/Resources/los_test_limited_unit_availability_02.txt', $output);
    }

    public function testPthreadsLosRecords()
    {
        $this->assertTrue($disabled = true);
        return;

        $rentalSchemaData = '
            {
                "supportedLocales": [
                    "en",
                    "ru"
                ],
                "texts": [
                    {
                        "type": "PRIMARY_DESCRIPTION",
                        "description": {
                            "en": {
                                "content": "test",
                                "locale": "en"
                            }
                        }
                    },
                    {
                        "type": "ABOUT_NEIGHBOURHOOD",
                        "description": {
                            "en": {
                                "title": "test",
                                "content": "tes",
                                "locale": "en"
                            }
                        }
                    },
                    {
                        "type": "ABOUT_DESTINATION",
                        "description": {
                            "en": {
                                "title": "ttest",
                                "content": "test",
                                "locale": "en"
                            }
                        }
                    },
                    {
                        "type": "WHAT_IS_INCLUDED",
                        "description": {
                            "en": {
                                "title": "test",
                                "content": "test",
                                "locale": "en"
                            }
                        }
                    },
                    {
                        "type": "WHAT_IS_NOT_INCLUDED",
                        "description": {
                            "en": {
                                "title": "test",
                                "content": "test",
                                "locale": "en"
                            }
                        }
                    }
                ],
                "address": {
                    "addressUnitNameNumber": "21 Bishops Close",
                    "city": "Torquay",
                    "stateProvince": "Devon",
                    "zipPostCode": "TQ1 2PL",
                    "countryISO2": "GB"
                },
                "media": [
                    {
                        "type": "URI",
                        "category": "PHOTO",
                        "uri": "https:\/\/procuro-property-assets.s3.amazonaws.com\/1\/ebfbaa9b-22ba-4474-978a-3a1c18dc2c1a\/1549879416500-boulevard-du-temple-1838.jpg",
                        "position": 0,
                        "isFeatured": false
                    }
                ],
                "features": [
                    {
                        "type": "SPA_POOL_SWIMMING_POOL",
                        "category": "Amenities",
                        "isSpecial": false,
                        "isOnsite": false,
                        "subType": [
                            "SPA_POOL_POOL_PRIVATE"
                        ]
                    },
                    {
                        "type": "CUSTOM_TEST_CUSTOM",
                        "category": "Amenities",
                        "isSpecial": false,
                        "isOnsite": false,
                        "description": {
                            "en": {
                                "content": "test",
                                "locale": "en",
                                "title": "PAGES.FACILITIES.FORM_LABELS.TEST_CUSTOM"
                            }
                        }
                    }
                ],
                "flags": {
                    "isActive": false
                },
                "name": "Villa Louise Builder",
                "listing": {
                    "type": "LISTING_TYPE_BUILDING",
                    "beds": 0,
                    "sleeps": 3,
                    "maxOccupancy": 16,
                    "bedrooms": 1,
                    "bathrooms": 0
                },
                "rooms": [
                    {
                        "type": "BEDROOM",
                        "name": "Bedroom #1",
                        "isSharedSpace": false,
                        "isLockable": false
                    }
                ],
                "unitAvailability": {
                    "dateRange": {
                        "startDate": "2019-02-28",
                        "endDate": "2022-02-28"
                    },
                    "changeoverDefault": 3,
                    "minPriorNotifyDefault": 0,
                    "minStayDefault": 0,
                    "instantBookableDefault": "Y",
                    "configuration": {
                        "changeover": "23323222332320233232220323222332322233232223323022332322200232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333",
                        "availability": "NNNNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN",
                        "minStay": "0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0",
                        "maxStay": "30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30"
                    }
                }
            }
        ';

        $pricingConfig = '
            {
                "name": "Pricing",
                "schema": "property-pricing",
                "version": "0.0.1",
                "meta": [],
                "data": [
                    {
                        "currency": "GBP",
                        "defaults": {
                            "damageDeposit": 0,
                            "damageDepositCalculationMethod": "fixed",
                            "perPetPerStay": 0,
                            "perPetPerNight": 0,
                            "perPetSplitMethod": "ON_TOTAL",
                            "minimumNights": 0,
                            "balanceDaysBeforeArrival": 0,
                            "depositSplitPercentage": 0,
                            "daysRequiredInAdvanceForBooking": 0,
                            "extraNightAlterationStrategyUseGlobalNights": false
                        },
                        "taxes": [],
                        "periods": [
                            {
                                "description": "Example Period",
                                "priority": 500,
                                "conditionOperand": "AND",
                                "conditions": [
                                    {
                                        "type": "date",
                                        "modifyRatePerUnit": false,
                                        "startDate": "2019-02-01",
                                        "endDate": "2021-02-01",
                                        "arrivalDays": [],
                                        "departureDays": []
                                    }
                                ],
                                "minimumNights": "0",
                                "bookableType": null,
                                "rate": {
                                    "type": "nightly",
                                    "amount": 100,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "equals",
                                    "applicableTaxes": []
                                }
                            }
                        ],
                        "modifiers": [
                            {
                                "type": "modifier",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "description": "Per Guest Per Night",
                                "conditionOperand": "AND",
                                "conditions": [
                                    {
                                        "type": "guests",
                                        "modifyRatePerUnit": true,
                                        "minimum": 8
                                    },
                                    {
                                        "type": "nights",
                                        "modifyRatePerUnit": true
                                    }
                                ],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 25,
                                    "calculationMethod": "fixed",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": []
                                }
                            }
                        ]
                    }
                ]
            }
        ';

        $schema = json_decode($rentalSchemaData, true);
        $pricing = json_decode($pricingConfig, true);

        $upp = new Upp(
            new HashMapPricingResolver(ArrayUtils::getNestedArrayValue('mixins', $pricing, [])),
            new TestTranslator()
        );

        $losGenerator = new LosGenerator($upp);

        $parsed = $upp->parsePricingConfig($pricing, new StructureOptions());


        $dateChunks = DateUtils::getDateChunks(new \DateTime('2019-02-28'), new \DateTime('2020-08-28'), 8);

        /** @var LosGeneratorTask[] $chunkTasks */
        $chunkTasks = [];

        $pool = new Pool(8, Autoloader::class, ['vendor/autoload.php']);

        foreach($dateChunks as $chunk) {
            list($start, $end) = $chunk;
            $losOptions = new LosOptions('GBP', $start, $end);
            $losOptions->setForceFullGeneration(false);
            $ld = LookupDirectorFactory::newFromRentalData($schema, $losOptions);
            $lgt = new LosGeneratorTask($losGenerator, $losOptions, $ld, $parsed);
            $chunkTasks[] = $lgt;
            $pool->submit($lgt);
        }

        $pool->shutdown();

        $results = [];
        foreach($chunkTasks as $lgt) {
            $results[] = $lgt->getRecords();
        }

        $losRecords = (new LosRecordMerger())->merge($results);

        echo PHP_EOL . $losRecords->getMetrics()->getRunDataToString() . PHP_EOL;

        $options = new TransformOptions();
        $options->setBcomRateId('111');
        $options->setBcomRoomId('222');

        $transformer = new AirbnbRecordTransformer();

        echo PHP_EOL;
        echo PHP_EOL;
        echo json_encode($transformer->transform($losRecords, $options), JSON_PRETTY_PRINT);

        $this->assertTrue(true);
    }

}