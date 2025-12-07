<?php

/**
 * Multiple Domains Example
 * 
 * This example demonstrates creating sitemaps for multiple domains
 * and combining them into a single sitemap index.
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

    // Create the sitemap index
    $index = new Index($publicPath);

    // Main domain sitemap
    echo "Creating sitemap for main domain (example.com)...\n";
    $mainSitemap = new Map($publicPath, 'example-com', 'https://example.com');
    $mainSitemap->addItem(new Url('/', 'now', Frequency::DAILY, 1.0));
    $mainSitemap->addItem(new Url('/services', 'now', Frequency::WEEKLY, 0.8));
    $mainSitemap->addItem(new Url('/pricing', 'now', Frequency::MONTHLY, 0.8));
    $mainSitemap->write();

    foreach ($mainSitemap->writtenFilePaths as $path) {
        $index->addItem(new Group("https://example.com/sitemap/{$path}", 'now'));
    }

    // Blog subdomain sitemap
    echo "Creating sitemap for blog subdomain (blog.example.com)...\n";
    $blogSitemap = new Map($publicPath, 'blog-example-com', 'https://blog.example.com');
    $blogSitemap->addItem(new Url('/', 'now', Frequency::DAILY, 0.9));
    for ($i = 1; $i <= 50; $i++) {
        $blogSitemap->addItem(new Url("/posts/article-{$i}", 'now', Frequency::WEEKLY, 0.7));
    }
    $blogSitemap->write();

    foreach ($blogSitemap->writtenFilePaths as $path) {
        $index->addItem(new Group("https://example.com/sitemap/{$path}", 'now'));
    }

    // Shop subdomain sitemap
    echo "Creating sitemap for shop subdomain (shop.example.com)...\n";
    $shopSitemap = new Map($publicPath, 'shop-example-com', 'https://shop.example.com');
    $shopSitemap->addItem(new Url('/', 'now', Frequency::DAILY, 0.9));
    $shopSitemap->addItem(new Url('/categories', 'now', Frequency::WEEKLY, 0.8));
    for ($i = 1; $i <= 200; $i++) {
        $shopSitemap->addItem(new Url("/products/item-{$i}", 'now', Frequency::DAILY, 0.7));
    }
    $shopSitemap->write();

    foreach ($shopSitemap->writtenFilePaths as $path) {
        $index->addItem(new Group("https://example.com/sitemap/{$path}", 'now'));
    }

    // Write the index
    $index->write();

    echo "\n✓ Multi-domain sitemap index created successfully!\n";
    echo "  Index: {$publicPath}/sitemap.xml\n";
    echo "  Domains covered:\n";
    echo "    - example.com (3 URLs)\n";
    echo "    - blog.example.com (51 URLs)\n";
    echo "    - shop.example.com (202 URLs)\n";
    echo "  Total: 256 URLs across 3 domains\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
