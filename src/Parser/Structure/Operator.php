<?php

namespace Aptenex\Upp\Parser\Structure;

class Operator
{

    public const OP_AND = 'AND';
    public const OP_OR = 'OR';

    public const OP_EQUALS = 'equals';
    public const OP_ADDITION = 'addition';
    public const OP_SUBTRACTION = 'subtraction';
    public const OP_NONE = 'none';

    /**
     * @return array
     */
    public static function getConditionalList(): array
    {
        return [
            self::OP_AND,
            self::OP_OR
        ];
    }

    /**
     * @return array
     */
    public static function getMathList(): array
    {
        return [
            self::OP_EQUALS,
            self::OP_ADDITION,
            self::OP_SUBTRACTION
        ];
    }

}