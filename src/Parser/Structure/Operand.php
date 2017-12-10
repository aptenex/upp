<?php

namespace Aptenex\Upp\Parser\Structure;

class Operand
{

    const OP_AND = 'AND';
    const OP_OR = 'OR';

    const OP_EQUALS = 'equals';
    const OP_ADDITION = 'addition';
    const OP_SUBTRACTION = 'subtraction';
    const OP_NONE = 'none';

    /**
     * @return array
     */
    public static function getConditionalList()
    {
        return [
            self::OP_AND,
            self::OP_OR
        ];
    }

    /**
     * @return array
     */
    public static function getMathList()
    {
        return [
            self::OP_EQUALS,
            self::OP_ADDITION,
            self::OP_SUBTRACTION
        ];
    }

}