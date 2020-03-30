<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Aptenex\Upp\Parser\Structure\DaysOfWeek\DayConfig;

class Period extends AbstractControlItem implements ControlItemInterface
{

    /**
     * @var string
     */
    const BOOKABLE_TYPE_DEFAULT = self::BOOKABLE_TYPE_INSTANT_BOOKABLE;

    /**
     * @var string
     */
    const BOOKABLE_TYPE_INSTANT_BOOKABLE = 'instant_bookable';

    /**
     * @var string
     */
    const BOOKABLE_TYPE_ENQUIRY_ONLY = 'enquiry_only';

    /**
     * @var string
     */
    const BOOKABLE_TYPE_ENQUIRY_WITH_PRICE = 'enquiry_with_price';
	
	/**
	 * @var string
	 */
	const BOOKABLE_TYPE_REQUEST_TO_BOOK = 'request_to_book';

    /**
     * @var array
     */
    public static $bookableTypeMap = [
        self::BOOKABLE_TYPE_DEFAULT => null,
        self::BOOKABLE_TYPE_INSTANT_BOOKABLE => 'Instant Bookable',
        self::BOOKABLE_TYPE_ENQUIRY_ONLY => 'Enquiry Only (no price)',
        self::BOOKABLE_TYPE_ENQUIRY_WITH_PRICE => 'Enquiry with Price Shown',
		self::BOOKABLE_TYPE_REQUEST_TO_BOOK => 'Request to book',
    ];

    /**
     * High is highest priority
     *
     * @var array
     */
    public static $bookableTypePriorityMap = [
        self::BOOKABLE_TYPE_INSTANT_BOOKABLE => 1,
        self::BOOKABLE_TYPE_ENQUIRY_ONLY => 3,
        self::BOOKABLE_TYPE_ENQUIRY_WITH_PRICE => 2,
		self::BOOKABLE_TYPE_REQUEST_TO_BOOK => 4
    ];

    /**
     * @var int
     */
    private $minimumNights = null;

    /**
     * @var null|string
     */
    private $bookableType = null;

    /**
     * @return int
     */
    public function getMinimumNights()
    {
        return $this->minimumNights;
    }

    /**
     * @param int $minimumNights
     */
    public function setMinimumNights($minimumNights)
    {
        $this->minimumNights = $minimumNights;
    }

    /**
     * @return bool
     */
    public function hasMinimumNights()
    {
        return !empty($this->getMinimumNights());
    }

    /**
     * @return null|string
     */
    public function getBookableType()
    {
        return $this->bookableType;
    }

    /**
     * @param null|string $bookableType
     */
    public function setBookableType($bookableType)
    {
        $this->bookableType = $bookableType;
    }

    /**
     * @return bool
     */
    public function hasBookableType()
    {
        return !empty($this->bookableType);
    }

    public function getDayOfWeekConfigForStartDate(\DateTime $date): ?DayConfig
    {
        if (!$this->getRate()->hasDaysOfWeek()) {
            return null;
        }

        $daysOfWeek = $this->getRate()->getDaysOfWeek();

        $dayConfig = $daysOfWeek->getDayConfigByDay(\strtolower($date->format('l')));

        if ($dayConfig === null) {
            return null;
        }

        return $dayConfig;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'minimumNights' => $this->getMinimumNights(),
            'bookableType'  => $this->getBookableType()
        ]);
    }

}