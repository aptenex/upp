<?php

namespace Tests;

use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\ArrayUtils;
use Los\Lookup\LookupDirectorFactory;
use Los\LosGenerator;
use Los\LosOptions;
use Los\Transformer\AirbnbRecordTransformer;
use Los\Transformer\SimpleArrayRecordTransformer;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Translation\TestTranslator;

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
                    "maxOccupancy": 8,
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
                        "changeover": "23323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332322233232223323222332333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333",
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
                                        "minimum": 3
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

        $structureOptions = new StructureOptions();
        $parsed = $upp->parsePricingConfig($pricing, $structureOptions);

        $losGenerator = new LosGenerator($upp);

        $losOptions = new LosOptions(new \DateTime('2019-03-01'), new \DateTime('2020-09-01'));
        //$losOptions->setForceFullGeneration(true);

        $ld = LookupDirectorFactory::newFromRentalData($schema, $losOptions);

        $losRecords = $losGenerator->generateLosRecords($losOptions, $ld, $parsed);

        echo PHP_EOL . $losRecords->getRunDataToString() . PHP_EOL;

        $airbnb = new AirbnbRecordTransformer();
        $simple = new SimpleArrayRecordTransformer();
        //echo PHP_EOL . PHP_EOL . json_encode($simple->transform($losRecords), JSON_PRETTY_PRINT);
        echo PHP_EOL;
        echo PHP_EOL;
        echo json_encode($airbnb->transform($losRecords), JSON_PRETTY_PRINT);
    }

}