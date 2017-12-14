<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Util\ArrayUtils;
use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Symfony\Component\Validator\Constraints\NotBlank;

class CurrencyConfig
{

    /**
     * @NotBlank()
     *
     * @var string
     */
    private $currency;

    /**
     * @var Defaults
     */
    private $defaults;

    /**
     * @var Tax[]
     */
    private $taxes = [];

    /**
     * @var Period[]
     */
    private $periods = [];

    /**
     * @var Modifier[]
     */
    private $modifiers = [];

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
        $this->currency = strtoupper(trim($currency));
    }

    /**
     * @return Period[]
     */
    public function getPeriods()
    {
        return $this->periods;
    }

    /**
     * @param Period $period
     */
    public function addPeriod(Period $period)
    {
        $this->periods[] = $period;
    }

    /**
     * @param Period[] $periods
     * @param bool     $reorder
     */
    public function setPeriods($periods, $reorder = true)
    {
        // For now, we are not letting the user set the priority, we will determine the priority
        // based off if a date range is within a larger date range, meaning the nested range will
        // get priority as it makes sense

        if ($reorder) {
            /** @var Period[] $pp1 */
            $pp1 = ArrayUtils::cloneArray($periods);
            /** @var Period[] $pp2 */
            $pp2 = ArrayUtils::cloneArray($periods);
            foreach ($pp1 as $i1 => $p1) {
                /** @var DateCondition $dc1 */
                $dc1 = $p1->getDateCondition();

                if (is_null($dc1)) {
                    continue;
                }

                foreach ($pp2 as $i2 => $p2) {
                    if ($p1->getId() === $p2->getId()) {
                        continue;
                    }

                    /** @var DateCondition $dc2 */
                    $dc2 = $p2->getDateCondition();
                    if (is_null($dc2)) {
                        continue;
                    }

                    $dc1Start = new \DateTime($dc1->getStartDate());
                    $dc1End = new \DateTime($dc1->getEndDate());
                    $dc2Start = new \DateTime($dc2->getStartDate());
                    $dc2End = new \DateTime($dc2->getEndDate());

                    // Here we will check intersecting dates
                    if ($dc2Start > $dc1Start && $dc1End > $dc2End) {
                        // This is a nested date
                        $periods[$i2]->setPriority($p2->getPriority() + 1);
                    }
                }

            }

            // We need to sort this by the specified priority
            // Higher priority = first
            usort($periods, function ($a, $b) {

                /**
                 * @var $a Period
                 * @var $b Period
                 */

                if ($a->getPriority() > $b->getPriority()) return -1;
                if ($a->getPriority() < $b->getPriority()) return 1;

                return 0;
            });
        }

        $this->periods = $periods;
    }

    /**
     * @return Modifier[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @param Modifier[] $modifiers
     */
    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
    }

    /**
     * @return Defaults
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param Defaults $defaults
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @return Tax[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @param Tax[] $taxes
     */
    public function setTaxes($taxes)
    {
        $this->taxes = $taxes;
    }

}