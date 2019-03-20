<?php

namespace Tests;

use Aptenex\Upp\Util\DateUtils;
use PHPUnit\Framework\TestCase;

class DateUtilsTest extends TestCase
{

    public function testDateChunks()
    {

        $start = new \DateTime('2019-01-01');
        $end = (clone $start)->add(new \DateInterval('P18M'));

        // 6 for 3 month chunks
        $chunks = DateUtils::getDateChunks($start, $end, 6);

        $this->assertCount(6, $chunks);
    }

}