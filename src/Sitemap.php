<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Sitemap;

use JDZ\Filesystem\File;
use JDZ\Filesystem\Path;

use XMLWriter;
use Exception;
use DateTime;
use DateTimeZone;

/**
 * Generate Sitemaps (http://www.sitemaps.org/)
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Sitemap implements SitemapInterface
{
  const ALWAYS   = 'always';
  const HOURLY   = 'hourly';
  const DAILY    = 'daily';
  const WEEKLY   = 'weekly';
  const MONTHLY  = 'monthly';
  const YEARLY   = 'yearly';
  const NEVER    = 'never';
  
  const DEFAULT_PRIORITY = 0.5;
  
  /**
   * @var   array     Valid values for frequency parameter
   */
  protected $validFrequencies = [
      Sitemap::ALWAYS,
      Sitemap::HOURLY,
      Sitemap::DAILY,
      Sitemap::WEEKLY,
      Sitemap::MONTHLY,
      Sitemap::YEARLY,
      Sitemap::NEVER,
  ];
  
  /**
   * @var   string    Path to the file to be written
   */
  protected $filepath;
  
  /**
   * @var   string    Name of the file to be written
   */
  protected $filename;
  
  /**
   * @var   string    The website base url
   */
  protected $website;

  /**
   * @var   int       Maximum allowed number of URLs in a single file
   */
  protected $maxUrls;

  /**
   * @var   int       Number of URLs to be kept in memory before writing it to file
   */
  protected $bufferSize;

  /**
   * @var   int       Number of URLs added
   */
  protected $urlsCount;

  /**
   * @var   bool      XML should be indented
   */
  protected $useIndent;

  /**
   * @var   XMLWriter   XML writer
   */
  protected $writer;
  
  /**
   * @var   array     Path of files written
   */
  protected $writtenFilePaths;

  /**
   * @var   array     Written urls (to insure uniqueness)
   */
  protected $writtenUrls;

  /**
   * @var   int       Number of files written
   */
  protected $fileCount;

  /**
   * @var   string    Current filepath
   */
  protected $fileCurrent;

  /**
   * Constructor
   * 
   * @throws  Exception
   */
  public function __construct()
  {
    $this->filepath         = null;
    $this->filename         = 'sitemap';
    $this->website          = '';
    $this->maxUrls          = 50000;
    $this->bufferSize       = 1000;
    $this->urlsCount        = 0;
    $this->useIndent        = true;
    $this->writer           = null;
    $this->writtenFilePaths = [];
    $this->writtenUrls      = [];
    $this->fileCount        = 0;
    $this->fileCurrent      = '';
  }
  
  /**
   * {@inheritDoc}
   */
  public function getWrittenFilePaths()
  {
    return $this->writtenFilePaths;
  }
  
  /**
   * {@inheritDoc}
   */
  public function getWrittenUrls()
  {
    return $this->writtenUrls;
  }
  
  /**
   * {@inheritDoc}
   */
  public function getIndex()
  {
    return $this->index;
  }
  
  /**
   * {@inheritDoc}
   */
  public function setFilepath($filepath)
  {
    $filepath = Path::clean($filepath.'/');
    
    if ( !is_dir($filepath) ){
      throw new Exception('Please specify valid file path. Directory not exists. You have specified: '.$filepath);
    }
    
    $this->filepath = $filepath;
  }
  
  /**
   * {@inheritDoc}
   */
  public function setFilename($filename)
  {
    $this->filename = (string)$filename;
  }
  
  /**
   * {@inheritDoc}
   */
  public function setWebsite($website)
  {
    $website = rtrim((string)$website, '/');
    if ( false === filter_var($website, FILTER_VALIDATE_URL) ){
      throw new Exception('The website must be a valid URL. You have specified: '.$website);
    }
    
    $this->website = $website;
  }
  
  /**
   * {@inheritDoc}
   */
  public function setMaxUrls($number)
  {
    $this->maxUrls = (int)$number;
  }
  
  /**
   * {@inheritDoc}
   */
  public function setBufferSize($number)
  {
    $this->bufferSize = (int)$number;
  }
  
  /**
   * {@inheritDoc}
   */
  public function setUseIndent($value)
  {
    $this->useIndent = (bool)$value;
  }

  /**
   * {@inheritDoc}
   */
  public function addItem($location, $changeFrequency=Sitemap::WEEKLY, $priority=Sitemap::DEFAULT_PRIORITY, $lastModified='now')
  {
    $location = ltrim($location, '/');
    $location = $this->website.'/'.$location;
    
    if ( false === filter_var($location, FILTER_VALIDATE_URL) ){
      throw new Exception('The location must be a valid URL. You have specified: '.$location);
    }
    
    if ( in_array($location, $this->writtenUrls) ){
      return;
    }
    
    if ( $this->urlsCount === 0 ){
      $this->createNewFile();
    } 
    elseif ( $this->urlsCount%$this->maxUrls === 0 ){
      $this->finishFile();
      $this->createNewFile();
    }
    elseif ( $this->urlsCount%$this->bufferSize === 0 ){
      $this->flush();
    }
   
    $this->writer->startElement('url');
    
    $this->writer->writeElement('loc', $location);
    
    if ( $lastModified !== null ){
      $now = new DateTime($lastModified, new DateTimeZone('UTC'));
      $this->writer->writeElement('lastmod', $now->format('c'));
    }

    if ( $changeFrequency !== null ){
      if ( !in_array($changeFrequency, $this->validFrequencies, true) ){
        throw new Exception('Please specify valid changeFrequency. Valid values are: '.implode(', ', $this->validFrequencies).' You have specified: '.$changeFrequency);
      }
      
      $this->writer->writeElement('changefreq', $changeFrequency);
    }
    
    if ( $priority !== null && $priority !== Sitemap::DEFAULT_PRIORITY ){
      if ( !is_numeric($priority) || $priority < 0 || $priority > 1 ){
        throw new Exception('Please specify valid priority. Valid values range from 0.0 to 1.0. You have specified: '.$priority);
      }
      $this->writer->writeElement('priority', number_format($priority, 1, '.', ','));
    }
    
    $this->writer->endElement();
    
    $this->writtenUrls[] = $location;
    
    $this->urlsCount++;
  }
  
  /**
   * {@inheritDoc}
   */
  public function write($lastModified='now')
  {
    $this->finishFile();
    
    $this->index = new SitemapIndex();
    $this->index->setFilepath($this->filepath);
    $this->index->setFilename($this->filename);
    $this->index->setWebsite($this->website);
    $this->index->setUseIndent($this->useIndent);
    foreach($this->writtenFilePaths as $file){
      $this->index->addItem(pathinfo($file, PATHINFO_BASENAME), $lastModified);
    }
    $this->index->write();
  }
  
  /**
   * Creates new file 
   *
   * @return  void
   * @throws Exception if file is not writeable
   */
  protected function createNewFile()
  {
    $this->fileCount++;
    
    if ( $this->fileCount > 1 ){
      $this->fileCurrent = $this->filepath.'sitemap/sitemap-'.$this->fileCount.'.xml';
    }
    else {
      $this->fileCurrent = $this->filepath.'sitemap/sitemap.xml';
    }
    
    $this->writtenFilePaths[] = Path::clean($this->fileCurrent);

    if ( file_exists($this->fileCurrent) ){
      $fileCurrent = realpath($this->fileCurrent);
      if ( !is_writable($fileCurrent) ){
        throw new \Exception('File '.$fileCurrent.' is not writable.');
      } 
      unlink($fileCurrent);
    }
    
    $this->writer = new XMLWriter();
    $this->writer->openMemory();
    $this->writer->startDocument('1.0', 'UTF-8');
    $this->writer->setIndent($this->useIndent);
    $this->writer->startElement('urlset');
    $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
  }
  
  /**
   * Writes closing tags to current file
   * 
   * @return  void
   */
  protected function finishFile()
  {
    if ( $this->writer !== null ){
      $this->writer->endElement();
      $this->writer->endDocument();
      $this->flush();
    }
  }
  
  /**
   * Flushes buffer into file
   * 
   * @return  void
   */
  protected function flush()
  {
    $content = $this->writer->flush(true);
    
    try {
      File::write($this->fileCurrent, $content);
    }
    catch(Exception $e){
      throw $e;
    }
  }
}
