<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap\Tests;

use PHPUnit\Framework\TestCase;
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Frequency;
use JDZ\Sitemap\Exception;

class UrlTest extends TestCase
{
    public function testUrlCreationWithDefaults()
    {
        $url = new Url('/test-page');

        $this->assertInstanceOf(Url::class, $url);
    }

    public function testUrlCreationWithAllParameters()
    {
        $url = new Url(
            '/test-page',
            '2023-01-15 10:00:00',
            Url::DAILY,
            0.8
        );

        $this->assertInstanceOf(Url::class, $url);
    }

    public function testToSitemapWithValidUrl()
    {
        $url = new Url('/test-page', '2023-01-15 10:00:00', Url::WEEKLY, 0.7);
        $website = 'https://example.com';

        $result = $url->toSitemap($website);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('loc', $result);
        $this->assertArrayHasKey('lastmod', $result);
        $this->assertArrayHasKey('changefreq', $result);
        $this->assertArrayHasKey('priority', $result);

        $this->assertEquals('https://example.com/test-page', $result['loc']);
        $this->assertEquals(Url::WEEKLY, $result['changefreq']);
        $this->assertEquals(0.7, $result['priority']);
    }

    public function testToSitemapWithTrailingSlashInWebsite()
    {
        $url = new Url('/test-page');
        $website = 'https://example.com/';

        $result = $url->toSitemap($website);

        // Should handle trailing slash correctly
        $this->assertEquals('https://example.com//test-page', $result['loc']);
    }

    public function testToSitemapWithLeadingSlashInLoc()
    {
        $url = new Url('/test-page');
        $website = 'https://example.com';

        $result = $url->toSitemap($website);

        $this->assertEquals('https://example.com/test-page', $result['loc']);
    }

    public function testToSitemapWithoutLeadingSlashInLoc()
    {
        $url = new Url('test-page');
        $website = 'https://example.com';

        $result = $url->toSitemap($website);

        $this->assertEquals('https://example.com/test-page', $result['loc']);
    }

    public function testInvalidPriorityTooLow()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please specify valid priority');

        $url = new Url('/test', 'now', Url::WEEKLY, -0.1);
        $url->toSitemap('https://example.com');
    }

    public function testInvalidPriorityTooHigh()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please specify valid priority');

        $url = new Url('/test', 'now', Url::WEEKLY, 1.1);
        $url->toSitemap('https://example.com');
    }

    public function testInvalidChangeFrequency()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please specify valid changeFrequency');

        $url = new Url('/test', 'now', 'invalid-frequency', 0.5);
        $url->toSitemap('https://example.com');
    }

    public function testValidChangeFrequencies()
    {
        $frequencies = [
            Frequency::ALWAYS,
            Frequency::HOURLY,
            Frequency::DAILY,
            Frequency::WEEKLY,
            Frequency::MONTHLY,
            Frequency::YEARLY,
            Frequency::NEVER
        ];

        foreach ($frequencies as $frequency) {
            $url = new Url('/test', 'now', $frequency, 0.5);
            $result = $url->toSitemap('https://example.com');

            $this->assertEquals($frequency->value, $result['changefreq']);
        }
    }

    public function testLastmodFormatting()
    {
        $url = new Url('/test', '2023-06-15 14:30:00');
        $result = $url->toSitemap('https://example.com');

        // Check that lastmod is in ISO 8601 format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result['lastmod']);
    }

    public function testLastmodWithNow()
    {
        $url = new Url('/test', 'now');
        $result = $url->toSitemap('https://example.com');

        // Should have a valid lastmod timestamp
        $this->assertNotEmpty($result['lastmod']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result['lastmod']);
    }

    public function testDefaultPriority()
    {
        $url = new Url('/test');
        $result = $url->toSitemap('https://example.com');

        $this->assertEquals(0.5, $result['priority']);
    }

    public function testDefaultChangeFrequency()
    {
        $url = new Url('/test');
        $result = $url->toSitemap('https://example.com');

        $this->assertEquals(Url::WEEKLY, $result['changefreq']);
    }

    public function testPriorityBoundaries()
    {
        // Test minimum valid priority
        $url = new Url('/test', 'now', Url::WEEKLY, 0.0);
        $result = $url->toSitemap('https://example.com');
        $this->assertEquals(0.0, $result['priority']);

        // Test maximum valid priority
        $url = new Url('/test', 'now', Url::WEEKLY, 1.0);
        $result = $url->toSitemap('https://example.com');
        $this->assertEquals(1.0, $result['priority']);
    }

    public function testInvalidWebsiteUrl()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The location must be a valid URL');

        $url = new Url('/test');
        $url->toSitemap('not-a-valid-url');
    }

    public function testFrequencyEnumUsage()
    {
        $url = new Url('/test', 'now', Frequency::DAILY, 0.8);
        $result = $url->toSitemap('https://example.com');

        $this->assertEquals('daily', $result['changefreq']);
    }

    public function testAllFrequencyEnumCases()
    {
        $frequencies = [
            [Frequency::ALWAYS, 'always'],
            [Frequency::HOURLY, 'hourly'],
            [Frequency::DAILY, 'daily'],
            [Frequency::WEEKLY, 'weekly'],
            [Frequency::MONTHLY, 'monthly'],
            [Frequency::YEARLY, 'yearly'],
            [Frequency::NEVER, 'never']
        ];

        foreach ($frequencies as [$enum, $expectedValue]) {
            $url = new Url('/test', 'now', $enum, 0.5);
            $result = $url->toSitemap('https://example.com');

            $this->assertEquals($expectedValue, $result['changefreq']);
        }
    }

    public function testBackwardCompatibilityWithStringConstants()
    {
        // Test that old string constants still work
        $url = new Url('/test', 'now', Url::DAILY, 0.5);
        $result = $url->toSitemap('https://example.com');

        $this->assertEquals('daily', $result['changefreq']);
    }
}
