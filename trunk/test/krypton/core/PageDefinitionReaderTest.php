<?php
/**
 *	TestUnit of PageDefinitionReader
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_PageDefinitionReader
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.PageDefinitionReader' );
/**
 *	TestUnit of PageDefinitionReader
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_PageDefinitionReader
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class Framework_Krypton_Core_PageDefinitionReaderTest extends PHPUnit_Framework_TestCase
{
	protected $fileName	= "framework/krypton/core/pages.xml";
	protected $document;

	public function __construct()
	{
		$this->document	= new DOMDocument();
		$this->document->preserveWhiteSpace	= true;
		$this->document->validateOnParse = true;
		$this->document->load( $this->fileName );
		$this->reader	= new Framework_Krypton_Core_PageDefinitionReader( $this->document );
	}

	public function testConstruct()
	{
		$reader	= new Framework_Krypton_Core_PageDefinitionReader( $this->document );
		$assertion	= array(
			'help',
			'contact',
			'imprint',
		);
		$creation	= array_keys( $reader->getPages( 'foot' ) );
		$this->assertEquals( $assertion, $creation );

		$assertion	= array(
			'scope'		=> "foot",
			'id'		=> "help",
			'default'	=> false,
			'hidden'	=> false,
			'disabled'	=> true,
			'cache'		=> false,
			'type'		=> "static",
			'file'		=> "information/help.html",
			'factory'	=> "",
			'roles'		=> array(
				"public"
			),
		);
		$creation	= array_shift( $reader->getPages( 'foot' ) );
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetPages()
	{
		$assertion	= array(
			'main',
			'foot',
		);
		$creation	= array_keys( $this->reader->getPages() );
		$this->assertEquals( $assertion, $creation );

		$assertion	= array(
			"home",
			"blog",
			"register",

			"login",
			"logout",
			"manage",
			"list",
			"search",
			"shop",
			"catalog",
		);
		$creation	= array_keys( $this->reader->getPages( 'main' ) );
		$this->assertEquals( $assertion, $creation );
	}
}
?>