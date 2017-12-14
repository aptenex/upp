<?php

namespace Aptenex\Upp\Calculation\Pricing\Strategy;

class BracketsEvaluator
{

    /**
     * This method is good for the partial week overcharge, but not good for the discount days
     * as multiple brackets can be matched and these individual conditions will have to be dealt with
     *
     * @param array $brackets
     * @param int $nights
     *
     * @param bool $exactMatch
     * @return number
     */
    public function retrieveValue($brackets, $nights, $exactMatch = false)
    {
        $nights = (int) $nights;

        foreach($brackets as $item) {
            $condition = $item['night'];
            $value = $item['amount'];

            if ($this->matchesCondition($condition, $nights, $exactMatch)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * This will return an array of all matching brackets, but these brackets will also be expanded into
     * individual days if they have any magic attached eg 3-7. This will allow the alteration to be done
     * very easily
     *
     * @param array $brackets
     * @param int $nights
     * @return null|array
     */
    public function retrieveExtraNightsDiscountValues($brackets, $nights)
    {
        $nights = (int) $nights;

        // First we want to expand the brackets into just nights so we can sort them into order (incase they are not)
        $expandedBrackets = $this->expandBrackets($brackets, $nights);

        $matched = [];

        foreach($expandedBrackets as $condition => $value) {
            // Since we know the format of the brackets (very simple) we can do a simple comparison check
            if ($condition <= $nights) {
                $matched[$condition] = $value;
            }
        }

        return $matched;
    }

    /**
     * @param array $brackets
     * @param int $nights
     * @return bool
     */
    public function hasAtLeastOneMatch($brackets, $nights)
    {
        return !is_null($this->retrieveValue($brackets, $nights));
    }

    /**
     * @param string $condition
     * @param int $nights
     * @param bool $exactMatch
     *
     * @return bool
     */
    public function matchesCondition($condition, $nights, $exactMatch = false)
    {
        $nights = (int) $nights;

        if (strpos($condition, '+') !== false) {
            preg_match("/\d*/", $condition, $output);

            // Since this could be eg 7+ and nights=10 if nights is greater to or equal then it matches

            if (count($output) === 1 && $nights >= ((int) $output[0])) {
                return true;
            }
        }

        if (strpos($condition, '-') !== false) {
            preg_match("/(\d+)\-(\d+)/", $condition, $output);

            if (count($output) === 3) {
                $min = (int) $output[1];
                $max = (int) $output[2];
                if ($nights >= $min && $nights <= $max) {
                    return true;
                }
            }
        }

        // If the condition is simply a number like 5 then do the simple check
        if ($exactMatch) {
            if (is_numeric($condition) && (int) $condition === $nights) {
                return true;
            }
        } else {
            if (is_numeric($condition) && (int) $condition <= $nights) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param array $brackets
     * @param int   $nights
     * @param bool  $includeAllData
     *
     * @return array
     */
    public function expandBrackets(array $brackets, $nights, $includeAllData = false)
    {
        $en = [];

        foreach($brackets as $item) {
            $bracket = $item['night'];
            $value = $includeAllData ? $item : $item['amount'];

            if (strpos($bracket, '+') !== false) {
                preg_match("/\d*/", $bracket, $output);

                if (count($output) === 1) {
                    $nightStart = (int) $output[0];
                    // lets expand until the matched nights so we dont go too far
                    for ($i = $nightStart; $i <= $nights; $i++) {
                        $en[$i] = $value;
                    }
                }
            } else if (strpos($bracket, '-') !== false) {
                preg_match("/(\d+)\-(\d+)/", $bracket, $output);

                if (count($output) === 3) {
                    $min = (int) $output[1];
                    $max = (int) $output[2];

                    for ($i = $min; $i <= $max; $i++) {
                        $en[$i] = $value;
                    }
                }
            } else if (is_numeric($bracket)) {
                $en[(int) $bracket] = $value;
            }
        }

        // This will fill the rest of the nights with the highest number
        if (count($en) < $nights) {
            ksort($en);
            $value = array_values($en)[count($en) - 1];
            $highestNum = array_keys($en)[count($en) - 1];
            for ($i = $highestNum; $i <= $nights; $i++) {
                $en[$i] = $value;
            }
        }

        return $en;
    }

}