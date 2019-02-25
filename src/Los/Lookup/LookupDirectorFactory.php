<?php
namespace Los\Lookup;

class LookupDirectorFactory
{

    public static function newDirectorFromUnitAvailability(array $unitAvailability): LookupDirector
    {
        return new LookupDirector(
            new AvailabilityStringLookup()
        );
    }

}