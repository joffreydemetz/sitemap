<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap;

use JDZ\Sitemap\Writer;
use JDZ\Sitemap\Group;
use JDZ\Sitemap\Exception;

/**
 * @see    https://www.sitemaps.org/
 */
class Index extends Writer
{
  public function __construct(string $filepath, bool $useIndent = true)
  {
    $this->currentPath = $filepath . '/sitemap.xml';
    $this->useIndent = $useIndent;
  }

  /**
   * @throws  Exception  if the write has not been initialized
   */
  public function addItem(Group $group): void
  {
    $data = $group->toSitemap();

    if (in_array($data['loc'], $this->writtenUrls)) {
      return;
    }

    if (null === $this->writer) {
      $this->initWriter();

      if (null === $this->writer) {
        throw new Exception('XMLWriter not initialized');
      }

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
