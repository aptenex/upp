<?php

namespace Aptenex\Upp\Context;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PricingContext
{

    /**
     * @var string
     */
    private $description;

    /**
     * @Currency()
     * @NotBlank()
     *
     * @var string
     */
    private $currency;

    /**
     * @Date()
     *
     * @var string
     */
    private $arrivalDate;

    /**
     * @Date()
     *
     * @var string
     */
    private $departureDate;

    /**
     * @Date()
     *
     * @var string
     */
    private $bookingDate;

    /**
     * @NotBlank()
     * @Type(type="integer")
     *
     * @var int
     */
    private $guests = null;

    /**
     * @Type(type="integer")
     *
     * @var int
     */
    private $adults = null;

    /**
     * @Type(type="integer")
     *
     * @var int
     */
    private $children = null;

    /**
     * @Type(type="integer")
     *
     * @var int
     */
    private $infants = null;

    /**
     * @Valid()
     *
     * @var array
     */
    private $extras = [];

    /**
     * @Type(type="string")
     * @Assert\Length(max="3")
     *
     * @var null|string
     */
    private $locale = 'en';

    /**
     * Test mode disables various checks like not being able to check a price in the past
     *
     * @var bool
     */
    private $testMode = false;

    /**
     * @var array
     */
    private $configOverride;

    public function __construct()
    {
        $this->setBookingDate(date("Y-m-d"));
    }

    /**
     * @Callback
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        $canValidateDates = true;

        if (empty($this->getBookingDate())) {
            $canValidateDates = false;
            $context
                ->buildViolation('The booking date is required')
                ->atPath('bookingDate')
                ->addViolation()
            ;
        }

        if (empty($this->getArrivalDate())) {
            $canValidateDates = false;
            $context
                ->buildViolation('The arrival date is required')
                ->atPath('arrivalDate')
                ->addViolation()
            ;
        }

        if (empty($this->getDepartureDate())) {
            $canValidateDates = false;
            $context
                ->buildViolation('The departure date is required')
                ->atPath('departureDate')
                ->addViolation()
            ;
        }

        if ($canValidateDates && $this->getDepartureDateObj() <= $this->getArrivalDateObj()) {
            $context
                ->buildViolation('The departure date cannot be before the arrival date')
                ->atPath('departureDate')
                ->addViolation()
            ;
        }
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getArrivalDate()
    {
        return $this->arrivalDate;
    }

    /**
     * @return \DateTime
     */
    public function getArrivalDateObj()
    {
        return new \DateTime($this->getArrivalDate());
    }

    /**
     * @param string $arrivalDate
     */
    public function setArrivalDate($arrivalDate)
    {
        $this->arrivalDate = $arrivalDate;
    }

    /**
     * @return string
     */
    public function getDepartureDate()
    {
        return $this->departureDate;
    }

    /**
     * @return \DateTime
     */
    public function getDepartureDateObj()
    {
        return new \DateTime($this->getDepartureDate());
    }

    /**
     * @param string $departureDate
     */
    public function setDepartureDate($departureDate)
    {
        $this->departureDate = $departureDate;
    }

    /**
     * @return int
     */
    public function getNoNights()
    {
        $interval = $this->getArrivalDateObj()->diff($this->getDepartureDateObj());

        $days = $interval->format("%r%a");

        return (int) $days;
    }

    /**
     * @return int
     */
    public function getNoDaysBeforeArrival()
    {
        $interval = $this->getBookingDateObj()->diff($this->getArrivalDateObj());

        $days = $interval->format("%r%a");

        return (int) $days;
    }

    /**
     * @return string
     */
    public function getBookingDate()
    {
        return $this->bookingDate;
    }

    /**
     * @return \DateTime
     */
    public function getBookingDateObj()
    {
        return new \DateTime($this->getBookingDate());
    }

    /**
     * @param string $bookingDate
     */
    public function setBookingDate($bookingDate)
    {
        $this->bookingDate = $bookingDate;
    }

    /**
     * @return int
     */
    public function getGuests()
    {
        return $this->guests;
    }

    /**
     * @param int $guests
     */
    public function setGuests($guests)
    {
        $this->guests = (int) $guests;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     */
    public function setAdults($adults)
    {
        $this->adults = (int) $adults;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param int $children
     */
    public function setChildren($children)
    {
        $this->children = (int) $children;
    }

    /**
     * @return int
     */
    public function getInfants()
    {
        return $this->infants;
    }

    /**
     * @param int $infants
     */
    public function setInfants($infants)
    {
        $this->infants = (int) $infants;
    }

    /**
     * @return array
     */
    public function getConfigOverride()
    {
        return $this->configOverride;
    }

    /**
     * @param array $configOverride
     */
    public function setConfigOverride($configOverride)
    {
        $this->configOverride = $configOverride;
    }

    /**
     * @return bool
     */
    public function hasConfigOverride()
    {
        return is_array($this->getConfigOverride());
    }

    /**
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param array $extras
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;
    }

    /**
     * @param array $extraData
     */
    public function addExtras($extraData)
    {
        $this->extras[] = Extra::initializeFromData($extraData);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param null|string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * @param bool $testMode
     */
    public function setTestMode(bool $testMode)
    {
        $this->testMode = $testMode;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'arrivalDate'       => $this->getArrivalDate(),
            'departureDate'     => $this->getDepartureDate(),
            'bookingDate'       => $this->getBookingDate(),
            'guests'            => $this->getGuests(),
            'adults'            => $this->getAdults(),
            'children'          => $this->getChildren(),
            'infants'           => $this->getInfants(),
            'locale'            => $this->getLocale()
        ];
    }

    /**
     * @param array $data
     * @return PricingContext
     */
    public static function initializeFromArray(array $data)
    {
        $pc = new PricingContext();

        $arrayWhitelist = ['extras'];

        foreach ($data as $key => $value) {

            if (is_array($value) && in_array($key, $arrayWhitelist, true)) {

                $adder = sprintf('add%s', ucfirst($key));

                foreach($value as $dataItem) {
                    $pc->$adder($dataItem);
                }

            } else {

                $setter = sprintf('set%s', ucfirst($key));

                if (!method_exists($pc, $setter)) {
                    continue;
                }

                $pc->$setter($value);

            }
        }

        return $pc;
    }

}