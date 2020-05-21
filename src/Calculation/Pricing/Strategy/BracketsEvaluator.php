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
            if (!\is_array($item)) {
                continue;
            }

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
     * @param int|null $guests
     * @return null|array
     */
    public function retrieveExtraNightsDiscountValues($brackets, $nights, int $guests = null): ?array
    {
        $nights = (int) $nights;

        // First we want to expand the brackets into just nights so we can sort them into order (incase they are not)
        if ($guests === null) {
            $expandedBrackets = $this->expandBrackets($brackets, $nights);
        } else {
            $expandedBrackets = $this->expandBracketsWithGuests($brackets, $nights, $guests);
        }

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
    public function hasAtLeastOneMatch($brackets, $nights): bool
    {
        return $this->retrieveValue($brackets, $nights) !== null;
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
        return $this->matchConditionRaw($condition, $nights, $exactMatch);
    }

    public function matchConditionRaw($condition, $matchField, $exactMatch = false)
    {
        if (strpos($condition, '+') !== false) {
            preg_match("/\d*/", $condition, $output);

            // Since this could be eg 7+ and nights=10 if nights is greater to or equal then it matches

            if (count($output) === 1 && $matchField >= ((int) $output[0])) {
                return true;
            }
        }

        if (strpos($condition, '-') !== false) {
            preg_match("/(\d+)\-(\d+)/", $condition, $output);

            if (count($output) === 3) {
                $min = (int) $output[1];
                $max = (int) $output[2];
                if ($matchField >= $min && $matchField <= $max) {
                    return true;
                }
            }
        }

        // If the condition is simply a number like 5 then do the simple check
        if ($exactMatch) {
            if (is_numeric($condition) && (int) $condition === $matchField) {
                return true;
            }
        } else {
            if (is_numeric($condition) && (int) $condition <= $matchField) {
                return true;
            }
        }
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
        return $this->expandRawBrackets($brackets, 'night', $nights, $includeAllData);
    }

    /**
     *
     *
     * @param array $brackets
     * @param int   $nights
     * @param int   $guests
     *
     * @return array
     */
    public function expandBracketsWithGuests(array $brackets, $nights, $guests)
    {
        $nightsExpanded = $this->expandBrackets($brackets, $nights, true);

        // Now we need to loop through each one of these brackets and then expand the guest brackets
        foreach($nightsExpanded as $night => $item) {
            $defaultAmount = $nightsExpanded[$night]['amount'];

            $expandedGuests = [];
            if (isset($item['guests']) && $item['guests'] !== null && !empty($item['guests'])) {
                $expandedGuests = $this->expandRawBrackets($item['guests'], 'guests', $guests, false);
            }

            $expandedGuests['_default'] = $defaultAmount;
            $nightsExpanded[$night] = $expandedGuests;
        }


        return $nightsExpanded;
    }

    private function expandRawBrackets(array $brackets, string $bracketField, int $fieldMaxCount, $includeAllData = false): array
    {
        $e = [];

        foreach($brackets as $item) {
            $bracket = $item[$bracketField];
            $value = $includeAllData ? $item : $item['amount'];

            if (strpos($bracket, '+') !== false) {
                preg_match("/\d*/", $bracket, $output);

                if (count($output) === 1) {
                    $bracketFieldStart = (int) $output[0];
                    // lets expand until the matched nights so we dont go too far
                    for ($i = $bracketFieldStart; $i <= $fieldMaxCount; $i++) {
                        $e[$i] = $value;
                    }
                }
            } else if (strpos($bracket, '-') !== false) {
                preg_match("/(\d+)\-(\d+)/", $bracket, $output);

                if (count($output) === 3) {
                    $min = (int) $output[1];
                    $max = (int) $output[2];

                    for ($i = $min; $i <= $max; $i++) {
                        $e[$i] = $value;
                    }
                }
            } else if (is_numeric($bracket)) {
                $e[(int) $bracket] = $value;
            }
        }

        if (empty($e)) {
            return [];
        }

        // Sort the array
        ksort($e);

        // This will fill the rest of the nights with the highest number
        if (count($e) < $fieldMaxCount) {
            $value = array_values($e)[count($e) - 1];
            $highestNum = array_keys($e)[count($e) - 1];
            for ($i = $highestNum; $i <= $fieldMaxCount; $i++) {
                $e[$i] = $value;
            }
        }

        return $e;
    }

}