<?php

namespace Aptenex\Upp\Los\Lookup;

use Aptenex\Upp\Los\LosOptions;
use Aptenex\Upp\Exception\CannotGenerateLosException;

class MaximumStayStringLookup implements MaximumStayLookupInterface
{

    /**
     * @var LosOptions
     */
    private $options;

    /**
     * @var array
     */
    private $maximumStayMap;

    /**
     * @param \DateTime $startingDate
     * @param string $maximumStayString
     * @param LosOptions $options
     *
     * @throws CannotGenerateLosException
     */
    public function __construct(\DateTime $startingDate, $maximumStayString, LosOptions $options)
    {
        $this->options = $options;
        $this->parseMaximumStayString($startingDate, $maximumStayString);
    }

    /**
     * @param string $date
     * @return int
     */
    public function getMaximumStay($date): int
    {
        if (!isset($this->maximumStayMap[$date])) {
            return $this->options->getDefaultMaxStay();
        }

        return $this->maximumStayMap[$date];
    }

    /**
     * @param \DateTime $startingDate
     * @param string $maximumStayString
     *
     * @throws CannotGenerateLosException
     */
    private function parseMaximumStayString(\DateTime $startingDate, $maximumStayString): void
    {
        if (empty($maximumStayString)) {
            $this->maximumStayMap = [];

            return;
        }

        $days = explode(',', $maximumStayString);

        $startingDate = clone $startingDate;

        foreach($days as $index => $maximumStay) {
            $this->maximumStayMap[$startingDate->format('Y-m-d')] = (int) $maximumStay;

            try {
                $startingDate = $startingDate->add(new \DateInterval('P1D'));
            } catch (\Exception $e) {
                throw new CannotGenerateLosException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

}