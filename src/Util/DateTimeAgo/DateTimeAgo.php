<?php

namespace Aptenex\Upp\Util\DateTimeAgo;

class DateTimeAgo
{

    /**
     * @var TextTranslatorInterface
     */
    protected $textTranslator;

    /**
     * @var integer
     */
    protected $maxDaysCount = 10;

    /**
     * @var string $format
     */
    protected $format = 'Y-m-d H:i:s';

    public function __construct(TextTranslatorInterface $textTranslator = null)
    {
        if (is_null($textTranslator)) {
            $textTranslator = new EnglishTextTranslator();
        }

        $this->textTranslator = $textTranslator;
    }

    /**
     * Get string representation of the date with given translator
     *
     * @param \DateTime      $date
     * @param \DateTime|null $referenceDate
     *
     * @return string
     */
    public function get(\DateTime $date, \DateTime $referenceDate = null)
    {
        if (is_null($referenceDate)) {
            $referenceDate = new \DateTime();
        }

        $diff = $referenceDate->diff($date);

        return $this->getText($diff, $date);
    }

    /**
     * Get string related to \DateInterval object
     *
     * @param \DateInterval $diff
     *
     * @param $date
     * @return string
     */
    public function getText($diff, $date)
    {
        if ($this->now($diff)) {
            return $this->textTranslator->now();
        }

        if ($this->minutes($diff)) {
            return $this->textTranslator->minutes($this->minutes($diff));
        }

        if ($this->hours($diff)) {
            return $this->textTranslator->hours($this->hours($diff));
        }

        if ($this->days($diff)) {
            return $this->textTranslator->days($this->days($diff));
        }

        return $date->format($this->format);
    }

    /**
     * @param \DateInterval $diff
     *
     * @return bool
     */
    public function now($diff)
    {
        if ($this->hourly($diff) && ($diff->h == 0) && ($diff->i == 0) && ($diff->s <= 59)) {
            return true;
        }

        return false;
    }

    /**
     * Is date limit by hour
     *
     * @param \DateInterval $diff
     *
     * @return bool
     */
    public function hourly($diff)
    {
        if ($this->daily($diff) && ($diff->d == 0) && (($diff->h == 0) || (($diff->h == 1) && ($diff->i == 0)))) {
            return true;
        }

        return false;
    }

    /**
     * Is date limit by day
     *
     * @param \DateInterval $diff
     *
     * @return bool
     */
    public function daily($diff)
    {
        if (($diff->y == 0) && ($diff->m == 0) && (($diff->d == 0) || (($diff->d == 1) && ($diff->h == 0) && ($diff->i == 0)))) {
            return true;
        }

        return false;
    }

    /**
     * Number of minutes related to the interval or false if more.
     *
     * @param \DateInterval $diff
     *
     * @return integer|false
     */
    public function minutes($diff)
    {
        if ($this->hourly($diff)) {
            return $diff->i;
        }

        return false;
    }

    /**
     * Number of hours related to the interval or false if more.
     *
     * @param \DateInterval $diff
     *
     * @return integer|false
     */
    public function hours($diff)
    {
        if ($this->daily($diff)) {
            return $diff->h;
        }

        return false;
    }

    /**
     * Number of days related to the interval or false if more.
     *
     * @param \DateInterval $diff
     *
     * @return integer|false
     */
    public function days($diff)
    {
        if ($diff->days <= $this->maxDaysCount) {
            return $diff->days;
        }

        return false;
    }

    public function setTextTranslator($text_translator)
    {
        $this->textTranslator = $text_translator;
    }

    public function setMaxDaysCount($max_days_count)
    {
        $this->maxDaysCount = $max_days_count;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

}