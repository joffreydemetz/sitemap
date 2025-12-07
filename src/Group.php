<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap;

use JDZ\Sitemap\Exception;

/**
 * @see    https://www.sitemaps.org/
 */
class Group 
{
  private string $loc;
  private string $lastmod;
  
  public function __construct(string $loc, string $lastmod='now')
  {
    $this->loc = $loc;
    $this->lastmod = (new \DateTime($lastmod, new \DateTimeZone('UTC')))->format('c');
  }
  
  public function toSitemap(): array
  {
    if ( false === \filter_var($this->loc, \FILTER_VALIDATE_URL) ){
      throw new Exception('The location must be a valid URL. You have specified: '.$this->loc);
    }
    
    return [
      'loc' => $this->loc,
      'lastmod' => $this->lastmod,
    ];
  }
}
