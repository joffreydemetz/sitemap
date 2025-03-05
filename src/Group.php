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
