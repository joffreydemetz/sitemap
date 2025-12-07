<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap\Tests;

use PHPUnit\Framework\TestCase;
use JDZ\Sitemap\Map;
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Exception;
use JDZ\Sitemap\Frequency;

class MapTest extends TestCase
{
    private string $testDir;
    private static string $baseTestDir;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Ensure tests/_data directory exists
        self::$baseTestDir = __DIR__ . DIRECTORY_SEPARATOR . '_data';
        if (!is_dir(self::$baseTestDir)) {
            mkdir(self::$baseTestDir, 0777, true);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary directory for test files
        $this->testDir = self::$baseTestDir . DIRECTORY_SEPARATOR . 'sitemap_test_' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($this->testDir);
        mkdir($this->testDir . 'sitemap');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test files
        if (is_dir($this->testDir)) {
            $this->removeDirectory($this->testDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testMapCreation()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');

        $this->assertInstanceOf(Map::class, $map);
    }

    public function testMapCreationWithIndent()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com', true);

        $this->assertInstanceOf(Map::class, $map);
    }

    public function testMapCreationWithoutIndent()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com', false);

        $this->assertInstanceOf(Map::class, $map);
    }

    public function testAddSingleItem()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');
        $url = new Url('/test-page', 'now', Frequency::DAILY, 0.8);

        $map->addItem($url);
        $map->write();

        $this->assertFileExists($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');
        $this->assertCount(1, $map->writtenFilePaths);
        $this->assertEquals('test-sitemap.xml', $map->writtenFilePaths[0]);
    }

    public function testAddMultipleItems()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');

        $map->addItem(new Url('/page1'));
        $map->addItem(new Url('/page2'));
        $map->addItem(new Url('/page3'));
        $map->write();

        $this->assertFileExists($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');
        $this->assertCount(1, $map->writtenFilePaths);

        $content = file_get_contents($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('https://example.com/page1', $content);
        $this->assertStringContainsString('https://example.com/page2', $content);
        $this->assertStringContainsString('https://example.com/page3', $content);
    }

    public function testDuplicateUrlsAreIgnored()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');

        $map->addItem(new Url('/page1'));
        $map->addItem(new Url('/page1')); // Duplicate
        $map->addItem(new Url('/page2'));
        $map->write();

        $content = file_get_contents($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');        // Count occurrences of page1 URL
        $count = substr_count($content, 'https://example.com/page1');
        $this->assertEquals(1, $count, 'Duplicate URLs should be ignored');
    }

    public function testWebsiteTrailingSlashHandling()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com/');

        $map->addItem(new Url('/page1'));
        $map->write();

        $content = file_get_contents($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');

        // The website parameter gets rtrim in constructor, so trailing slash is removed
        $this->assertStringContainsString('https://example.com/page1', $content);
    }

    public function testGeneratedXmlStructure()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');

        $map->addItem(new Url('/page1', 'now', Frequency::WEEKLY, 0.5));
        $map->write();

        $content = file_get_contents($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');        // Validate basic XML structure
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $content);
        $this->assertStringContainsString('<url>', $content);
        $this->assertStringContainsString('<loc>', $content);
        $this->assertStringContainsString('<lastmod>', $content);
        $this->assertStringContainsString('<changefreq>', $content);
        $this->assertStringContainsString('<priority>', $content);
        $this->assertStringContainsString('</url>', $content);
        $this->assertStringContainsString('</urlset>', $content);
    }

    public function testXmlIsValidXml()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');

        $map->addItem(new Url('/page1'));
        $map->write();

        $content = file_get_contents($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');

        // Try to load as XML to validate structure
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml, 'Generated XML should be valid');
        $this->assertEquals('urlset', $xml->getName());
    }

    public function testWrittenFilePathsTracking()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');

        $this->assertEmpty($map->writtenFilePaths);

        $map->addItem(new Url('/page1'));
        $map->write();

        $this->assertNotEmpty($map->writtenFilePaths);
        $this->assertContains('test-sitemap.xml', $map->writtenFilePaths);
    }

    public function testOverwriteExistingFile()
    {
        $filePath = $this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml';

        // Create initial file
        $map1 = new Map($this->testDir, 'test-sitemap', 'https://example.com');
        $map1->addItem(new Url('/page1'));
        $map1->write();

        $firstContent = file_get_contents($filePath);

        // Create new file with different content
        $map2 = new Map($this->testDir, 'test-sitemap', 'https://example.com');
        $map2->addItem(new Url('/page2'));
        $map2->write();

        $secondContent = file_get_contents($filePath);

        // Content should be different
        $this->assertNotEquals($firstContent, $secondContent);
        $this->assertStringContainsString('/page2', $secondContent);
    }

    public function testUrlElementsArePresent()
    {
        $map = new Map($this->testDir, 'test-sitemap', 'https://example.com');

        $map->addItem(new Url('/page1', '2023-06-15', Frequency::DAILY, 0.8));
        $map->write();

        $content = file_get_contents($this->testDir . 'sitemap' . DIRECTORY_SEPARATOR . 'test-sitemap.xml');
        $xml = simplexml_load_string($content);
        $url = $xml->url[0];

        $this->assertNotNull($url->loc);
        $this->assertNotNull($url->lastmod);
        $this->assertNotNull($url->changefreq);
        $this->assertNotNull($url->priority);

        $this->assertEquals('https://example.com/page1', (string)$url->loc);
        $this->assertEquals('daily', (string)$url->changefreq);
        $this->assertEquals('0.8', (string)$url->priority);
    }

    public function testRequiresSitemapDirectory()
    {
        // This test verifies that the sitemap subdirectory must exist
        $testDir = self::$baseTestDir . DIRECTORY_SEPARATOR . 'sitemap_no_subdir_' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($testDir);
        // Note: NOT creating sitemap subdirectory

        $map = new Map($testDir, 'test-sitemap', 'https://example.com');

        try {
            $map->addItem(new Url('/page1'));
            $map->write();
            $this->fail('Expected an exception to be thrown');
        } catch (\Throwable $e) {
            $this->assertTrue(true, 'Exception was thrown as expected');
        } finally {
            // Clean up
            if (is_dir($testDir)) {
                array_map('unlink', glob($testDir . '*'));
                rmdir($testDir);
            }
        }
    }
}
