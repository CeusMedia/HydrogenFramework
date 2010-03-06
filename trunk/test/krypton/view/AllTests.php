<?php
if( !defined( 'PHPUnit_MAIN_METHOD' ) )
	define( 'PHPUnit_MAIN_METHOD', 'Framework_Krypton_View_AllTests::main' );

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'framework/krypton/view/component/AllTests.php';
class Framework_Krypton_View_AllTests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run( self::suite() );
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite( 'cmClasses/Framework/Krypton/View' );
		$suite->addTest(Framework_Krypton_View_Component_AllTests::suite());
		return $suite;
	}
}
if( PHPUnit_MAIN_METHOD == 'Framework_Krypton_View_AllTests::main' )
	Framework_Krypton_View_AllTests::main();
?>
