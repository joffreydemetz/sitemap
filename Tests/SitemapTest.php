<?php
/**
 * THIS SOFTWARE IS PRIVATE
 * CONTACT US FOR MORE INFORMATION
 * Joffrey Demetz <joffrey.demetz@gmail.com>
 * <http://joffreydemetz.com>
 */
namespace JDZ\Sitemap;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class SitemapTest extends TestCase
{
  private $umask;
  
  /**
   * The directory to test file creation
   * 
   * @var string
   */
  protected $workspace = null;

  /**
   * The Symfony Filesystem Component instance
   * 
   * @var Filesystem
   */
  protected $filesystem = null;
  
  /**
   * Setup some vars for the process
   */
  protected function setUp()
  {
    $this->umask = umask(0);
    
    $this->filesystem = new Filesystem();
    
    $this->workspace = sys_get_temp_dir().'/'.microtime(true).'.'.mt_rand();
    mkdir($this->workspace, 0777, true);
    $this->workspace = realpath($this->workspace);
  }
  
  /** 
   * Remove created folder & files and reset original umask
   */
  protected function tearDown()
  {
    $this->filesystem->remove($this->workspace);
    
    umask($this->umask);
  }

  /**
   * Test simple index creation
   */
  public function testWritingFile()
  {
    $sitemap = new Sitemap();
    $sitemap->setFilepath(__DIR__);
    $sitemap->setFilename('sitemap');
    $sitemap->setWebsite('http://example.com');
    $sitemap->addItem('/mylink1');
    $sitemap->addItem('/mylink2/path/long/as/hell');
    $sitemap->addItem('/mylink3', Sitemap::HOURLY);
    $sitemap->addItem('/mylink4', Sitemap::DAILY, 0.3);
    $sitemap->write();
    
    $expectedSitemapFile = __DIR__ . '/sitemap/sitemap.xml';
    $this->assertTrue(file_exists($expectedSitemapFile), "$expectedSitemapFile does not exist!");
    $this->assertIsValidSitemap($expectedSitemapFile);
    unlink($expectedSitemapFile);
    
    $expectedIndexFile = __DIR__ . '/sitemap.xml';
    $this->assertTrue(file_exists($expectedIndexFile), "$expectedIndexFile does not exist!");
    $this->assertIsValidIndex($expectedIndexFile);
    unlink($expectedIndexFile);
    
    $urls = $sitemap->getWrittenUrls();
    $this->assertEquals(4, count($urls));
    $this->assertContains('http://example.com/mylink1', $urls);
    $this->assertContains('http://example.com/mylink2/path/long/as/hell', $urls);
    $this->assertContains('http://example.com/mylink3', $urls);
    $this->assertContains('http://example.com/mylink4', $urls);
  }
  
  /**
   * Test multiple files index creation
   */
  public function testMultipleFiles()
  {
    $sitemap = new Sitemap();
    $sitemap->setFilepath(__DIR__);
    $sitemap->setFilename('sitemap');
    $sitemap->setWebsite('http://example.com');
    $sitemap->setMaxUrls(2);
    
    for($i=0; $i<20; $i++) {
      $sitemap->addItem('http://example.com/mylink'.$i);
    }
    $sitemap->write();
    
    $expectedSitemapFiles = array(
      __DIR__ . '/sitemap/sitemap.xml',
      __DIR__ . '/sitemap/sitemap-2.xml',
      __DIR__ . '/sitemap/sitemap-3.xml',
      __DIR__ . '/sitemap/sitemap-4.xml',
      __DIR__ . '/sitemap/sitemap-5.xml',
      __DIR__ . '/sitemap/sitemap-6.xml',
      __DIR__ . '/sitemap/sitemap-7.xml',
      __DIR__ . '/sitemap/sitemap-8.xml',
      __DIR__ . '/sitemap/sitemap-9.xml',
      __DIR__ . '/sitemap/sitemap-10.xml',
    );
    
    foreach($expectedSitemapFiles as $expectedSitemapFile) {
      $this->assertTrue(file_exists($expectedSitemapFile), "$expectedSitemapFile does not exist!");
      $this->assertIsValidSitemap($expectedSitemapFile);
      unlink($expectedSitemapFile);
    }
    
    $files = $sitemap->getWrittenFilePaths();
    $this->assertEquals(10, count($files));
    
    $urls  = $sitemap->getIndex()->getWrittenUrls();
    $this->assertEquals(10, count($urls));
    $this->assertContains('http://example.com/sitemap/sitemap.xml', $urls);
    $this->assertContains('http://example.com/sitemap/sitemap-4.xml', $urls);
    $this->assertContains('http://example.com/sitemap/sitemap-10.xml', $urls);
    
    $expectedIndexFile = __DIR__ . '/sitemap.xml';
    $this->assertTrue(file_exists($expectedIndexFile), "$expectedIndexFile does not exist!");
    $this->assertIsValidIndex($expectedIndexFile);
    unlink($expectedIndexFile);
  }
  
