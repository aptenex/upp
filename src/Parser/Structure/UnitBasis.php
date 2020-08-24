<?php

namespace Aptenex\Upp\Parser\Structure;

class UnitBasis
{

    public const PER_PERSON      = 'per_person'; // Each person on the reservation will be billed this item ONCE.
    public const PER_PERSON_PER_WEEK = 'per_person_per_week'; // strongly recommend to avoid use, how do we handle 9 days? Full or partial?
    public const PER_PERSON_PER_NIGHT = 'per_person_per_night';
    public const PER_PERSON_PER_DAY = 'per_person_per_day';
    public const PER_RESERVATION = 'per_reservation';
    public const PER_KWH = 'per_kwh'; //reserved for electricty cost fee only.
    public const PER_HOUR = 'per_hour';
    public const PER_DAY = 'per_day'; // we have per day and per night, because if you stay for 2 nights, you really stay for 3 days...
    public const PER_NIGHT = 'per_night';
    // ACCORDING TO USE FEES CAN ONLY BE ALLOCATED TO ON_ARRIVAL
    public const ACCORDING_TO_USE = 'according_to_use'; // for example, might be suitable for 'Boat Hire', where usage is billed.

}