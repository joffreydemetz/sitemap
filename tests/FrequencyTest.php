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
    public function testFrequencyEnumExists()
    {
        $this->assertTrue(enum_exists(Frequency::class));
    }

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

    public function testFrequencyFromString()
    {
        $this->assertEquals(Frequency::ALWAYS, Frequency::from('always'));
        $this->assertEquals(Frequency::HOURLY, Frequency::from('hourly'));
        $this->assertEquals(Frequency::DAILY, Frequency::from('daily'));
        $this->assertEquals(Frequency::WEEKLY, Frequency::from('weekly'));
        $this->assertEquals(Frequency::MONTHLY, Frequency::from('monthly'));
        $this->assertEquals(Frequency::YEARLY, Frequency::from('yearly'));
        $this->assertEquals(Frequency::NEVER, Frequency::from('never'));
    }

    public function testFrequencyFromInvalidString()
    {
        $this->expectException(\ValueError::class);
        Frequency::from('invalid');
    }

    public function testFrequencyTryFromString()
    {
        $this->assertEquals(Frequency::DAILY, Frequency::tryFrom('daily'));
        $this->assertNull(Frequency::tryFrom('invalid'));
    }
}
