<?php
/**
 *	TestUnit of Action
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Action
 *	@uses			Framework_Krypton_Core_Messenger
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.Action' );
import( 'de.ceus-media.framework.krypton.core.Messenger' );
import( 'de.ceus-media.adt.list.Dictionary' );
/**
 *	TestUnit of Action
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Action
 *	@uses			Framework_Krypton_Core_Messenger
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class TestAction extends Framework_Krypton_Core_Action
{
	public $var	= 0;
	public function __construct()
	{
		parent::__construct();
		$this->registerAction( 'testOne',			'returnOne' );
		$this->registerAction( 'notExisting',		'notExistingMethod' );
		$this->registerAction( 'removeThisAction',	'removeAction' );
	}

	public function addTestAction()
	{
		$this->registerAction( 'testAction' );
	}

	public function returnOne()
	{
		$this->var	= 1;
	}
}
 
class Framework_Krypton_Core_ActionTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$language = $this->getMock( 'Language', array( 'getWords' ) );
		$language->expects( $this->any() )->method( 'getWords' )->will( $this->returnValue( array() ) );
		
		$registry	= Framework_Krypton_Core_Registry::getInstance();
		$registry->set( 'request', new ADT_List_Dictionary, TRUE );
		$registry->set( 'session', new ADT_List_Dictionary, TRUE );
		$registry->set( 'messenger', new Framework_Krypton_Core_Messenger, TRUE );
		$registry->set( 'language', $language, TRUE );
	}

	public function setUp()
	{
		$this->action	= new TestAction();
	}

	public function testRegisterAction()
	{
		$this->action->addTestAction();
		$assertion	= TRUE;
		$creation	= $this->action->isRegisteredAction( 'testAction' );
		$this->assertEquals( $assertion, $creation );
	}

	public function testIsRegisteredAction()
	{
		$assertion	= TRUE;
		$creation	= $this->action->isRegisteredAction( 'testOne' );
		$this->assertEquals( $assertion, $creation );

		$assertion	= FALSE;
		$creation	= $this->action->isRegisteredAction( 'wrong_name' );
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testPerformActions()
	{
		$registry	= Framework_Krypton_Core_Registry::getInstance();
		$request	= $registry->get( 'request' );
		$request->set( 'testOne', '1' );
		
		$this->action->performActions();
		$assertion	= 1;
		$creation	= $this->action->var;
		$this->assertEquals( $assertion, $creation );
	}
	
	public function testPerformActionsException()
	{
		$registry	= Framework_Krypton_Core_Registry::getInstance();
		$request	= $registry->get( 'request' );
		$request->set( 'notExisting', 1 );

		try
		{
			$this->action->performActions();
		}
		catch( Exception $e )
		{
			return;
		}
		$this->fail( 'An expected Exception has not been thrown.' );
	}
	
	public function testUnregisterAction()
	{
		$this->action->unregisterAction( "testOne" );
		$assertion	= FALSE;
		$creation	= $this->action->isRegisteredAction( 'testOne' );
		$this->assertEquals( $assertion, $creation );
	}
}
?>