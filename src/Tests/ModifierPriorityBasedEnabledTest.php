<?php

namespace Tests;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Upp;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Translation\TestTranslator;

class ModifierPriorityBasedEnabledTest extends TestCase
{

    private $config = '{
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
            "maximumNights": "",
            "balanceDaysBeforeArrival": 0,
            "depositSplitPercentage": 0,
            "daysRequiredInAdvanceForBooking": 0,
            "bookableType": "instant_bookable",
            "extraNightAlterationStrategyUseGlobalNights": false,
            "damageDepositSplitMethod": "ON_TOTAL",
            "modifiersUseCategorizedCalculationOrder": true,
            "enablePriorityBasedModifiers": true
          },
          "taxes": [],
          "periods": [
            {
              "description": "LOS high season 7 nights discount",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-01-01",
                  "endDate": "2020-09-30",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 100,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": [],
                "strategy": {
                  "extraNightsAlteration": {
                    "applyToTotal": true,
                    "makePreviousNightsSameRate": true,
                    "calculationMethod": "percentage",
                    "calculationOperand": "equals",
                    "brackets": [
                      {
                        "night": "7+",
                        "amount": 0.9,
                        "days": [],
                        "matchAmount": 0,
                        "damageDepositOverride": null
                      }
                    ]
                  }
                }
              }
            }
          ],
          "modifiers": [
            {
              "type": "service_charge",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "SERVICE FEE : 5% ",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.05,
                "calculationMethod": "percentage",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "priority": 3,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB6 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 90
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "priority": 3,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB3 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 90
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "priority": 1,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM30 10%",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "priority": 2,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM15 10%",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            }
          ]
        }
      ]
    }';

    public function testModifierPriorityEnabledCumulativeDiscounts()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $options = new StructureOptions();

        $config = $upp->parsePricingConfig(json_decode($this->config, true), $options);

        $cc = $config->getCurrencyConfig('GBP');

        $this->assertTrue($cc->getDefaults()->isEnablePriorityBasedModifiers());

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2020-03-05');
        $context->setArrivalDate('2020-06-01');
        $context->setDepartureDate('2020-06-02');
        $context->setGuests(2);

        $price = $upp->generatePrice($context, $config);

        $expectedAdjustments = ["MOD LM15 10%", "SERVICE FEE : 5%"];
        $actualAdjustments = [];
        foreach($price->getAdjustments() as $item) {
            $actualAdjustments[] = trim($item->getDescription());
        }

        $this->assertSame($expectedAdjustments, $actualAdjustments);

        $context2 = new PricingContext();
        $context2->setCurrency('GBP');
        $context2->setBookingDate('2019-01-10');
        $context2->setArrivalDate('2020-03-14');
        $context2->setDepartureDate('2020-03-15');
        $context2->setGuests(2);

        $price2 = $upp->generatePrice($context2, $config);

        $expectedAdjustments = ["MOD EB6 10%", "MOD EB3 10%", "SERVICE FEE : 5%"];
        $actualAdjustments = [];
        foreach($price2->getAdjustments() as $item) {
            $actualAdjustments[] = trim($item->getDescription());
        }

        $this->assertSame($expectedAdjustments, $actualAdjustments);
    }

    private $miskawaanConfig = '
        {
            "name": "Pricing",
            "schema": "property-pricing",
            "version": "0.0.1",
            "meta": [],
            "data": [
                {
                    "currency": "USD",
                    "defaults": {
                        "minimumNights": 3,
                        "maximumNights": 30,
                        "perPetPerStay": 0,
                        "perPetPerNight": 0,
                        "perPetSplitMethod": "ON_TOTAL",
                        "damageDeposit": 0,
                        "bookableType": "enquiry_with_price",
                        "damageDepositCalculationMethod": "fixed",
                        "damageDepositSplitMethod": "ON_DEPOSIT",
                        "daysRequiredInAdvanceForBooking": null,
                        "applyDiscountsToPartialMatches": false,
                        "balanceDaysBeforeArrival": 42,
                        "depositSplitPercentage": 30,
                        "periodSelectionStrategy": "DEFAULT",
                        "extraNightAlterationStrategyUseGlobalNights": true,
                        "partialWeekAlterationStrategyUseGlobalNights": false,
                        "modifiersUseCategorizedCalculationOrder": true,
                        "enablePriorityBasedModifiers": true
                    },
                    "taxes": [
                        {
                            "name": "VAT",
                            "type": "TYPE_VAT",
                            "uuid": "1cbb9ee2-03b0-4c7c-9c62-06fdbc831dc8",
                            "description": null,
                            "amount": 0.07,
                            "calculationMethod": "percentage",
                            "longStayExemption": null,
                            "includeBasePrice": true,
                            "includeExtras": true,
                            "extrasWhitelist": []
                        }
                    ],
                    "periods": [
                        {
                            "id": 21086,
                            "description": "Intermediate  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2021-09-01",
                                    "endDate": "2021-12-14",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2100,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2100,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 1700
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 1900
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2100
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2150
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21087,
                            "description": "Xmas\/ New Year ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2021-12-15",
                                    "endDate": "2022-01-10",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 3600,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": []
                            },
                            "minimumNights": "5",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21088,
                            "description": "High  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-01-11",
                                    "endDate": "2022-01-21",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2500,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2500,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 2100
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 2300
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2500
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2550
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21089,
                            "description": "Prime  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-01-22",
                                    "endDate": "2022-02-06",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2900,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2900,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 2500
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 2700
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2900
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2950
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "5",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21090,
                            "description": "High   ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-02-07",
                                    "endDate": "2022-04-09",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2500,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2500,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 2100
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 2300
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2500
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2550
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21091,
                            "description": "Prime   ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-04-10",
                                    "endDate": "2022-04-24",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2900,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2900,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 2500
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 2700
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2900
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2950
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "5",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21092,
                            "description": "Intermediate  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-04-25",
                                    "endDate": "2022-06-30",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2100,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2100,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 1700
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 1900
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2100
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2150
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21093,
                            "description": "High    ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-07-01",
                                    "endDate": "2022-08-31",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2500,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2500,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 2100
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 2300
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2500
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2550
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21094,
                            "description": "Intermediate  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-09-01",
                                    "endDate": "2022-12-14",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 2100,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": {
                                    "extraNightsAlteration": {
                                        "calculationMethod": "fixed",
                                        "calculationOperand": "equals",
                                        "calculationOperator": "equals",
                                        "applyToTotal": true,
                                        "makePreviousNightsSameRate": true,
                                        "enablePerGuestPerNight": true,
                                        "nightsMatchedOverridesPrice": false,
                                        "brackets": [
                                            {
                                                "night": "1+",
                                                "amount": 2100,
                                                "days": [],
                                                "matchAmount": 0,
                                                "damageDepositOverride": null,
                                                "guests": [
                                                    {
                                                        "guests": "1-10",
                                                        "amount": 1700
                                                    },
                                                    {
                                                        "guests": "11-12",
                                                        "amount": 1900
                                                    },
                                                    {
                                                        "guests": "13-14",
                                                        "amount": 2100
                                                    },
                                                    {
                                                        "guests": "15",
                                                        "amount": 2150
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                }
                            },
                            "minimumNights": "3",
                            "bookableType": "instant_bookable"
                        },
                        {
                            "id": 21095,
                            "description": "Xmas\/ New Year  ",
                            "priority": 500,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2022-12-15",
                                    "endDate": "2023-01-10",
                                    "arrivalDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ],
                                    "departureDays": [
                                        "monday",
                                        "tuesday",
                                        "wednesday",
                                        "thursday",
                                        "friday",
                                        "saturday",
                                        "sunday"
                                    ]
                                }
                            ],
                            "rate": {
                                "type": "nightly",
                                "amount": 3600,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "calculationOperator": "equals",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": {
                                    "calculationMethod": "fixed",
                                    "days": {
                                        "monday": {
                                            "day": "monday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "tuesday": {
                                            "day": "tuesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "wednesday": {
                                            "day": "wednesday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "thursday": {
                                            "day": "thursday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "friday": {
                                            "day": "friday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "saturday": {
                                            "day": "saturday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        },
                                        "sunday": {
                                            "day": "sunday",
                                            "amount": null,
                                            "minimumNights": null,
                                            "changeover": "ARRIVAL_OR_DEPARTURE"
                                        }
                                    }
                                },
                                "strategy": []
                            },
                            "minimumNights": "7",
                            "bookableType": "instant_bookable"
                        }
                    ],
                    "modifiers": [
                        {
                            "id": 1,
                            "description": "Service Charge generic",
                            "priority": null,
                            "conditionOperand": "AND",
                            "conditions": [],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.1,
                                "taxable": true,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [
                                    "1cbb9ee2-03b0-4c7c-9c62-06fdbc831dc8"
                                ],
                                "calculationMethod": "percentage",
                                "calculationOperand": "addition",
                                "calculationOperator": "addition",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "service_charge",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "total"
                        },
                        {
                            "id": 2,
                            "description": "30% Disc Winter Getaway",
                            "priority": 2,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2021-12-15",
                                    "endDate": "2022-03-31",
                                    "arrivalDays": [],
                                    "departureDays": []
                                }
                            ],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.3,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "percentage",
                                "calculationOperand": "subtraction",
                                "calculationOperator": "subtraction",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "discount",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "total"
                        },
                        {
                            "id": 3,
                            "description": "15% Early Bird",
                            "priority": 1,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2020-04-01",
                                    "endDate": "2022-12-14",
                                    "arrivalDays": [],
                                    "departureDays": []
                                },
                                {
                                    "type": "booking_days",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "minimum": 60,
                                    "maximum": 0
                                }
                            ],
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
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "discount",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "total"
                        },
                        {
                            "id": 4,
                            "description": "Discount: 20% for 30+ days",
                            "priority": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "type": "nights",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "minimum": 30,
                                    "maximum": 0
                                },
                                {
                                    "type": "date",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2021-08-19",
                                    "endDate": "2021-12-15",
                                    "arrivalDays": [],
                                    "departureDays": []
                                }
                            ],
                            "rate": {
                                "type": "adjustment",
                                "amount": 0.2,
                                "taxable": false,
                                "damageDeposit": 0,
                                "basePriceOnly": false,
                                "applicableTaxes": [],
                                "calculationMethod": "percentage",
                                "calculationOperand": "subtraction",
                                "calculationOperator": "subtraction",
                                "applyOverMinimumGuests": 0,
                                "daysOfWeek": null,
                                "strategy": null
                            },
                            "type": "modifier",
                            "splitMethod": "ON_TOTAL",
                            "hidden": false,
                            "priceGroup": "total"
                        }
                    ]
                }
            ]
        }
    ';

    public function testModifierPriorityEnabledCumulativeDiscountsMiskawaanSpecific()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $options = new StructureOptions();

        $config = $upp->parsePricingConfig(json_decode($this->miskawaanConfig, true), $options);

        $cc = $config->getCurrencyConfig('USD');

        $this->assertTrue($cc->getDefaults()->isEnablePriorityBasedModifiers());

        $context = new PricingContext();
        $context->setCurrency('USD');
        $context->setBookingDate('2021-09-20');
        $context->setArrivalDate('2021-12-20');
        $context->setDepartureDate('2021-12-27');
        $context->setGuests(2);

        $price = $upp->generatePrice($context, $config);

        $expectedAdjustments = ["30% Disc Winter Getaway", "Service Charge generic", "VAT"];
        $actualAdjustments = [];
        foreach($price->getAdjustments() as $item) {
            $actualAdjustments[] = trim($item->getDescription());
        }

        $this->assertSame($expectedAdjustments, $actualAdjustments);
    }

}