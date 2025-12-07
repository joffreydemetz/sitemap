<?php

/**
 * Frequency Enum Usage Example
 * 
 * This example demonstrates all the available frequency enum cases
 * and how to use them with different types of content.
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

    $sitemap = new Map($publicPath, 'frequency-demo', 'https://example.com');

    // ALWAYS - Content that changes with every access
    echo "Adding ALWAYS frequency URLs...\n";
    $sitemap->addItem(new Url('/api/status', 'now', Frequency::ALWAYS, 0.5));

    // HOURLY - Real-time feeds, live data
    echo "Adding HOURLY frequency URLs...\n";
    $sitemap->addItem(new Url('/news/breaking', 'now', Frequency::HOURLY, 0.9));
    $sitemap->addItem(new Url('/stock-prices', 'now', Frequency::HOURLY, 0.8));

    // DAILY - News sites, blogs with daily posts
    echo "Adding DAILY frequency URLs...\n";
    $sitemap->addItem(new Url('/', 'now', Frequency::DAILY, 1.0));
    $sitemap->addItem(new Url('/blog', 'now', Frequency::DAILY, 0.9));
    $sitemap->addItem(new Url('/daily-deals', 'now', Frequency::DAILY, 0.8));

    // WEEKLY - Blog posts, regular updates
    echo "Adding WEEKLY frequency URLs...\n";
    $sitemap->addItem(new Url('/blog/article-1', '2024-12-01', Frequency::WEEKLY, 0.7));
    $sitemap->addItem(new Url('/blog/article-2', '2024-12-07', Frequency::WEEKLY, 0.7));
    $sitemap->addItem(new Url('/events', 'now', Frequency::WEEKLY, 0.8));

    // MONTHLY - Archive pages, less frequent updates
    echo "Adding MONTHLY frequency URLs...\n";
    $sitemap->addItem(new Url('/about', 'now', Frequency::MONTHLY, 0.6));
    $sitemap->addItem(new Url('/team', 'now', Frequency::MONTHLY, 0.5));
    $sitemap->addItem(new Url('/blog/archive/2024/11', 'now', Frequency::MONTHLY, 0.4));

    // YEARLY - Static pages, annual updates
    echo "Adding YEARLY frequency URLs...\n";
    $sitemap->addItem(new Url('/terms-of-service', '2024-01-01', Frequency::YEARLY, 0.3));
    $sitemap->addItem(new Url('/privacy-policy', '2024-01-01', Frequency::YEARLY, 0.3));

    // NEVER - Archived content, permalinks
    echo "Adding NEVER frequency URLs...\n";
    $sitemap->addItem(new Url('/archive/2020/announcement', '2020-06-15', Frequency::NEVER, 0.2));
    $sitemap->addItem(new Url('/archive/2021/old-post', '2021-03-20', Frequency::NEVER, 0.2));

    $sitemap->write();

    echo "\n✓ Frequency demonstration sitemap created successfully!\n";
    echo "  Location: {$publicPath}/sitemap/frequency-demo.xml\n";
    echo "\nFrequency cases used:\n";
    echo "  - ALWAYS:  1 URL  (content changes with every access)\n";
    echo "  - HOURLY:  2 URLs (real-time data)\n";
    echo "  - DAILY:   3 URLs (daily updates)\n";
    echo "  - WEEKLY:  3 URLs (regular updates)\n";
    echo "  - MONTHLY: 3 URLs (less frequent changes)\n";
    echo "  - YEARLY:  2 URLs (annual updates)\n";
    echo "  - NEVER:   2 URLs (archived content)\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
