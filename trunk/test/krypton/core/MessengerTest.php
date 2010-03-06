<?php
/**
 *	TestUnit of Messenger
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Messenger
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
require_once 'PHPUnit/Framework/TestCase.php'; 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.core.Messenger' );
import( 'de.ceus-media.adt.list.Dictionary' );
/**
 *	TestUnit of Messenger
 *	@package		tests.framework.krypton.core
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_Core_Messenger
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@version		0.1
 */
class Framework_Krypton_Core_MessengerTest extends PHPUnit_Framework_TestCase
{
	protected $path	= "framework/krypton/core/";

	public function __construct()
	{
	}
	
	public function setUp()
	{
		$this->registry	= Framework_Krypton_Core_Registry::getInstance();

		$config		= array(
			'layout.formatTimestamp' => 'Y-m-d H:i:s'
		);
		$config		= new ADT_List_Dictionary( $config );
		$this->registry->set( 'config', $config, TRUE );

		$session	= new ADT_List_Dictionary();
		$this->registry->set( 'session', $session, true );
		$this->messenger	= new Framework_Krypton_Core_Messenger();
	}

	public function testConstruct()
	{
		$key		= 
		$message	= "The Messenger Message Key has been changed.";
		$session	= $this->registry->get( 'session' );
		$messenger	= new Framework_Krypton_Core_Messenger( "anotherKey" );
		$messenger->noteNotice( $message );
		
		$assertion	= true;
		$creation	= $session->has( 'anotherKey' );
		$this->assertEquals( $assertion, $creation );
		
		$messages	= $session->get( 'anotherKey' );
		$assertion	= $message;
		$creation	= $messages[0]['message'];
		$this->assertEquals( $assertion, $creation );
	}
		
	
	public function testNoteError()
	{
		$this->messenger->noteError( "test message: error" );

		$assertion	= file_get_contents( $this->path."messenger_error1.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_error1.html", $creation );
		$this->assertEquals( $assertion, $creation );


		$this->messenger->noteError( 'error: object "{object}" is {attribute}', 'Obj1', 'wrong' );

		$assertion	= file_get_contents( $this->path."messenger_error2.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_error2.html", $creation );
		$this->assertEquals( $assertion, $creation );
	}

	public function testNoteNotice()
	{
		$this->messenger->noteNotice( "test message: notice" );

		$assertion	= file_get_contents( $this->path."messenger_notice1.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_notice1.html", $creation );
		$this->assertEquals( $assertion, $creation );


		$this->messenger->noteNotice( 'notice: object "{object}" is {attribute}', 'Obj1', 'nice' );

		$assertion	= file_get_contents( $this->path."messenger_notice2.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_notice2.html", $creation );
		$this->assertEquals( $assertion, $creation );
	}

	public function testNoteFailure()
	{
		$this->messenger->noteFailure( "test message: failure" );

		$assertion	= file_get_contents( $this->path."messenger_failure1.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_failure1.html", $creation );
		$this->assertEquals( $assertion, $creation );


		$this->messenger->noteFailure( 'failure: object "{object}" is {attribute}', 'Obj1', 'ill' );

		$assertion	= file_get_contents( $this->path."messenger_failure2.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_failure2.html", $creation );
		$this->assertEquals( $assertion, $creation );
	}


	public function testNoteSuccess()
	{
		$this->messenger->noteSuccess( "test message: success" );

		$assertion	= file_get_contents( $this->path."messenger_success1.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_success1.html", $creation );
		$this->assertEquals( $assertion, $creation );


		$this->messenger->noteSuccess( 'success: object "{object}" is {attribute}', 'Obj1', 'correct' );

		$assertion	= file_get_contents( $this->path."messenger_success2.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger_success2.html", $creation );
		$this->assertEquals( $assertion, $creation );
	}

	public function testGotError()
	{
		$this->messenger->noteSuccess( "test message: success" );
		$assertion	= false;
		$creation	= $this->messenger->gotError();
		$this->assertEquals( $assertion, $creation );
		
		$this->messenger->noteNotice( "test message: notice" );
		$assertion	= false;
		$creation	= $this->messenger->gotError();
		$this->assertEquals( $assertion, $creation );
		
		$this->messenger->noteError( "test message: error" );
		$assertion	= true;
		$creation	= $this->messenger->gotError();
		$this->assertEquals( $assertion, $creation );
		
		$this->messenger->clear();
		$assertion	= false;
		$creation	= $this->messenger->gotError();
		$this->assertEquals( $assertion, $creation );
		
		$this->messenger->noteFailure( "test message: failure" );
		$assertion	= true;
		$creation	= $this->messenger->gotError();
		$this->assertEquals( $assertion, $creation );
		
	}

	public function testClear()
	{
		$this->messenger->noteFailure( "test message: failure" );
		$assertion	= true;
		$creation	= $this->messenger->gotError();
		$this->assertEquals( $assertion, $creation );

		$this->messenger->clear();

		$assertion	= false;
		$creation	= $this->messenger->gotError();
		$this->assertEquals( $assertion, $creation );
	}
		
	public function testAddHeading()
	{
		$session	= $this->registry->get( 'session' );
		$this->messenger->addHeading( "test1" );
		$assertion	= array( "test1" );
		$creation	= $session->get( 'messenger_headings' );
		$this->assertEquals( $assertion, $creation );
		
		$session	= $this->registry->get( 'session' );
		$this->messenger->addHeading( "test2" );
		$assertion	= array( "test1", "test2" );
		$creation	= $session->get( 'messenger_headings' );
		$this->assertEquals( $assertion, $creation );
	}

	public function testBuildHeading()
	{
		$session	= $this->registry->get( 'session' );
		$this->messenger->addHeading( "test1" );
		$this->messenger->addHeading( "test2" );

		$assertion	= "test1 / test2";
		$creation	= $this->messenger->buildHeadings();
		$this->assertEquals( $assertion, $creation );

		$this->messenger->headingSeparator	= " & ";		
		$assertion	= "test1 & test2";
		$creation	= $this->messenger->buildHeadings();
		$this->assertEquals( $assertion, $creation );
	}

	public function testBuildMessages()
	{
		$this->messenger->noteFailure( 'failure: object "{object}" is {attribute}', 'Obj1', 'ill' );
		$this->messenger->noteNotice( 'notice: object "{object}" is {attribute}', 'Obj1', 'nice' );
		$this->messenger->noteSuccess( 'success: object "{object}" is {attribute}', 'Obj1', 'correct' );
		$this->messenger->noteError( 'error: object "{object}" is {attribute}', 'Obj1', 'wrong' );

		$assertion	= file_get_contents( $this->path."messenger.html" );
		$creation	= preg_replace( "@\[.*\]@", "", $this->messenger->buildMessages() );
#		file_put_contents( $this->path."messenger.html", $creation );
		$this->assertEquals( $assertion, $creation );
	}	
}
?>