<?php

namespace Los\Lookup;

use Los\LosOptions;
use Aptenex\Upp\Helper\ArrayAccess;

class LookupDirectorFactory
{

    /**
     * @param array $unitAvailability
     * @param int $maxOccupancy
     * @param LosOptions $options
     *
     * @return LookupDirector
     *
     * @throws \Aptenex\Upp\Exception\CannotGenerateLosException
     */
    public static function newFromUnitAvailabilityAndMaxOccupancy(array $unitAvailability, int $maxOccupancy, LosOptions $options): LookupDirector
    {
        $startDate = new \DateTime(ArrayAccess::get('dateRange.startDate', $unitAvailability));

        $al = new AvailabilityStringLookup($startDate, ArrayAccess::get('configuration.availability', $unitAvailability));
        $cl = new ChangeoverStringLookup($startDate, ArrayAccess::get('configuration.changeover', $unitAvailability));
        $minL = new MinimumStayStringLookup($startDate, ArrayAccess::get('configuration.minStay', $unitAvailability), $options);
        $maxL = new MaximumStayStringLookup($startDate, ArrayAccess::get('configuration.maxStay', $unitAvailability), $options);
        $oL = new MaxOccupancyFixedValue($maxOccupancy);

        return new LookupDirector($al, $cl, $minL, $maxL, $oL);
    }

    /**
     * @param array $unitAvailability
     * @param array $rentalSchema
     * @param LosOptions $options
     *
     * @return LookupDirector
     *
     * @throws \Aptenex\Upp\Exception\CannotGenerateLosException
     */
    public static function newFromUnitAvailabilityAndRentalData(array $unitAvailability, array $rentalSchema, LosOptions $options): LookupDirector
    {
        $startDate = new \DateTime(ArrayAccess::get('dateRange.startDate', $unitAvailability));

        $al = new AvailabilityStringLookup($startDate, ArrayAccess::get('configuration.availability', $unitAvailability));
        $cl = new ChangeoverStringLookup($startDate, ArrayAccess::get('configuration.changeover', $unitAvailability));
        $minL = new MinimumStayStringLookup($startDate, ArrayAccess::get('configuration.minStay', $unitAvailability), $options);
        $maxL = new MaximumStayStringLookup($startDate, ArrayAccess::get('configuration.maxStay', $unitAvailability), $options);
        $oL = new MaxOccupancySchemaLookup($rentalSchema);

        return new LookupDirector($al, $cl, $minL, $maxL, $oL);
    }

    /**
     * @param array $rentalSchema
     * @param LosOptions $options
     *
     * @return LookupDirector
     *
     * @throws \Aptenex\Upp\Exception\CannotGenerateLosException
     */
    public static function newFromRentalData(array $rentalSchema, LosOptions $options): LookupDirector
    {
        $unitAvailability = ArrayAccess::get('unitAvailability', $rentalSchema);

        $startDate = new \DateTime(ArrayAccess::get('dateRange.startDate', $unitAvailability));

        $al = new AvailabilityStringLookup($startDate, ArrayAccess::get('configuration.availability', $unitAvailability));
        $cl = new ChangeoverStringLookup($startDate, ArrayAccess::get('configuration.changeover', $unitAvailability));
        $minL = new MinimumStayStringLookup($startDate, ArrayAccess::get('configuration.minStay', $unitAvailability), $options);
        $maxL = new MaximumStayStringLookup($startDate, ArrayAccess::get('configuration.maxStay', $unitAvailability), $options);
        $oL = new MaxOccupancySchemaLookup($rentalSchema);

        return new LookupDirector($al, $cl, $minL, $maxL, $oL);
    }

}