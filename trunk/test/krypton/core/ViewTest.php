<?php
/**
 *	TestUnit of View
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_View
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.View' );
import( 'de.ceus-media.framework.krypton.core.Messenger' );
import( 'de.ceus-media.adt.list.Dictionary' );
/**
 *	TestUnit of View
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_View
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class Framework_Krypton_Core_ViewTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$this->path	= dirname( __FILE__ )."/";
		$words	= array(
			'main'	=> array(
				'paging'	=> array(
					'next'		=> "[next]",
					'previous'	=> "[prev]",
					'last'		=> "[last]",
					'first'		=> "[first]",
					'more'		=> "[more]",
				),
			),
		);
					
		$language = $this->getMock( 'Language', array( 'getWords' ) );
		$language->expects( $this->any() )->method( 'getWords' )->will( $this->returnValue( $words ) );
		
		$registry	= Framework_Krypton_Core_Registry::getInstance();
		$registry->set( 'request', new ADT_List_Dictionary, true );
		$registry->set( 'session', new ADT_List_Dictionary, true );
		$registry->set( 'messenger', new Framework_Krypton_Core_Messenger, true );
		$registry->set( 'language', $language, true );
		
		$this->view	= new Framework_Krypton_Core_View;
	}

	public function testConstruct()
	{
		$view	= new Framework_Krypton_Core_View( true );
	}

	public function testBuildContent()
	{
		$assertion	= "";
		$creation	= $this->view->buildContent();
		$this->assertEquals( $assertion, $creation );
	}

	public function testBuildControl()
	{
		$assertion	= "";
		$creation	= $this->view->buildControl();
		$this->assertEquals( $assertion, $creation );
	}

	public function testBuildExtra()
	{
		$assertion	= "";
		$creation	= $this->view->buildExtra();
		$this->assertEquals( $assertion, $creation );
	}

	public function testBuildPaging1()
	{
		$assertion	= preg_replace( "@\r?\n *@s", "", file_get_contents( $this->path."paging1.html" ) );
		$creation	= preg_replace( "@\r?\n *@s", "", $this->view->buildPaging( 100, 10, 0 ) );
		$this->assertEquals( $assertion, $creation );
	}

	public function testBuildPaging2()
	{
		$assertion	= preg_replace( "@\r?\n *@s", "", file_get_contents( $this->path."paging2.html" ) );
		$creation	= preg_replace( "@\r?\n *@s", "", $this->view->buildPaging( 100, 10, 50 ) );
		$this->assertEquals( $assertion, $creation );
	}

	public function testBuildPaging3()
	{
		$assertion	= preg_replace( "@\r?\n *@s", "", file_get_contents( $this->path."paging3.html" ) );
		$creation	= preg_replace( "@\r?\n *@s", "", $this->view->buildPaging( 100, 10, 90 ) );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testBuildPadingOptions()
	{
		$options	= array(
			'coverage'	=> 10
		);

		$assertion	= preg_replace( "@\r?\n *@s", "", file_get_contents( $this->path."paging4.html" ) );
		$creation	= preg_replace( "@\r?\n *@s", "", $this->view->buildPaging( 100, 10, 50, $options ) );
		$this->assertEquals( $assertion, $creation );
	}
}
?>