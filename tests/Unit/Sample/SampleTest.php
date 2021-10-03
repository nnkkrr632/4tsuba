<?php

namespace Tests\Unit\Sample;

use PHPUnit\Framework\TestCase;
use App\Models\Sample;

class SampleTest extends TestCase
{
    public function testAdd()
    {
        $sample = new Sample;
        $sum = $sample->add(5, 3);
        $this->assertEquals(8, $sum);
    }

    /**
     * @test
     */
    public function testMinus()
    {
        $sample = new Sample;
        $sum = $sample->sub(5, 3);
        $this->assertEquals(2, $sum);
    }
}
