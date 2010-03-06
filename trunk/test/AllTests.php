<?php
if( !defined( 'PHPUnit_MAIN_METHOD' ) )
	define( 'PHPUnit_MAIN_METHOD', 'Framework_AllTests::main' );

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'framework/krypton/AllTests.php';
class Framework_AllTests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run( self::suite() );
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite( 'cmClasses/Framework' );
		$suite->addTest(Framework_Krypton_AllTests::suite());
		return $suite;
	}
}
if( PHPUnit_MAIN_METHOD == 'Framework_AllTests::main' )
	Framework_AllTests::main();
?>
