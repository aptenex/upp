<?php

namespace Los\Lookup;

use Los\LosOptions;
use Aptenex\Upp\Exception\CannotGenerateLosException;

class MinimumStayStringLookup implements MinimumStayLookupInterface
{

    /**
     * @var LosOptions
     */
    private $options;

    /**
     * @var array
     */
    private $minimumStayMap;

    /**
     * @param \DateTime $startingDate
     * @param string $minimumStayString
     * @param LosOptions $options
     *
     * @throws CannotGenerateLosException
     */
    public function __construct(\DateTime $startingDate, string $minimumStayString, LosOptions $options)
    {
        $this->options = $options;
        $this->minimumStayMap = $this->parseMinimumStayString($startingDate, $minimumStayString);
    }

    /**
     * @param string $date
     * @return int
     */
    public function getMinimumStay($date): int
    {
        if (!isset($this->minimumStayMap[$date])) {
            return $this->options->getDefaultMinStay();
        }

        return $this->minimumStayMap[$date];
    }

    /**
     * @param \DateTime $startingDate
     * @param string $minimumStayString
     *
     * @throws CannotGenerateLosException
     */
    private function parseMinimumStayString(\DateTime $startingDate, string $minimumStayString)
    {
        if (empty($minimumStayString)) {
            return;
        }

        $days = explode(',', $minimumStayString);

        foreach($days as $index => $minimumStay) {
            $this->minimumStayMap[$startingDate->format('Y-m-d')] = (int) $minimumStay;

            try {
                $startingDate = $startingDate->add(new \DateInterval('P1D'));
            } catch (\Exception $e) {
                throw new CannotGenerateLosException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

}