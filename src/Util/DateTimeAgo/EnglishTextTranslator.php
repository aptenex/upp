<?php

namespace Aptenex\Upp\Util\DateTimeAgo;

class EnglishTextTranslator extends AbstractTextTranslator
{

    protected $minuteWords = ['minute ago', 'minutes ago'];
    protected $hourWords   = ['hour ago', 'hours ago'];
    protected $dayWords    = ['day ago', 'days ago'];

    /**
     * {@inheritdoc}
     */
    public function now()
    {
        return 'now';
    }

    /**
     * {@inheritdoc}
     */
    public function pluralization($number)
    {
        return ($number == 1) ? 0 : 1;
    }

}