<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap\Tests;

use PHPUnit\Framework\TestCase;
use JDZ\Sitemap\Frequency;

class FrequencyTest extends TestCase
{
    public function testAllFrequencyCases()
    {
        $cases = Frequency::cases();

        $this->assertCount(7, $cases);
        $this->assertContains(Frequency::ALWAYS, $cases);
        $this->assertContains(Frequency::HOURLY, $cases);
        $this->assertContains(Frequency::DAILY, $cases);
        $this->assertContains(Frequency::WEEKLY, $cases);
        $this->assertContains(Frequency::MONTHLY, $cases);
        $this->assertContains(Frequency::YEARLY, $cases);
        $this->assertContains(Frequency::NEVER, $cases);
    }

    public function testFrequencyValues()
    {
        $this->assertEquals('always', Frequency::ALWAYS->value);
        $this->assertEquals('hourly', Frequency::HOURLY->value);
        $this->assertEquals('daily', Frequency::DAILY->value);
        $this->assertEquals('weekly', Frequency::WEEKLY->value);
        $this->assertEquals('monthly', Frequency::MONTHLY->value);
        $this->assertEquals('yearly', Frequency::YEARLY->value);
        $this->assertEquals('never', Frequency::NEVER->value);
    }
}
