<?php

/**
 * Dynamic Content from Database Example
 * 
 * This example simulates generating a sitemap from database content
 * (in this case, simulated with arrays).
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

    // Simulate database content
    $blogPosts = [
        ['slug' => 'introduction-to-php', 'updated_at' => '2024-12-01', 'priority' => 0.8],
        ['slug' => 'advanced-patterns', 'updated_at' => '2024-12-03', 'priority' => 0.9],
        ['slug' => 'best-practices', 'updated_at' => '2024-12-05', 'priority' => 0.8],
    ];

    $products = [
        ['id' => 101, 'slug' => 'laptop-pro', 'updated_at' => '2024-12-06', 'in_stock' => true],
        ['id' => 102, 'slug' => 'wireless-mouse', 'updated_at' => '2024-12-04', 'in_stock' => true],
        ['id' => 103, 'slug' => 'keyboard-mechanical', 'updated_at' => '2024-12-02', 'in_stock' => false],
    ];

    $categories = [
        ['slug' => 'electronics', 'priority' => 0.9],
        ['slug' => 'accessories', 'priority' => 0.8],
        ['slug' => 'software', 'priority' => 0.7],
    ];

    // Create sitemap
    $sitemap = new Map($publicPath, 'dynamic', 'https://example.com');

    // Add homepage
    $sitemap->addItem(new Url('/', 'now', Frequency::DAILY, 1.0));

    // Add blog posts
    echo "Adding blog posts from database...\n";
    foreach ($blogPosts as $post) {
        $sitemap->addItem(new Url(
            "/blog/{$post['slug']}",
            $post['updated_at'],
            Frequency::WEEKLY,
            $post['priority']
        ));
    }

    // Add products
    echo "Adding products from database...\n";
    foreach ($products as $product) {
        // Higher priority for in-stock products
        $priority = $product['in_stock'] ? 0.8 : 0.5;

        $sitemap->addItem(new Url(
            "/products/{$product['slug']}",
            $product['updated_at'],
            Frequency::DAILY,
            $priority
        ));
    }

    // Add categories
    echo "Adding categories...\n";
    foreach ($categories as $category) {
        $sitemap->addItem(new Url(
            "/category/{$category['slug']}",
            'now',
            Frequency::WEEKLY,
            $category['priority']
        ));
    }

    $sitemap->write();

    echo "\n✓ Dynamic content sitemap created successfully!\n";
    echo "  Location: {$publicPath}/sitemap/dynamic.xml\n";
    echo "  Content from 'database':\n";
    echo "    - Homepage: 1 URL\n";
    echo "    - Blog posts: " . count($blogPosts) . " URLs\n";
    echo "    - Products: " . count($products) . " URLs\n";
    echo "    - Categories: " . count($categories) . " URLs\n";
    echo "  Total: " . (1 + count($blogPosts) + count($products) + count($categories)) . " URLs\n";

    echo "\nNote: In a real application, you would query your database:\n";
    echo "  \$posts = \$db->query('SELECT slug, updated_at FROM blog_posts WHERE published = 1');\n";
    echo "  \$products = \$db->query('SELECT slug, updated_at, in_stock FROM products');\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
