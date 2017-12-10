<?php

namespace Aptenex\Upp\Parser\Structure;

class SplitMethod
{

    const ON_DEPOSIT = "ON_DEPOSIT";
    const ON_BALANCE = "ON_BALANCE";
    const ON_TOTAL = "ON_TOTAL";
    const ON_ARRIVAL = "ON_ARRIVAL";

    private $enum = [
        self::ON_DEPOSIT => "ON_DEPOSIT",
        self::ON_BALANCE => "ON_BALANCE",
        self::ON_TOTAL   => "ON_TOTAL",
        self::ON_ARRIVAL => "ON_ARRIVAL"
    ];

}