<?php

namespace Aptenex\Upp\Parser\Resolver;

interface ResolverInterface
{

    /**
     * @param string $keyWithIdentifier
     *
     * @return array|null
     */
    public function parseAndResolveMixin($keyWithIdentifier);

    /**
     * @param $key
     *
     * @return array|null
     */
    public function resolveMixin($key);

    /**
     * This is called to determine if the value is a mixin - including its special identifier
     *
     * @param mixed $mixedValue
     * @return bool
     */
    public function isMixin($mixedValue);

    /**
     * This will strip out the special identifier from the mixin key allowing it to be retrieved from wherever.
     *
     * @param $key
     * @return string mixed
     */
    public function parseMixinKey($key);

    /**
     * Returns the identifier that prefixes the mixin key
     *
     * @return mixed
     */
    public function getSpecialIdentifier();

}