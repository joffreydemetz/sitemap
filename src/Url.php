<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap;

use JDZ\Sitemap\Exception;
use JDZ\Sitemap\Frequency;

class Url
{
  // Kept for backward compatibility
  public const ALWAYS = 'always';
  public const HOURLY = 'hourly';
  public const DAILY = 'daily';
  public const WEEKLY = 'weekly';
  public const MONTHLY = 'monthly';
  public const YEARLY = 'yearly';
  public const NEVER = 'never';

  private const DEFAULT_PRIORITY = 0.5;

  private string $loc;
  private string $lastmod;
  private Frequency $changefreq;
  private float $priority;

  public function __construct(string $loc, string $lastmod = 'now', Frequency|string $changefreq = Frequency::WEEKLY, float $priority = self::DEFAULT_PRIORITY)
  {
    $this->loc = $loc;
    $this->lastmod = (new \DateTime($lastmod, new \DateTimeZone('UTC')))->format('c');

    if (is_string($changefreq)) {
      try {
        $this->changefreq = Frequency::from($changefreq);
      } catch (\ValueError $e) {
        $validValues = array_map(fn($case) => $case->value, Frequency::cases());
        throw new Exception('Please specify valid changeFrequency. Valid values are: ' . implode(', ', $validValues) . ' You have specified: ' . $changefreq);
      }
    } else {
      $this->changefreq = $changefreq;
    }

    $this->priority = $priority;
  }

  /**
   * @throws  Exception
   */
  public function toSitemap(string $website): array
  {
    $location = \ltrim($this->loc, '/');
    $location = $website . '/' . $location;

    if (false === \filter_var($location, \FILTER_VALIDATE_URL)) {
      throw new Exception('The location must be a valid URL. You have specified: ' . $location);
    }

    if ($this->priority < 0 || $this->priority > 1) {
      throw new Exception('Please specify valid priority. Valid values range from 0.0 to 1.0. You have specified: ' . $this->priority);
    }

    return [
      'loc' => $location,
      'lastmod' => $this->lastmod,
      'changefreq' => $this->changefreq->value,
      'priority' => $this->priority,
    ];
  }
}
