<?php

namespace Aptenex\Upp\Context;

use Aptenex\Upp\Parser\Structure\Condition\DistributionCondition;
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

    public const MODE_NORMAL = 'MODE_NORMAL';
    public const MODE_LOS = 'MODE_LOS';
    public const MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES = 'MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES';

    public static $losModes = [
        self::MODE_LOS,
        self::MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES
    ];

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $mode = self::MODE_NORMAL;

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
     * @Type(type="integer")
     *
     * @var int
     */
    private $pets = null;

    /**
     * @Type(type="string")
     *
     * @var string
     */
    private $distributionChannel = DistributionCondition::CHANNEL_RENTIVO;

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
     * If enabled, this will attempt to generate a price no matter the errors
     *
     * @var bool
     */
    private $forceGeneration = false;

    /**
     * If passed, then it will be used to validate certain parameters
     *
     * @var array
     */
    private $rentalSchemaData;

    /**
     * @var array
     */
    private $configOverride;

    /**
     * @var array
     */
    private $meta;

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
                ->addViolation();
        }

        if (empty($this->getArrivalDate())) {
            $canValidateDates = false;
            $context
                ->buildViolation('The arrival date is required')
                ->atPath('arrivalDate')
                ->addViolation();
        }

        if (empty($this->getDepartureDate())) {
            $canValidateDates = false;
            $context
                ->buildViolation('The departure date is required')
                ->atPath('departureDate')
                ->addViolation();
        }

        if ($canValidateDates) {
            if ($this->getArrivalDateObj()->format("Y-m-d") === $this->getDepartureDateObj()->format("Y-m-d")) {
                $context
                    ->buildViolation('The departure date cannot be on the same day as the arrival date')
                    ->atPath('departureDate')
                    ->addViolation();
            } else if ($this->getDepartureDateObj() <= $this->getArrivalDateObj()) {
                $context
                    ->buildViolation('The departure date cannot be before the arrival date')
                    ->atPath('departureDate')
                    ->addViolation();
            }
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
    public function getPets()
    {
        return $this->pets;
    }

    /**
     * @param int $pets
     */
    public function setPets($pets)
    {
        $this->pets = (int) $pets;
    }

    /**
     * @return bool
     */
    public function hasPets()
    {
        return $this->pets > 0;
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
     * @return bool
     */
    public function isForceGeneration(): bool
    {
        return $this->forceGeneration;
    }

    /**
     * @param bool $forceGeneration
     */
    public function setForceGeneration(bool $forceGeneration)
    {
        $this->forceGeneration = $forceGeneration;
    }

    /**
     * @return array
     */
    public function getRentalSchemaData()
    {
        return $this->rentalSchemaData;
    }

    /**
     * @param array $rentalSchemaData
     */
    public function setRentalSchemaData($rentalSchemaData)
    {
        $this->rentalSchemaData = $rentalSchemaData;
    }

    /**
     * @return bool
     */
    public function hasRentalSchemaData()
    {
        return !empty($this->rentalSchemaData);
    }

    /**
     * @return string
     */
    public function getDistributionChannel()
    {
        return $this->distributionChannel;
    }

    /**
     * @param string $distributionChannel
     */
    public function setDistributionChannel($distributionChannel)
    {
        $this->distributionChannel = $distributionChannel;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return bool
     */
    public function isLosMode(): bool
    {
        return \in_array($this->mode, self::$losModes, true);
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            'currency'            => $this->getCurrency(),
            'mode'                => $this->getMode(),
            'arrivalDate'         => $this->getArrivalDate(),
            'departureDate'       => $this->getDepartureDate(),
            'bookingDate'         => $this->getBookingDate(),
            'guests'              => $this->getGuests(),
            'adults'              => $this->getAdults(),
            'pets'                => $this->getPets(),
            'children'            => $this->getChildren(),
            'infants'             => $this->getInfants(),
            'locale'              => $this->getLocale(),
            'distributionChannel' => $this->getDistributionChannel(),
            'forceGeneration'     => $this->isForceGeneration(),
            'testMode'            => $this->isTestMode(),
            'meta'                => $this->getMeta()
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

                foreach ($value as $dataItem) {
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