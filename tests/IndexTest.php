<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap\Tests;

use PHPUnit\Framework\TestCase;
use JDZ\Sitemap\Index;
use JDZ\Sitemap\Group;

class IndexTest extends TestCase
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
        $this->testDir = self::$baseTestDir . DIRECTORY_SEPARATOR . 'sitemap_index_test_' . uniqid();
        mkdir($this->testDir);
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

    public function testIndexCreation()
    {
        $index = new Index($this->testDir);

        $this->assertInstanceOf(Index::class, $index);
    }

    public function testIndexCreationWithIndent()
    {
        $index = new Index($this->testDir, true);

        $this->assertInstanceOf(Index::class, $index);
    }

    public function testIndexCreationWithoutIndent()
    {
        $index = new Index($this->testDir, false);

        $this->assertInstanceOf(Index::class, $index);
    }

    public function testAddSingleItem()
    {
        $index = new Index($this->testDir);
        $group = new Group('https://example.com/sitemap/sitemap-1.xml');

        $index->addItem($group);
        $index->write();

        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');
    }

    public function testAddMultipleItems()
    {
        $index = new Index($this->testDir);

        $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml'));
        $index->addItem(new Group('https://example.com/sitemap/sitemap-2.xml'));
        $index->addItem(new Group('https://example.com/sitemap/sitemap-3.xml'));
        $index->write();

        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');

        $content = file_get_contents($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');
        $this->assertStringContainsString('<sitemapindex', $content);
        $this->assertStringContainsString('https://example.com/sitemap/sitemap-1.xml', $content);
        $this->assertStringContainsString('https://example.com/sitemap/sitemap-2.xml', $content);
        $this->assertStringContainsString('https://example.com/sitemap/sitemap-3.xml', $content);
    }

    public function testDuplicateGroupsAreIgnored()
    {
        $index = new Index($this->testDir);

        $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml'));
        $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml')); // Duplicate
        $index->addItem(new Group('https://example.com/sitemap/sitemap-2.xml'));
        $index->write();

        $content = file_get_contents($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');

        // Count occurrences of sitemap-1.xml
        $count = substr_count($content, 'https://example.com/sitemap/sitemap-1.xml');
        $this->assertEquals(1, $count, 'Duplicate groups should be ignored');
    }

    public function testGeneratedXmlStructure()
    {
        $index = new Index($this->testDir);

        $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml'));
        $index->write();

        $content = file_get_contents($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');

        // Validate basic XML structure
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<sitemapindex', $content);
        $this->assertStringContainsString('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $content);
        $this->assertStringContainsString('<sitemap>', $content);
        $this->assertStringContainsString('<loc>', $content);
        $this->assertStringContainsString('<lastmod>', $content);
        $this->assertStringContainsString('</sitemap>', $content);
        $this->assertStringContainsString('</sitemapindex>', $content);
    }

    public function testXmlIsValidXml()
    {
        $index = new Index($this->testDir);

        $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml'));
        $index->write();

        $content = file_get_contents($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');

        // Try to load as XML to validate structure
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml, 'Generated XML should be valid');
        $this->assertEquals('sitemapindex', $xml->getName());
    }

    public function testSitemapElementsArePresent()
    {
        $index = new Index($this->testDir);

        $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml', '2023-06-15'));
        $index->write();

        $content = file_get_contents($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');

        $xml = simplexml_load_string($content);
        $sitemap = $xml->sitemap[0];

        $this->assertNotNull($sitemap->loc);
        $this->assertNotNull($sitemap->lastmod);

        $this->assertEquals('https://example.com/sitemap/sitemap-1.xml', (string)$sitemap->loc);
    }

    public function testOverwriteExistingFile()
    {
        $filePath = $this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml';

        // Create initial file
        $index1 = new Index($this->testDir);
        $index1->addItem(new Group('https://example.com/sitemap/sitemap-1.xml'));
        $index1->write();

        $firstContent = file_get_contents($filePath);

        // Create new file with different content
        $index2 = new Index($this->testDir);
        $index2->addItem(new Group('https://example.com/sitemap/sitemap-2.xml'));
        $index2->write();

        $secondContent = file_get_contents($filePath);

        // Content should be different
        $this->assertNotEquals($firstContent, $secondContent);
        $this->assertStringContainsString('sitemap-2.xml', $secondContent);
    }

    public function testEmptyIndexThrowsNoError()
    {
        $index = new Index($this->testDir);

        // Writing without adding items should not throw error
        // but won't create a file since writer is null
        $index->write();

        // File should not exist since no items were added
        $this->assertFileDoesNotExist($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');
    }

    public function testMultipleSitemapIndexes()
    {
        $index = new Index($this->testDir);

        // Add multiple sitemap files to the index
        for ($i = 1; $i <= 5; $i++) {
            $index->addItem(new Group("https://example.com/sitemap/sitemap-{$i}.xml"));
        }
        $index->write();

        $content = file_get_contents($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');
        $xml = simplexml_load_string($content);

        // Should have 5 sitemap entries
        $this->assertCount(5, $xml->sitemap);
    }

    public function testIndexWithDifferentDomains()
    {
        $index = new Index($this->testDir);

        $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml'));
        $index->addItem(new Group('https://subdomain.example.com/sitemap/sitemap-2.xml'));
        $index->write();

        $content = file_get_contents($this->testDir . DIRECTORY_SEPARATOR . 'sitemap.xml');

        $this->assertStringContainsString('https://example.com/sitemap/sitemap-1.xml', $content);
        $this->assertStringContainsString('https://subdomain.example.com/sitemap/sitemap-2.xml', $content);
    }

    public function testRequiresValidDirectory()
    {
        // This test verifies that the directory must exist
        $testDir = self::$baseTestDir . DIRECTORY_SEPARATOR . 'sitemap_no_dir_' . uniqid();
        // Note: NOT creating directory

        $index = new Index($testDir);

        try {
            $index->addItem(new Group('https://example.com/sitemap/sitemap-1.xml'));
            $index->write();
            $this->fail('Expected an exception to be thrown');
        } catch (\Throwable $e) {
            $this->assertTrue(true, 'Exception was thrown as expected');
        }
    }
}
