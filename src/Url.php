<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Sitemap;

use JDZ\Sitemap\Exception;

/**
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 * @see    https://www.sitemaps.org/
 */
class Url 
{
  public const ALWAYS = 'always';
  public const HOURLY = 'hourly';
  public const DAILY = 'daily';
  public const WEEKLY = 'weekly';
  public const MONTHLY = 'monthly';
  public const YEARLY = 'yearly';
  public const NEVER = 'never';
  
  private const DEFAULT_FREQUENCY = self::WEEKLY;
  private const DEFAULT_PRIORITY = 0.5;
  
  private array $validFrequencies = [
      self::ALWAYS,
      self::HOURLY,
      self::DAILY,
      self::WEEKLY,
      self::MONTHLY,
      self::YEARLY,
      self::NEVER,
  ];
  
  private string $loc;
  private string $lastmod;
  private string $changefreq;
  private float $priority;
  
  public function __construct(string $loc, string $lastmod='now', string $changefreq=self::DEFAULT_FREQUENCY, float $priority=self::DEFAULT_PRIORITY)
  {
    $this->loc = $loc;
    $this->lastmod = (new \DateTime($lastmod, new \DateTimeZone('UTC')))->format('c');
    $this->changefreq = $changefreq;
    $this->priority = $priority;
  }
  
  /**
   * @throws  Exception
   */
  public function toSitemap(string $website): array
  {
    $location = \ltrim($this->loc, '/');
    $location = $website.'/'.$location;
    
    if ( false === \filter_var($location, \FILTER_VALIDATE_URL) ){
      throw new Exception('The location must be a valid URL. You have specified: '.$location);
    }
    
    if ( $this->priority < 0 || $this->priority > 1 ){
      throw new Exception('Please specify valid priority. Valid values range from 0.0 to 1.0. You have specified: '.$this->priority);
    }
    
    if ( !in_array($this->changefreq, $this->validFrequencies, true) ){
      throw new Exception('Please specify valid changeFrequency. Valid values are: '.implode(', ', $this->validFrequencies).' You have specified: '.$this->changefreq);
    }
    
    return [
      'loc' => $location,
      'lastmod' => $this->lastmod,
      'changefreq' => $this->changefreq,
      'priority' => $this->priority,
    ];
  }
}
