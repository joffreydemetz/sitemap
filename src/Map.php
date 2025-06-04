<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Sitemap;

use JDZ\Sitemap\Writer;
use JDZ\Sitemap\Exception;

/**
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 * @see    https://www.sitemaps.org/
 */
class Map extends Writer
{
  private const MAX_URL_PER_FILES = 40000;
  private const BUFFER_SIZE = 1000;

  public array $writtenFilePaths = [];

  private string $filepath;
  private string $filename = 'sitemap';
  private string $website = '';
  private int $urlsCount = 0;
  private int $fileCount = 0;

  public function __construct(string $filepath, string $filename, string $website, bool $useIndent = true)
  {
    $this->filepath = $filepath;
    $this->filename = $filename;
    $this->website = \rtrim($website, '/');
    $this->useIndent = $useIndent;
  }

  /**
   * @throws  Exception
   */
  public function addItem(Url $url): void
  {
    $data = $url->toSitemap($this->website);

    if (in_array($data['loc'], $this->writtenUrls)) {
      return;
    }

    // start new file
    if (0 === $this->urlsCount) {
      $this->createNewFile();
    }
    // limit urls per file
    elseif ($this->urlsCount % self::MAX_URL_PER_FILES === 0) {
      $this->closeFile();
      $this->createNewFile();
    }
    // limit the buffer 
    // writes the buffferSize elements already in buffer
    // don't write file every location
    elseif ($this->urlsCount % self::BUFFER_SIZE === 0) {
      $this->writeFile();
    }

    $this->writer->startElement('url');
    $this->writer->writeElement('loc', $data['loc']);
    $this->writer->writeElement('lastmod', $data['lastmod']);
    $this->writer->writeElement('changefreq', $data['changefreq']);
    $this->writer->writeElement('priority', $data['priority']);
    $this->writer->endElement();

    $this->writtenUrls[] = $data['loc'];
    $this->urlsCount++;
  }

  /**
   * @throws Exception if file is not writeable
   */
  private function createNewFile(): void
  {
    $this->fileCount++;

    if ($this->fileCount > 1) {
      $this->currentPath = $this->filepath . 'sitemap/' . $this->filename . '-' . $this->fileCount . '.xml';
      $this->writtenFilePaths[] = $this->filename . '-' . $this->fileCount . '.xml';
    } else {
      $this->currentPath = $this->filepath . 'sitemap/' . $this->filename . '.xml';
      $this->writtenFilePaths[] = $this->filename . '.xml';
    }

    if (\file_exists($this->currentPath)) {
      $fileCurrent = realpath($this->currentPath);
      if (!\is_writable($fileCurrent)) {
        throw new Exception('File ' . $fileCurrent . ' is not writable.');
      }
      unlink($fileCurrent);
    }

    $this->initWriter();

    $this->writer->startElement('urlset');
    $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
  }
}
