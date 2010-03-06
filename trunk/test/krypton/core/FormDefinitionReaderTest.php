<?php
/**
 *	TestUnit of FormDefinitionReader
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_FormDefinitionReader
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.FormDefinitionReader' );
/**
 *	TestUnit of FormDefinitionReader
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_FormDefinitionReader
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class Framework_Krypton_Core_FormDefinitionReaderTest extends PHPUnit_Framework_TestCase
{
	protected $path			= "framework/krypton/core/";
	protected $cachePath	= ""; 

	public function __construct()
	{
		$this->cachePath	= $this->path."cache/forms/";
	}

	public function setUp()
	{
		$this->reader	= new Framework_Krypton_Core_FormDefinitionReader( $this->path );
		$this->reader->setChannel( 'html' );
		$this->reader->setForm( 'addSomething' );
		$this->reader->loadDefinition( 'form' );
	}
	
	public function tearDown()
	{
		if( is_dir( $this->cachePath ) )
		{
			unlink( $this->cachePath."form_html_addSomething.cache" );
			rmDir( $this->cachePath );
			rmDir( dirname( $this->cachePath ) );
		}
	}

	public function testConstruct()
	{
		$reader	= new Framework_Krypton_Core_FormDefinitionReader( $this->path );
		$reader->setChannel( 'html' );
		$reader->setForm( 'addSomething' );
		$reader->loadDefinition( 'form' );
		
		$assertion	= array( 'relation_id', 'title' );
		$creation	= $reader->getFields();
		$this->assertEquals( $assertion, $creation );

#		throw new PHPUnit_Framework_IncompleteTestError( 'Dieser Test ist noch nicht fertig ausprogrammiert.' );
	}


	public function testGetCacheFilename()
	{
	}

	public function testGetField()
	{
		$field = $this->reader->getField( 'relation_id' );

		$assertion	= true;
		$creation	= is_array( $field );
		$this->assertEquals( $assertion, $creation );

		$assertion	= 3;
		$creation	= count( $field );
		$this->assertEquals( $assertion, $creation );

		$assertion	= true;
		$creation	= isset( $field['input'] );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "add_relation_id";
		$creation	= $field['input']['name'];
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetFieldException()
	{
		$this->setExpectedException( "InvalidArgumentException" );
		$this->reader->getField( 'not_registered' );
	}

	public function testGetFieldSyntax()
	{
		$syntax	= $this->reader->getFieldSyntax( 'relation_id' );
		$assertion	= array(
			'class'		=> '',
			'mandatory'	=> 1,
			'maxlength'	=> "",
			'minlength'	=> "",
		);
		$creation = $syntax;
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetFieldSyntaxException()
	{
		$this->setExpectedException( "InvalidArgumentException" );
		$this->reader->getFieldSyntax( 'not_registered' );
	}

	public function testGetFieldInput()
	{
		$syntax	= $this->reader->getFieldInput( 'relation_id' );
		$assertion	= array(
			'name'		=> "add_relation_id",
			'type'		=> "select",
			'style'		=> "l mandatory",
			'validator'	=> "",
			'source'	=> "optionsRelation",
			'options'	=> "",
			'submit'	=> "",
			'disabled'	=> "",
			'hidden'	=> "",
			'tabindex'	=> "",
			'colspan'	=> "",
			'label'		=> "",
			'default'	=> "",
		);
		$creation = $syntax;
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetFieldInputException()
	{
		$this->setExpectedException( "InvalidArgumentException" );
		$this->reader->getFieldInput( 'not_registered' );
	}

	public function testGetFields()
	{
		$assertion	= array(
			'relation_id',
			'title',
		);
		$creation = $this->reader->getFields();
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetFieldSemantics()
	{
		$semantics	= $this->reader->getFieldSemantics( 'relation_id' );
		$assertion	= array(
			array(
				'predicate'	=> "hasValue",
				'edge'		=> "",
			),
			array(
				'predicate'	=> "isGreater",
				'edge'		=> "1",
			),
		);
		$creation = $semantics;
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetFieldSemanticsException()
	{
		$this->setExpectedException( "InvalidArgumentException" );
		$this->reader->getFieldSemantics( 'not_registered' );
	}


	public function testRestlicheMethoden()
	{
		throw new PHPUnit_Framework_IncompleteTestError( 'Dieser Test ist noch nicht fertig ausprogrammiert.' );
	}

	public function testLoadDefinition()
	{
		//  write cache
		$reader	= new Framework_Krypton_Core_FormDefinitionReader( $this->path, true, $this->cachePath );
		$reader->setChannel( 'html' );
		$reader->setForm( 'addSomething' );
		$reader->loadDefinition( 'form' );

		$field	= $reader->getField( 'relation_id' );
		$assertion	= true;
		$creation	= is_array( $field );
		$this->assertEquals( $assertion, $creation );

		$assertion	= 3;
		$creation	= count( $field );
		$this->assertEquals( $assertion, $creation );

		$assertion	= true;
		$creation	= isset( $field['input'] );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "add_relation_id";
		$creation	= $field['input']['name'];
		$this->assertEquals( $assertion, $creation );

		//  read cache		
		$reader	= new Framework_Krypton_Core_FormDefinitionReader( $this->path, true, $this->cachePath );
		$reader->setChannel( 'html' );
		$reader->setForm( 'addSomething' );
		$reader->loadDefinition( 'form' );

		$field	= $reader->getField( 'relation_id' );
		$assertion	= true;
		$creation	= is_array( $field );
		$this->assertEquals( $assertion, $creation );
	}
}


/*
	public function __construct( $path = "", $useCache = false, $cachePath = "cache/", $prefix = "" )
	public function setChannel( $channel )
	public function setPrefix( $prefix )
	public function setForm( $form )
*/
?>