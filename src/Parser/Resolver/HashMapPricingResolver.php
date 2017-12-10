<?php

namespace Aptenex\Upp\Parser\Resolver;

use Aptenex\Upp\Util\ArrayUtils;

class HashMapPricingResolver extends BasePricingResolver implements ResolverInterface
{

    /**
     * This HashMap contains a list of mixins to their data values.
     *
     * example-mixin => {}
     *
     * @var array
     */
    private $hashMap;

    /**
     * @param array $hashMap
     */
    public function __construct(array $hashMap)
    {
        $this->hashMap = $hashMap;
    }

    /**
     * @param $key
     * 
     * @return array|null
     */
    public function resolveMixin($key)
    {
        return ArrayUtils::getNestedArrayValue($key, $this->hashMap);
    }

}