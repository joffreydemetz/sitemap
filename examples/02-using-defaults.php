<?php

/**
 * Using Defaults Example
 * 
 * This example demonstrates using default values for URL parameters.
 * 
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Sitemap\Map;
use JDZ\Sitemap\Url;

try {
    $publicPath = realpath(__DIR__ . '/public');

    $sitemap = new Map($publicPath, 'defaults', 'https://example.com');

    // Using all defaults:
    // - lastmod: 'now' (current timestamp)
    // - changefreq: Frequency::WEEKLY
    // - priority: 0.5
    $sitemap->addItem(new Url('/page1'));
    $sitemap->addItem(new Url('/page2'));
    $sitemap->addItem(new Url('/page3'));

    // Mix of explicit values and defaults
    $sitemap->addItem(new Url('/page4', '2024-12-01')); // Custom date, other defaults

    $sitemap->write();

    echo "✓ Sitemap with defaults created successfully!\n";
    echo "  Location: {$publicPath}/sitemap/defaults.xml\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
