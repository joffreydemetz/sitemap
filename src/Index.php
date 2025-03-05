<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Sitemap;

use JDZ\Sitemap\Writer;

/**
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 * @see    https://www.sitemaps.org/
 */
class Index extends Writer
{
  public function __construct(string $filepath, bool $useIndent=true)
  {
    $this->currentPath = $filepath.'sitemap.xml';
    $this->useIndent = $useIndent;
  }
  
  public function addItem(Group $group): void
  {
    $data = $group->toSitemap();
    
    if ( in_array($data['loc'], $this->writtenUrls) ){
      return;
    }
    
    if ( null === $this->writer ){
      $this->initWriter();
      
      $this->writer->startElement('sitemapindex');
      $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }
    
    $this->writer->startElement('sitemap');
    $this->writer->writeElement('loc', $data['loc']);
    $this->writer->writeElement('lastmod', $data['lastmod']);
    $this->writer->endElement();
    
    $this->writtenUrls[] = $data['loc'];
  }
}
