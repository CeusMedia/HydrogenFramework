<?php
/**
 *	TestUnit of PageDefinitionEditor
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_PageDefinitionEditor
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.PageDefinitionEditor' );
/**
 *	TestUnit of PageDefinitionEditor
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_PageDefinitionEditor
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class Framework_Krypton_Core_PageDefinitionEditorTest extends PHPUnit_Framework_TestCase
{
	protected $fileName	= "framework/krypton/core/pages_editor.xml";
	
	private function findRoleForPage( $roleName, $pageId )
	{
		$document	= new DOMDocument();
		$document->preserveWhiteSpace	= true;
		$document->validateOnParse = true;
		$document->load( $this->fileName );
		$node	= $document->getElementById( $pageId );
		$node	= $node->getElementsByTagName( 'roles' )->item( 0 );

		$list	= array();
		foreach( $node->childNodes as $child )
			if( $child->nodeType == 1 )
				$list[]	= $child->nodeName;
		
		return in_array( $roleName, $list );
	}

	public function setUp()
	{
		copy( dirname( $this->fileName )."/pages.xml", $this->fileName );
		$this->editor	= new Editor( $this->fileName );
	}

	public function tearDown()
	{
		unlink( $this->fileName );
	}

	public function testConstruct()
	{
		$editor		= new Editor( $this->fileName );
		$assertion	= $this->fileName;
		$creation	= $editor->getFileName();
		$this->assertEquals( $assertion, $creation );
	}

	public function testAddRoleToPage()
	{
		$this->editor->addRoleToPage( "outside", "help" );

		$assertion	= true;
		$creation	= $this->findRoleForPage( "outside", "help" );
		$this->assertEquals( $assertion, $creation );
	}

	public function testDisablePage()
	{
		$assertion	= true;
		$creation	= $this->editor->disablePage( 'contact' );
		$this->assertEquals( $assertion, $creation );

		$document	= new DOMDocument();
		$document->preserveWhiteSpace	= true;
		$document->validateOnParse = true;
		$document->load( $this->fileName );
		
		$assertion	= 1;
		$creation	= $document->getElementById( 'contact' )->getAttribute( 'disabled' );
		$this->assertEquals( $assertion, $creation );
	}

	public function testDisablePageException()
	{
		try
		{
			$this->editor->disablePage( 'not_existing' );
		}
		catch( Exception $e )
		{
			return;
		}
		$this->fail( 'An expected Exception has not been thrown.' );
	}

	public function testEnablePage()
	{
		$assertion	= true;
		$creation	= $this->editor->enablePage( 'help' );
		$this->assertEquals( $assertion, $creation );

		$document	= new DOMDocument();
		$document->preserveWhiteSpace	= true;
		$document->validateOnParse = true;
		$document->load( $this->fileName );
		
		$assertion	= 0;
		$creation	= $document->getElementById( 'help' )->getAttribute( 'disabled' );
		$this->assertEquals( $assertion, $creation );
	}

	public function testEnablePageException()
	{
		try
		{
			$this->editor->enablePage( 'not_existing' );
		}
		catch( Exception $e )
		{
			return;
		}
		$this->fail( 'An expected Exception has not been thrown.' );
	}

	public function testHidePage()
	{
		$assertion	= true;
		$creation	= $this->editor->hidePage( 'contact' );
		$this->assertEquals( $assertion, $creation );

		$document	= new DOMDocument();
		$document->preserveWhiteSpace	= true;
		$document->validateOnParse = true;
		$document->load( $this->fileName );
		
		$assertion	= 1;
		$creation	= $document->getElementById( 'contact' )->getAttribute( 'hidden' );
		$this->assertEquals( $assertion, $creation );
	}

	public function testHidePageException()
	{
		try
		{
			$this->editor->hidePage( 'not_existing' );
		}
		catch( Exception $e )
		{
			return;
		}
		$this->fail( 'An expected Exception has not been thrown.' );
	}

	public function testRemoveRoleFromPage()
	{
		$this->editor->removeRoleFromPage( "public", "help" );

		$assertion	= false;
		$creation	= $this->findRoleForPage( "public", "help" );
		$this->assertEquals( $assertion, $creation );
	}

	public function testShowPage()
	{
		$assertion	= true;
		$creation	= $this->editor->showPage( 'contact' );
		$this->assertEquals( $assertion, $creation );

		$document	= new DOMDocument();
		$document->preserveWhiteSpace	= true;
		$document->validateOnParse = true;
		$document->load( $this->fileName );
		
		$assertion	= 0;
		$creation	= $document->getElementById( 'contact' )->getAttribute( 'hidden' );
		$this->assertEquals( $assertion, $creation );
	}

	public function testShowPageException()
	{
		try
		{
			$this->editor->showPage( 'not_existing' );
		}
		catch( Exception $e )
		{
			return;
		}
		$this->fail( 'An expected Exception has not been thrown.' );
	}
}
class Editor extends Framework_Krypton_Core_PageDefinitionEditor
{
	public function getFileName()
	{
		return $this->fileName;
	}
}
?>