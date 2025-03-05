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
class Writer
{
  protected string $currentPath = '';
  protected bool $useIndent = false;
  protected ?\XMLWriter $writer = null;
  protected array $writtenUrls = [];
  
  public function write(): void
  {
    $this->closeFile();
  }
  
  protected function initWriter()
  {
    $this->writer = new \XMLWriter();
    $this->writer->openMemory();
    $this->writer->startDocument('1.0', 'UTF-8');
    $this->writer->setIndent($this->useIndent);
  }
  
  protected function closeFile(): void
  {
    if ( null !== $this->writer ){
      $this->writer->endElement();
      $this->writer->endDocument();
      $this->writeFile();
    }
  }
  
  /**
   * @throws  Exception
   */
  protected function writeFile(): void
  {
    if ( $this->writer instanceof \XMLWriter ){
      if ( false === ($fp = \fopen($this->currentPath, 'w')) ){
        throw new Exception('Unable to open file ('.$this->currentPath.')');
      }
      
      $content = $this->writer->flush(true);
      
      if ( false === \fwrite($fp, $content) ){
        throw new Exception('Unable to write in file ('.$this->currentPath.')');
      }
      
      \fclose($fp);
    }
  }
}
