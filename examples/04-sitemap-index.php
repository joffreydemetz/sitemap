<?php

/**
 * Sitemap Index Example
 * 
 * This example shows how to create a sitemap index that references
 * multiple sitemap files (e.g., for different sections of a website).
 * 
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Sitemap\Index;
use JDZ\Sitemap\Map;
use JDZ\Sitemap\Group;
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Frequency;

try {
    $publicPath = realpath(__DIR__ . '/public');
    $baseUrl = 'https://example.com';

    // Create the sitemap index
    $index = new Index($publicPath);

    // Create sitemap for main pages
    echo "Creating main pages sitemap...\n";
    $mainSitemap = new Map($publicPath, 'main', $baseUrl);
    $mainSitemap->addItem(new Url('/', 'now', Frequency::DAILY, 1.0));
    $mainSitemap->addItem(new Url('/about', 'now', Frequency::MONTHLY, 0.8));
    $mainSitemap->addItem(new Url('/contact', 'now', Frequency::MONTHLY, 0.7));
    $mainSitemap->write();

    // Add main sitemap to index
    foreach ($mainSitemap->writtenFilePaths as $path) {
        $index->addItem(new Group("{$baseUrl}/sitemap/{$path}", 'now'));
    }

    // Create sitemap for blog
    echo "Creating blog sitemap...\n";
    $blogSitemap = new Map($publicPath, 'blog', $baseUrl);
    for ($i = 1; $i <= 100; $i++) {
        $blogSitemap->addItem(new Url("/blog/post-{$i}", 'now', Frequency::WEEKLY, 0.8));
    }
    $blogSitemap->write();

    // Add blog sitemap to index
    foreach ($blogSitemap->writtenFilePaths as $path) {
        $index->addItem(new Group("{$baseUrl}/sitemap/{$path}", 'now'));
    }

    // Create sitemap for products
    echo "Creating products sitemap...\n";
    $productsSitemap = new Map($publicPath, 'products', $baseUrl);
    for ($i = 1; $i <= 500; $i++) {
        $productsSitemap->addItem(new Url("/products/item-{$i}", 'now', Frequency::DAILY, 0.7));
    }
    $productsSitemap->write();

    // Add products sitemap to index
    foreach ($productsSitemap->writtenFilePaths as $path) {
        $index->addItem(new Group("{$baseUrl}/sitemap/{$path}", 'now'));
    }

    // Write the index file
    $index->write();

    echo "\n✓ Sitemap index created successfully!\n";
    echo "  Index: {$publicPath}/sitemap.xml\n";
    echo "  Sitemaps referenced:\n";
    echo "    - main.xml (3 URLs)\n";
    echo "    - blog.xml (100 URLs)\n";
    echo "    - products.xml (500 URLs)\n";
    echo "  Total: 603 URLs across 3 sitemaps\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
