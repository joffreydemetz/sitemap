<?php

/**
 * Large Sitemap with Auto-Splitting Example
 * 
 * This example demonstrates how the library automatically splits large sitemaps
 * into multiple files when exceeding 40,000 URLs per file.
 * 
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Sitemap\Map;
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Frequency;

try {
    $publicPath = realpath(__DIR__ . '/public');

    $sitemap = new Map($publicPath, 'products', 'https://example.com');

    echo "Generating large sitemap with 50,000 URLs...\n";

    // Add 50,000 product URLs
    // This will automatically create multiple sitemap files
    for ($i = 1; $i <= 50000; $i++) {
        $sitemap->addItem(new Url(
            "/products/item-{$i}",
            'now',
            Frequency::WEEKLY,
            0.6
        ));

        // Show progress every 10,000 URLs
        if ($i % 10000 === 0) {
            echo "  Added {$i} URLs...\n";
        }
    }

    $sitemap->write();

    echo "\n✓ Large sitemap created successfully!\n";
    echo "  Files generated:\n";
    foreach ($sitemap->writtenFilePaths as $path) {
        echo "    - {$publicPath}/sitemap/{$path}\n";
    }
    echo "  Total URLs: 50,000\n";
    echo "  Note: Automatically split into " . count($sitemap->writtenFilePaths) . " files (max 40,000 URLs per file)\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
