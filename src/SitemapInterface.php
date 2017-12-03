<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Sitemap;

use Exception;

/**
 * Sitemap interface
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
interface SitemapInterface
{
  /**
   * List of sitemap files
   * 
   * @return  array   The list of generated sitemap files
   */
  public function getWrittenFilePaths();
  
  /**
   * List of added urls
   * 
   * @return  array   The list of added urls
   */
  public function getWrittenUrls();
  
  /**
   * Get the created index
   * 
   * @return  SitemapIndex   The Index object
   */
  public function getIndex();
  
  /**
   * Sets the sitemap file path
   * 
   * @param   string    $filepath    The sitemap file path
   * @return  void
   * @throws  Exception
   */
  public function setFilepath($filepath);
  
  /**
   * Sets the sitemap file name
   * 
   * @param   string    $filename    The sitemap file name
   * @return  void
   */
  public function setFilename($filename);
  
  /**
   * Sets the website base url
   * 
   * @param   string    $website    The website base url
   * @return  void
   */
  public function setWebsite($website);
  
  /**
   * Sets maximum number of URLs to write in a single file.
   * Default is 50000.
   * 
   * @param   int       $number     Maximum number of URLS
   * @return  void
   */
  public function setMaxUrls($number);
  
  /**
   * Sets number of URLs to be kept in memory before writing it to file.
   * Default is 1000.
   *
   * @param   int       $number     
   * @return  void
   */
  public function setBufferSize($number);
  
  /**
   * Sets if XML should be indented.
   * Default is true.
   *
   * @param   bool    $value
   * @return  void
   */
  public function setUseIndent($value);

  /**
   * Adds a new item to sitemap
   *
   * @param   string      $location         Location item URL
   * @param   string      $changeFrequency  Change frequency. Use one of Sitemap constants here
   * @param   float       $priority         Item's priority (0.0-1.0). Default null is equal to 0.5
   * @param   mixed       $lastModified     Valid datetime format (Unix timestamp, "now", ..) of sitemap modification time
   * @return  void
   * @throws  Exception
   */
  public function addItem($location, $changeFrequency=Sitemap::WEEKLY, $priority=Sitemap::DEFAULT_PRIORITY, $lastModified='now');
  
  /**
   * Finishes writing
   * 
   * @param   mixed       $lastModified     Valid datetime format (Unix timestamp, "now", ..) of sitemap modification time
   * @return  void
   */
  public function write($lastModified='now');
}