  /**
   * Test frequency validation
   */
  public function testFrequencyValidation()
  {
    $this->setExpectedException('Exception');

    $sitemap = new Sitemap();
    $sitemap->setFilepath(__DIR__);
    $sitemap->setFilename('sitemap');
    $sitemap->setWebsite('http://example.com');
    
    $sitemap->addItem('/mylink1');
    $sitemap->addItem('/mylink2', 'invalid');
  }

  /**
   * Test priority validation
   */
  public function testPriorityValidation()
  {
    $sitemap = new Sitemap();
    $sitemap->setFilepath(__DIR__);
    $sitemap->setFilename('sitemap');
    $sitemap->setWebsite('http://example.com');
    
    $exceptionCaught = false;
    try {
      $sitemap->addItem('/mylink1');
      $sitemap->addItem('/mylink2', 'always', 2.0);
    } catch (\Exception $e) {
      $exceptionCaught = true;
    }
    
    $this->assertTrue($exceptionCaught, 'No priority validation error.');
  }
  
  /**
   * Test location validation
   */
  public function testLocationValidation()
  {
    $sitemap = new Sitemap();
    $sitemap->setFilepath(__DIR__);
    $sitemap->setFilename('sitemap');
    $sitemap->setWebsite('http://example.com');

    $exceptionCaught = false;
    try {
      $sitemap->addItem('/mylink1');
      $sitemap->addItem('!?;not link-- )àé")');
    } catch (\Exception $e) {
      $exceptionCaught = true;
    }
    
    $this->assertTrue($exceptionCaught, 'No location validation error.');
  }
  
  /**
   * Test website validation
   */
  public function testWebsiteValidation()
  {
    $sitemap = new Sitemap();
    
    $exceptionCaught = false;
    try {
      $sitemap->setWebsite('toto');
    } catch (\Exception $e){
      $exceptionCaught = true;
    }
    
    $this->assertTrue($exceptionCaught, 'No website error caught.');
  }
  
  /**
   * Test filepath validation
   */
  public function testFilepathValidation()
  {
    $sitemap = new Sitemap();
    
    $exceptionCaught = false;
    try {
      $sitemap->setFilepath('toto');
    } catch (\Exception $e){
      $exceptionCaught = true;
    }
    
    $this->assertTrue($exceptionCaught, 'No filepath error caught.');
  }
  
  /**
   * Asserts validity of sitemap according to XSD schema
   * 
   * @param   string $filename
   * @return  void
   */
  protected function assertIsValidSitemap($filename)
  {
    $xml = new \DOMDocument();
    $xml->load($filename);
    $this->assertTrue($xml->schemaValidate(__DIR__ . '/sitemap.xsd'));
  }

  /**
   * Asserts validity of sitemap index according to XSD schema
   * 
   * @param   string $filename
   * @return  void
   */
  protected function assertIsValidIndex($filename)
  {
    $xml = new \DOMDocument();
    $xml->load($filename);
    $this->assertTrue($xml->schemaValidate(__DIR__ . '/siteindex.xsd'));
  }  
}
