<?php

/**
 * Basic Sitemap Example
 * 
 * This example shows how to create a simple sitemap with a few URLs.
 * 
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Sitemap\Map;
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Frequency;

try {
    // Define the output directory
    $publicPath = realpath(__DIR__ . '/public');

    // Create a new sitemap
    $sitemap = new Map($publicPath, 'sitemap', 'https://example.com');

    // Add homepage with high priority
    $sitemap->addItem(new Url('/', 'now', Frequency::DAILY, 1.0));

    // Add about page
    $sitemap->addItem(new Url('/about', 'now', Frequency::MONTHLY, 0.8));

    // Add contact page
    $sitemap->addItem(new Url('/contact', 'now', Frequency::MONTHLY, 0.7));

    // Add blog pages
    $sitemap->addItem(new Url('/blog', 'now', Frequency::DAILY, 0.9));
    $sitemap->addItem(new Url('/blog/getting-started', '2024-12-01', Frequency::WEEKLY, 0.8));
    $sitemap->addItem(new Url('/blog/advanced-tips', '2024-12-05', Frequency::WEEKLY, 0.8));

    // Write the sitemap file
    $sitemap->write();

    echo "✓ Basic sitemap created successfully!\n";
    echo "  Location: {$publicPath}/sitemap/sitemap.xml\n";
    echo "  URLs added: " . count($sitemap->getWrittenUrls()) . "\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
