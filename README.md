# JDZ Sitemap

A modern PHP library for generating XML sitemaps and sitemap indexes compliant with the [sitemaps.org protocol](https://www.sitemaps.org/).

## Features

- 🚀 **Easy to use** - Simple and intuitive API
- 📦 **Modern PHP** - Built for PHP 8.1+ with type safety
- 🔒 **Type-safe enums** - Use the `Frequency` enum for change frequency values
- 🎯 **Standards compliant** - Follows sitemaps.org protocol specifications
- 📝 **Automatic handling** - Manages large sitemaps (splits after 40,000 URLs)
- 🔄 **Sitemap index support** - Create sitemap indexes for multiple sitemaps
- ✅ **Fully tested** - Comprehensive test suite

## Requirements

- **PHP**: ^8.1
- **Extensions**: 
  - `ext-simplexml`

## Installation

Install via Composer:

```bash
composer require jdz/sitemap
```

## Quick Start

### Basic Sitemap

```php
<?php

require_once 'vendor/autoload.php';

use JDZ\Sitemap\Map;
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Frequency;

// Create a sitemap
$sitemap = new Map('/path/to/public', 'sitemap', 'https://example.com');

// Add URLs
$sitemap->addItem(new Url('/', 'now', Frequency::DAILY, 0.9));
$sitemap->addItem(new Url('/about', 'now', Frequency::WEEKLY, 0.8));
$sitemap->addItem(new Url('/contact', 'now', Frequency::MONTHLY, 0.7));

// Write sitemap to file
$sitemap->write();
```

This generates: `/path/to/public/sitemap/sitemap.xml`

### Sitemap Index

```php
<?php

require_once 'vendor/autoload.php';

use JDZ\Sitemap\Index;
use JDZ\Sitemap\Map;
use JDZ\Sitemap\Group;
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Frequency;

$publicPath = '/path/to/public';

// Create sitemap index
$index = new Index($publicPath);

// Create main sitemap
$sitemap = new Map($publicPath, 'sitemap', 'https://example.com');
$sitemap->addItem(new Url('/', 'now', Frequency::DAILY, 0.9));
$sitemap->addItem(new Url('/blog', 'now', Frequency::DAILY, 0.8));
$sitemap->write();

// Add to index
foreach ($sitemap->writtenFilePaths as $path) {
    $index->addItem(new Group('https://example.com/sitemap/' . $path));
}

// Create subdomain sitemap
$subSitemap = new Map($publicPath, 'subdomain', 'https://blog.example.com');
$subSitemap->addItem(new Url('/', 'now', Frequency::DAILY, 0.9));
$subSitemap->addItem(new Url('/posts', 'now', Frequency::WEEKLY, 0.8));
$subSitemap->write();

// Add to index
foreach ($subSitemap->writtenFilePaths as $path) {
    $index->addItem(new Group('https://example.com/sitemap/' . $path));
}

// Write index
$index->write();
```

This generates:
- `/path/to/public/sitemap.xml` (index)
- `/path/to/public/sitemap/sitemap.xml`
- `/path/to/public/sitemap/subdomain.xml`

## Frequency Enum Cases

The `Frequency` enum provides type-safe change frequency values:

```php
use JDZ\Sitemap\Frequency;

Frequency::ALWAYS   // 'always'
Frequency::HOURLY   // 'hourly'
Frequency::DAILY    // 'daily'
Frequency::WEEKLY   // 'weekly'
Frequency::MONTHLY  // 'monthly'
Frequency::YEARLY   // 'yearly'
Frequency::NEVER    // 'never'
```

## Examples

See the [examples](examples/) directory for detailed examples:

- `example.php` - Complete usage demonstration with all verbosity levels

### Creating a URL with all parameters

```php
use JDZ\Sitemap\Url;
use JDZ\Sitemap\Frequency;

$url = new Url(
    '/products/item-123',           // Location (path)
    '2023-12-07 10:30:00',         // Last modification date
    Frequency::WEEKLY,              // Change frequency
    0.8                             // Priority (0.0 to 1.0)
);
```

### Using default values

```php
// Only path is required, other parameters have defaults:
// - lastmod: 'now' (current timestamp)
// - changefreq: Frequency::WEEKLY
// - priority: 0.5

$url = new Url('/simple-page');
```

### Handling duplicate URLs

Duplicate URLs are automatically ignored:

```php
$sitemap->addItem(new Url('/page1'));
$sitemap->addItem(new Url('/page1')); // Ignored
$sitemap->addItem(new Url('/page2'));
// Only 2 URLs will be in the sitemap
```

### Automatic file splitting

The library automatically creates multiple sitemap files when you exceed 40,000 URLs:

```php
$sitemap = new Map($publicPath, 'sitemap', 'https://example.com');

for ($i = 1; $i <= 100000; $i++) {
    $sitemap->addItem(new Url("/page-{$i}"));
}

$sitemap->write();

// Generates:
// - sitemap/sitemap.xml (URLs 1-40,000)
// - sitemap/sitemap-2.xml (URLs 40,001-80,000)
// - sitemap/sitemap-3.xml (URLs 80,001-100,000)

// Access generated files
print_r($sitemap->writtenFilePaths);
// Array: ['sitemap.xml', 'sitemap-2.xml', 'sitemap-3.xml']
```

## Testing

Run the test suite:

```bash
# Run all tests
composer test

# Run with coverage
composer test -- --coverage-html coverage

# Run specific test file
vendor/bin/phpunit tests/OutputTest.php
vendor/bin/phpunit tests/VerbosityTest.php

# Run with detailed output
vendor/bin/phpunit --testdox
```

## License

This library is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Changelog

### Version 2.0.0

**Breaking Changes:**
- Minimum PHP version increased to 8.1+
- Added type declarations throughout the codebase

**New Features:**
- ✨ Added `Frequency` enum for type-safe change frequency values
- 📦 Full PHP 8.1+ compatibility with union types and enums
- ✅ Comprehensive test suite

**Improvements:**
- 🎯 Modern PHP practices and strict typing
- 📝 Enhanced error handling with proper exceptions

**Maintenance:**
- 🔧 Updated dependencies
- 📚 Complete README with examples and documentation
- ✨ PSR-4 autoloading for tests

