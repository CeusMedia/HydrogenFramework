<?php
/**
 *	TestUnit of Model
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Registry
 *	@uses			ADT_List_Dictionary
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.Registry' );
import( 'de.ceus-media.adt.list.Dictionary' );
/**
 *	TestUnit of Model
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Registry
 *	@uses			ADT_List_Dictionary
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class Framework_Krypton_Core_RegistryTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->array	= array( 1, 2, 3 );
		$this->object	= new ADT_List_Dictionary();
		$this->object->set( 'key1', 'value1' );
		$this->registry	= Framework_Krypton_Core_Registry::getInstance();
		$this->registry->set( 'array', $this->array, true );
		$this->registry->set( 'object', $this->object, true );
	}

	public function testGet()
	{
		$assertion	= $this->array;
		$creation	= $this->registry->get( 'array' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= $this->object;
		$creation	= $this->registry->get( 'object' );
		$this->assertEquals( $assertion, $creation );

		$array		= $this->registry->get( 'array' );
		$array[]	= 4;
		$assertion	= $this->array;
		$creation	= $this->registry->get( 'array' );
		$this->assertEquals( $assertion, $creation );

		$array		=& $this->registry->get( 'array' );
		$array[]	= 4;
		$assertion	= $array;
		$creation	= $this->registry->get( 'array' );
		$this->assertEquals( $assertion, $creation );

		$object		= $this->registry->get( 'object' );
		$object->set( 'key2', 'value2' );		
		$assertion	= $object;
		$creation	= $this->registry->get( 'object' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= $this->object;
		$creation	= $this->registry->get( 'object' );
		$this->assertEquals( $assertion, $creation );
	}

	public function testGetException()
	{
		$this->setExpectedException( "InvalidArgumentException" );
		$this->registry->get( 'not_registered' );
	}

	public function testGetStatic()
	{
		$assertion	= $this->array;
		$creation	= Framework_Krypton_Core_Registry::getStatic( 'array' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= $this->object;
		$creation	= Framework_Krypton_Core_Registry::getStatic( 'object' );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testHas()
	{
		$assertion	= true;
		$creation	= $this->registry->has( 'array' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= true;
		$creation	= $this->registry->has( 'object' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= false;
		$creation	= $this->registry->has( 'not_registered' );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testSet()
	{
		$doc	= new DOMDocument( "1.0" );
		$node	= $doc->createElement( "tag1", "content1" );
		$this->registry->set( 'node', $node );
		
		$assertion	= true;
		$creation	= $this->registry->has( 'node' );
		$this->assertEquals( $assertion, $creation );
		
		$assertion	= $node;
		$creation	= $this->registry->get( 'node' );
		$this->assertEquals( $assertion, $creation );

		$node->setAttribute( 'changed', 1 );
		$assertion	= $node;
		$creation	= $this->registry->get( 'node' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= 1;
		$creation	= $this->registry->get( 'node' )->getAttribute( 'changed' );
		$this->assertEquals( $assertion, $creation );


		$doc	= new DOMDocument( "1.0" );
		$node	= $doc->createElement( "tag2", "content2" );
#		$node	= new DOMElement( "tag2", "content2" );

		$this->registry->set( 'node', $node, true );
		$assertion	= $node;
		$creation	= $this->registry->get( 'node' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= "tag2";
		$creation	= $this->registry->get( 'node' )->nodeName;
		$this->assertEquals( $assertion, $creation );
	}
	
	public function setSetException()
	{
	
		$bool	= false;
		$this->registry->set( 'bool', $bool );

		$this->setExpectedException( "RuntimeException" );
		$this->registry->set( 'bool', $bool );
	}

}
?>