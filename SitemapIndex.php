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
 * A class for generating Sitemap index (http://www.sitemaps.org/)
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class SitemapIndex
{
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
   * @var   bool        XML should be indented
   */
  protected $useIndent;

  /**
   * @var   XMLWriter   XML writer
   */
  protected $writer;

  /**
   * @var   array     Written urls (to insure uniqueness)
   */
  protected $writtenUrls;

  /**
   * Constructor
   * 
   * @param   string      $filepath     Index file path
   */
  public function __construct()
  {
    $this->filepath    = null;
    $this->filename    = 'sitemap';
    $this->website     = '';
    $this->useIndent   = true;
    $this->writer      = null;
    $this->writtenUrls = [];
  }
  
  /**
   * List of added urls
   * 
   * @return  array   The list of added urls
   */
  public function getWrittenUrls()
  {
    return $this->writtenUrls;
  }
  
  /**
   * Sets the sitemap file path
   * 
   * @param   string    $filepath    The sitemap file path
   * @return  void
   * @throws  \InvalidArgumentException
   */
  public function setFilepath($filepath)
  {
    $filepath = Path::clean($filepath.'/');
    
    if ( !is_dir($filepath) ){
      throw new \InvalidArgumentException('Please specify valid file path. Directory not exists. You have specified: '.$filepath);
    }
    
    $this->filepath = $filepath;
  }
  
  /**
   * Sets the sitemap file name
   * 
   * @param   string    $filename    The sitemap file name
   * @return  void
   */
  public function setFilename($filename)
  {
    $this->filename = (string)$filename;
  }
  
  /**
   * Sets the website base url
   * 
   * @param   string    $website    The website base url
   * @return  void
   */
  public function setWebsite($website)
  {
    $this->website = rtrim((string)$website, '/');
  }
  
  /**
   * Sets if XML should be indented.
   * Default is true.
   *
   * @param   bool    $value
   * @return  void
   */
  public function setUseIndent($value)
  {
    $this->useIndent = (bool)$value;
  }
  
  /**
   * Adds sitemap link to the index file
   *
   * @param   string      $location         URL of the sitemap
   * @param   mixed       $lastModified     Valid datetime format (Unix timestamp, "now", ..) of sitemap modification time
   * @return  void
   * @throws  \InvalidArgumentException
   */
  public function addItem($location, $lastModified='now')
  {
    $location = ltrim($location, '/');
    $location = $this->website.'/sitemap/'.$location;
    
    if ( false === filter_var($location, FILTER_VALIDATE_URL) ){
      throw new \InvalidArgumentException('The location must be a valid URL. You have specified: '.$location);
    }
    
    if ( in_array($location, $this->writtenUrls) ){
      return;
    }
    
    if ( $this->writer === null ){
      $this->writer = new XMLWriter();
      $this->writer->openMemory();
      $this->writer->startDocument('1.0', 'UTF-8');
      $this->writer->setIndent($this->useIndent);
      $this->writer->startElement('sitemapindex');
      $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }
    
    $this->writer->startElement('sitemap');
    $this->writer->writeElement('loc', $location);
    
    if ( $lastModified !== null ){
      $now = new DateTime($lastModified, new DateTimeZone('UTC'));
      $this->writer->writeElement('lastmod', $now->format('c'));
    }
    
    $this->writer->endElement();
    
    $this->writtenUrls[] = $location;
  }

  /**
   * Finishes writing
   * 
   * @return  void
   */
  public function write()
  {
    if ( $this->writer instanceof XMLWriter ){
      $this->writer->endElement();
      $this->writer->endDocument();
      
      $filepath = $this->filepath.$this->filename.'.xml';
      
      $content = $this->writer->flush(true);
    
      try {
        File::write($filepath, $content);
      }
      catch(Exception $e){
        throw $e;
      }
    }
  }
}
