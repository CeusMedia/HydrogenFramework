<?php
/**
 *	TestUnit of Component
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Component
 *	@uses			Framework_Krypton_Core_Messenger
 *	@uses			Framework_Krypton_Core_Language
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.Component' );
import( 'de.ceus-media.framework.krypton.core.Messenger' );
import( 'de.ceus-media.framework.krypton.core.Language' );
import( 'de.ceus-media.adt.list.Dictionary' );
/**
 *	TestUnit of Component
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Component
 *	@uses			Framework_Krypton_Core_Messenger
 *	@uses			Framework_Krypton_Core_Language
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class Framework_Krypton_Core_ComponentTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->registry		= Framework_Krypton_Core_Registry::getInstance();
		$config		= new ADT_List_Dictionary(
			array(
				'languages.default'	=> "en",
				'languages.allowed'	=> "en,de,fr",
				'paths.cache'		=> "framework/krypton/core/cacheDir/",
				'paths.languages'	=> "framework/krypton/core/languageDir/",
				'paths.html'		=> "framework/krypton/core/htmlDir/",
				'paths.text'		=> "framework/krypton/core/textDir/",
				'paths.test'		=> "framework/krypton/core/testDir/",
				'paths.templates'	=> "framework/krypton/core/templateDir/",
			)
		);

		$session	= new ADT_List_Dictionary();
		$session->set( 'language', "testLanguage" );
		$this->registry->set( 'config', &$config, TRUE );
		$this->registry->set( 'messenger', new Framework_Krypton_Core_Messenger, TRUE );
		$this->registry->set( 'request', new ADT_List_Dictionary, TRUE );
		$this->registry->set( 'session', $session, TRUE );
		$language	= new Framework_Krypton_Core_Language;
		$this->registry->set( 'language', $language, TRUE );
		$this->component	= new TestComponent();
	}


#	public function testIsAll()
#	{
#		throw new PHPUnit_Framework_IncompleteTestError( 'Dieser Test ist noch nicht fertig ausprogrammiert.' );
#	}

	public function testFormatPrice()
	{
		$assertion	= "1.234,50";
		$creation	= $this->component->formatPrice( 1234.5 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "1.234,56";
		$creation	= $this->component->formatPrice( 1234.56 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "1.234,57";
		$creation	= $this->component->formatPrice( 1234.567 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "1.234,57";
		$creation	= $this->component->formatPrice( 1234.5678 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "1.234,00";
		$creation	= $this->component->formatPrice( 1234 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "1.234";
		$creation	= $this->component->formatPrice( 1234, 0 );
		$this->assertEquals( $assertion, $creation );


		$assertion	= "-1.234,50";
		$creation	= $this->component->formatPrice( -1234.5 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "-1.234,56";
		$creation	= $this->component->formatPrice( -1234.56 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "-1.234,57";
		$creation	= $this->component->formatPrice( -1234.567 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "-1.234,57";
		$creation	= $this->component->formatPrice( -1234.5678 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "-1.234,00";
		$creation	= $this->component->formatPrice( -1234 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "-1.234";
		$creation	= $this->component->formatPrice( -1234, 0 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "0";
		$creation	= $this->component->formatPrice( "abc", 0 );
		$this->assertEquals( $assertion, $creation );
		
		try
		{
			$this->component->getPriceFromString( "abc" );
			$his->fail( 'An expected Exception has not been thrown.' );
		}
		catch( Exception $e ) {}
	}

	public function testShortenString()
	{
		$assertion	= "long Text...";
		$creation	= $this->component->shortenString( "long Text with many words", 12 );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "12345";
		$creation	= $this->component->shortenString( "123456", 5, "" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "12345";
		$creation	= $this->component->shortenString( "123456", -5, "" );
		$this->assertEquals( $assertion, $creation );
	}

	public function testPriceFromString()
	{
		$assertion	= 1234.56;
		$creation	= $this->component->getPriceFromString( "1.234,56" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= 1234.56;
		$creation	= $this->component->getPriceFromString( "1 234.56" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= 1234.56;
		$creation	= $this->component->getPriceFromString( "1'234,56" );
		$this->assertEquals( $assertion, $creation );


		$assertion	= 1234;
		$creation	= $this->component->getPriceFromString( "1.234" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= 1234;
		$creation	= $this->component->getPriceFromString( "1 234" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= 1234;
		$creation	= $this->component->getPriceFromString( "1'234" );
		$this->assertEquals( $assertion, $creation );


		$assertion	= -1234.56;
		$creation	= $this->component->getPriceFromString( "-1.234,56" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= -1234.56;
		$creation	= $this->component->getPriceFromString( "-1 234.56" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= -1234.56;
		$creation	= $this->component->getPriceFromString( "-1'234,56" );
		$this->assertEquals( $assertion, $creation );


		$assertion	= -1234;
		$creation	= $this->component->getPriceFromString( "-1.234" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= -1234;
		$creation	= $this->component->getPriceFromString( "-1 234" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= -1234;
		$creation	= $this->component->getPriceFromString( "-1'234" );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testGetCacheUri()
	{
		$assertion	= "framework/krypton/core/cacheDir/test.serial";
		$creation	= $this->component->getCacheUri( "test.serial" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "framework/krypton/core/cacheDir/test/test.serial";
		$creation	= $this->component->getCacheUri( "test/test.serial" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "framework/krypton/core/cacheDir/test.test.serial";
		$creation	= $this->component->getCacheUri( "test.test.serial" );
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetContentUri()
	{
		$assertion	= "framework/krypton/core/htmlDir/testLanguage/test.html";
		$creation	= $this->component->getContentUri( "test.html" );
		$this->assertEquals( $assertion, $creation );

		$this->component->paths['test']	= "test";
		
		$assertion	= "framework/krypton/core/testDir/testLanguage/test.test";
		$creation	= $this->component->getContentUri( "test.test" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "framework/krypton/core/textDir/testLanguage/test.txt";
		$creation	= $this->component->getContentUri( "test.txt" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "framework/krypton/core/htmlDir/testLanguage/test/test.html";
		$creation	= $this->component->getContentUri( "test/test.html" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "framework/krypton/core/htmlDir/testLanguage/test/test.html";
		$creation	= $this->component->getContentUri( "test.test.html" );
		$this->assertEquals( $assertion, $creation );

		try
		{
			$creation	= $this->component->getContentUri( "no_extension" );
			$his->fail( 'An expected Exception has not been thrown.' );
		}
		catch( Exception $e ) {}

		try
		{
			$creation	= $this->component->getContentUri( "unregistered_extension" );
			$his->fail( 'An expected Exception has not been thrown.' );
		}
		catch( Exception $e ) {}

		try
		{
			$this->component->paths['wrong']	= "not_set_in_config";
			$creation	= $this->component->getContentUri( "configuration.is.wrong" );
			$his->fail( 'An expected Exception has not been thrown.' );
		}
		catch( Exception $e ) {}
	}

	public function testHasCache()
	{
		$assertion	= false;
		$creation	= $this->component->hasCache( "cache.serial" );
		$this->assertEquals( $assertion, $creation );

		$config		= $this->registry->get( 'config' );
		$cacheFile	= $config['paths.cache']."test.cache";
		file_put_contents( $cacheFile, time() );
		$assertion	= TRUE;
		$creation	= $this->component->hasCache( "test.cache" );
		@unlink( $cacheFile );
		$this->assertEquals( $assertion, $creation );
	}

	public function testHasContent()
	{
		$assertion	= false;
		$creation	= $this->component->hasContent( "test.html" );
		$this->assertEquals( $assertion, $creation );
		
		$config		= $this->registry->get( 'config' );
		$session	= $this->registry->get( 'session' );
		$config['paths.php']	= "framework/krypton/";
		$this->component->paths['php']	= "php";
		$session->set( 'language', 'core' );

		$assertion	= true;
		$creation	= $this->component->hasContent( "ComponentTest.php" );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testLoadContent()
	{
		$config		= $this->registry->get( 'config' );
		$session	= $this->registry->get( 'session' );
		$messenger	= $this->registry->get( 'messenger' );

		$config['paths.php']	= "framework/krypton/";
		unset( $config['paths.cache'] );
		$this->component->paths['php']	= "php";
		$session->set( 'language', 'core' );
		$assertion	= file_get_contents( __FILE__ );
		$creation	= $this->component->hasContent( "ComponentTest.php" );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testLoadContentException()
	{
		$this->setExpectedException( 'Exception_IO' );

		$config		= $this->registry->get( 'config' );
		$session	= $this->registry->get( 'session' );
		$messenger	= $this->registry->get( 'messenger' );

		$this->component->loadContent( "wrong_path.not_existing.html" );
	}

	public function testLoadTemplate()
	{
		$config		= $this->registry->get( 'config' );		
		$config['paths.templates']	= "framework/krypton/";
		$assertion	= file_get_contents( "framework/krypton/core/messenger.html" );
		$creation	= $this->component->loadTemplate( "core.messenger", array() );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testLoadTemplateException()
	{
		$this->setExpectedException( "Exception_Template" );
		$this->component->loadTemplate( "wrong_path.not_existing", array() );
	}
	
	public function testLoadLanguage()
	{
		$config		= $this->registry->get( 'config' );		
		$session	= $this->registry->get( 'session' );

		$config['paths.cache']		= "";
		$session->set( 'language', 'en' );

		$assertion	= true;
		$creation	= $this->component->loadLanguage( "language" );
		$this->assertEquals( $assertion, $creation );
	}

	public function testLoadLanguageCache()
	{
		$config		= $this->registry->get( 'config' );		
		$session	= $this->registry->get( 'session' );

		$session->set( 'language', 'en' );

		$assertion	= true;
		$creation	= $this->component->loadLanguage( "language" );
		$this->assertEquals( $assertion, $creation );

		$cacheFile	= $config['paths.cache'].basename( $config['paths.languages'] )."/en.language.ser";
		$this->component->loadLanguage( "language" );
		$assertion	= true;
		$creation	= file_exists( $cacheFile );
		@unlink( $cacheFile );
		$this->assertEquals( $assertion, $creation );
	}		
	
	public function testLoadLanguageException()
	{
		$config		= $this->registry->get( 'config' );		
		$config['paths.cache']	= "";
		$this->setExpectedException( "Exception_IO" );
		$this->component->loadLanguage( "not_existing" );
	}
	
	public function testLoadCache()
	{
		$cacheFile	= "framework/krypton/core/cacheDir/test.cache";
		file_put_contents( $cacheFile, "test_load" );
		$assertion	= "test_load";
		$creation	= $this->component->loadCache( "test.cache" );
		@unlink( $cacheFile );
		$this->assertEquals( $assertion, $creation );
	}

	public function testLoadCacheException()
	{
		$this->setExpectedException( "Exception" );
		$this->component->loadCache( "not_existing" );
	}

	public function testSaveCache()
	{
		$cacheFile	= "framework/krypton/core/cacheDir/test.cache";
		$this->component->saveCache( "test.cache", "test_save" );
		$assertion	= "test_save";
		$creation	= file_get_contents( $cacheFile );
		@unlink( $cacheFile );
		$this->assertEquals( $assertion, $creation );
	}

	public function testSaveCacheException()
	{
		$this->setExpectedException( "Exception" );
		$this->component->loadCache( "wrong/path/test.cache" );
	}
/*
	public function __construct( $useWikiParser = false )
	public function cleanseString( $string, $flag = 16, $verbose = false )
	public function handleException( $e, $lanfile, $section )
	protected function handleLogicException( Framework_Krypton_Exception_Logic $e, $filename, $section = "msg" )
	protected function handleValidationException( Framework_Krypton_Exception_Validation $e, $filename, $section )
	protected function handleSqlException( Framework_Krypton_Exception_SQL $e )
	protected function handleTemplateException( Framework_Krypton_Exception_Template $e )
*/
}

class TestComponent extends Framework_Krypton_Core_Component
{
/*
	public function testFormatPrice( $number, $decimals = 2, $separatorDecimals = ",", $separatorThousands = "." )
	{
		return $this->formatPrice( $number, $decimals, $separatorDecimals, $separatorThousands );
	}

	public function testShortenString( $string, $length, $mask = "..." )
	{
		return $this->shortenString( $string, $length, $mask );
	}
*/
}
?>
