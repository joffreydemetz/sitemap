<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap\Tests;

use PHPUnit\Framework\TestCase;
use JDZ\Sitemap\Group;
use JDZ\Sitemap\Exception;

class GroupTest extends TestCase
{
    public function testGroupCreationWithDefaults()
    {
        $group = new Group('https://example.com/sitemap.xml');
        
        $this->assertInstanceOf(Group::class, $group);
    }

    public function testGroupCreationWithCustomDate()
    {
        $group = new Group('https://example.com/sitemap.xml', '2023-01-15 10:00:00');
        
        $this->assertInstanceOf(Group::class, $group);
    }

    public function testToSitemapWithValidUrl()
    {
        $group = new Group('https://example.com/sitemap.xml', '2023-06-15 14:30:00');
        
        $result = $group->toSitemap();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('loc', $result);
        $this->assertArrayHasKey('lastmod', $result);
        
        $this->assertEquals('https://example.com/sitemap.xml', $result['loc']);
    }

    public function testToSitemapWithNow()
    {
        $group = new Group('https://example.com/sitemap.xml', 'now');
        
        $result = $group->toSitemap();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['lastmod']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result['lastmod']);
    }

    public function testInvalidUrl()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The location must be a valid URL');
        
        $group = new Group('not-a-valid-url');
        $group->toSitemap();
    }

    public function testInvalidUrlWithoutProtocol()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The location must be a valid URL');
        
        $group = new Group('example.com/sitemap.xml');
        $group->toSitemap();
    }

    public function testLastmodFormatting()
    {
        $group = new Group('https://example.com/sitemap.xml', '2023-06-15 14:30:00');
        
        $result = $group->toSitemap();
        
        // Check that lastmod is in ISO 8601 format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result['lastmod']);
    }

    public function testDifferentValidUrls()
    {
        $urls = [
            'https://example.com/sitemap.xml',
            'https://sub.example.com/sitemap-posts.xml',
            'http://example.com/sitemap/main.xml',
            'https://example.com:8080/sitemap.xml'
        ];
        
        foreach ($urls as $url) {
            $group = new Group($url);
            $result = $group->toSitemap();
            
            $this->assertEquals($url, $result['loc']);
        }
    }

    public function testLastmodDefaultsToNow()
    {
        $group = new Group('https://example.com/sitemap.xml');
        
        $result = $group->toSitemap();
        
        $this->assertNotEmpty($result['lastmod']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result['lastmod']);
    }
}
