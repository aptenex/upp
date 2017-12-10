<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Helper\DateTools;

class Stay
{

    /**
     * @var \DateTime
     */
    private $arrival;

    /**
     * @var \DateTime
     */
    private $departure;

    /**
     * @var Night[]
     */
    private $nights = [];

    /**
     * @var ControlItemInterface[]
     */
    private $periodsUsed = [];

    /**
     * @var ControlItemInterface[]
     */
    private $modifiersUsed = [];

    /**
     * @var \DateTime
     */
    private $bookingDate;

    /**
     * @var int
     */
    private $guests = 0;

    /**
     * @var int
     */
    private $adults = 0;

    /**
     * @var int
     */
    private $children = 0;

    /**
     * @var int
     */
    private $infants = 0;

    /**
     * @var Extra[]
     */
    private $extras = [];

    /**
     * @param PricingContext $context
     */
    public function __construct(PricingContext $context)
    {
        $this->arrival = $context->getArrivalDateObj();
        $this->departure = $context->getDepartureDateObj();
        $this->bookingDate = $context->getBookingDateObj();
        $this->guests = $context->getGuests();
        $this->adults = $context->getAdults();
        $this->children = $context->getChildren();
        $this->infants = $context->getInfants();

        foreach($context->getExtras() as $extra) {
            $this->addExtra(Extra::createFromContextExtra($extra));
        }

        foreach (DateTools::getNightsFromRange($this->arrival, $this->departure) as $date) {
            $this->nights[$date->format("Y-m-d")] = new Night($date, $context->getCurrency());
        }
    }

    /**
     * @return \DateTime
     */
    public function getArrival()
    {
        return $this->arrival;
    }

    /**
     * @return \DateTime
     */
    public function getDeparture()
    {
        return $this->departure;
    }

    /**
     * @return int
     */
    public function getNoNights()
    {
        $interval = $this->getArrival()->diff($this->getDeparture());

        $days = $interval->format("%r%a");

        return (int) $days;
    }

    /**
     * @return Night[]
     */
    public function getNights()
    {
        return $this->nights;
    }

    /**
     * @param \DateTime $date
     *
     * @return Night|null
     */
    public function findDayByDate(\DateTime $date)
    {
        $dateStr = $date->format("Y-m-d");

        if (!array_key_exists($dateStr, $this->nights)) {
            return null;
        }

        return $this->nights[$dateStr];
    }

    /**
     * @return \DateTime
     */
    public function getBookingDate()
    {
        return $this->bookingDate;
    }

    /**
     * @return int
     */
    public function getDaysBeforeArrival()
    {
        $interval = $this->getBookingDate()->diff($this->getArrival());

        $days = $interval->format("%r%a");

        return (int) $days;
    }

    /**
     * @return int
     */
    public function getGuests()
    {
        return $this->guests;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return int
     */
    public function getInfants()
    {
        return $this->infants;
    }

    /**
     * @return ControlItem\ControlItemInterface[]
     */
    public function getPeriodsUsed()
    {
        return $this->periodsUsed;
    }

    /**
     * @param ControlItem\ControlItemInterface[] $periodsUsed
     */
    public function setPeriodsUsed($periodsUsed)
    {
        $this->periodsUsed = $periodsUsed;
    }

    /**
     * @param ControlItemInterface $controlItem
     */
    public function addPeriodsUsed(ControlItemInterface $controlItem)
    {
        // The same control item can only be added once
        $this->periodsUsed[$controlItem->getId()] = $controlItem;
    }

    /**
     * @return ControlItem\ControlItemInterface[]
     */
    public function getModifiersUsed()
    {
        return $this->modifiersUsed;
    }

    /**
     * @param ControlItem\ControlItemInterface[] $modifiersUsed
     */
    public function setModifiersUsed($modifiersUsed)
    {
        $this->modifiersUsed = $modifiersUsed;
    }

    /**
     * @param ControlItemInterface $controlItem
     */
    public function addModifiersUsed(ControlItemInterface $controlItem)
    {
        // The same control item can only be added once
        $this->modifiersUsed[$controlItem->getId()] = $controlItem;
    }

    /**
     * @return Extra[]
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param Extra[] $extras
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;
    }

    /**
     * @param Extra $extra
     */
    public function addExtra(Extra $extra)
    {
        $this->extras[] = $extra;
    }

    /**
     * @return array
     */
    public function getExtrasAsArray()
    {
        $a = [];

        foreach($this->getExtras() as $extra) {
            $a[] = $extra->__toArray();
        }

        return $a;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $data = [
            'arrivalDate'       => $this->getArrival()->format("Y-m-d"),
            'departureDate'     => $this->getDeparture()->format("Y-m-d"),
            'bookingDate'       => $this->getBookingDate()->format("Y-m-d"),
            'noNights'          => $this->getNoNights(),
            'guests'            => $this->getGuests(),
            'adults'            => $this->getAdults(),
            'children'          => $this->getChildren(),
            'infants'           => $this->getInfants(),
            'daysBeforeArrival' => $this->getDaysBeforeArrival(),
            'extras'            => $this->getExtrasAsArray(),
            'nights'            => [],
        ];

        foreach ($this->getNights() as $night) {
            $data['nights'][$night->getDate()->format("Y-m-d")] = $night->__toArray();
        }

        return $data;
    }

}