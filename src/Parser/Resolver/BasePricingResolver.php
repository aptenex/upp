<?php

namespace Aptenex\Upp\Parser\Resolver;

use Aptenex\Upp\Util\StringUtils;

abstract class BasePricingResolver implements ResolverInterface
{

    /**
     * @param string $keyWithIdentifier
     *
     * @return array|null
     */
    public function parseAndResolveMixin($keyWithIdentifier)
    {
        return $this->resolveMixin($this->parseMixinKey($keyWithIdentifier));
    }

    /**
     * @param mixed $mixedValue
     * @return bool
     */
    public function isMixin($mixedValue)
    {
        return is_string($mixedValue) && StringUtils::startsWith($mixedValue, $this->getSpecialIdentifier());
    }

    /**
     * @param $keyWithIdentifier
     * @return bool|string
     */
    public function parseMixinKey($keyWithIdentifier)
    {
        if ($this->isMixin($keyWithIdentifier)) {
            return substr($keyWithIdentifier, 1);
        }

        return $keyWithIdentifier;
    }

    /**
     * @return string
     */
    public function getSpecialIdentifier()
    {
        return '@';
    }

}