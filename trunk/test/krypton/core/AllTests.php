<?php
if( !defined( 'PHPUnit_MAIN_METHOD' ) )
	define( 'PHPUnit_MAIN_METHOD', 'Framework_Krypton_Core_AllTests::main' );

require_once( 'PHPUnit/Framework/TestSuite.php' );
require_once( 'PHPUnit/TextUI/TestRunner.php' );

require_once( 'framework/krypton/core/ActionTest.php' );
require_once( 'framework/krypton/core/CategoryFactoryTest.php' );
require_once( 'framework/krypton/core/ComponentTest.php' );
#require_once( 'framework/krypton/core/DefinitionActionTest.php' );
require_once( 'framework/krypton/core/DefinitionValidatorTest.php' );
require_once( 'framework/krypton/core/DefinitionViewTest.php' );
require_once( 'framework/krypton/core/FormDefinitionReaderTest.php' );
require_once( 'framework/krypton/core/LanguageTest.php' );
require_once( 'framework/krypton/core/LogicTest.php' );
#require_once( 'framework/krypton/core/MailSenderTest.php' );
require_once( 'framework/krypton/core/MessengerTest.php' );
require_once( 'framework/krypton/core/ModelTest.php' );
require_once( 'framework/krypton/core/PageControllerTest.php' );
require_once( 'framework/krypton/core/PageDefinitionEditorTest.php' );
require_once( 'framework/krypton/core/PageDefinitionReaderTest.php' );
#require_once( 'framework/krypton/core/PartitionSessionTest.php' );
require_once( 'framework/krypton/core/RegistryTest.php' );
#require_once( 'framework/krypton/core/SessionTest.php' );
#require_once( 'framework/krypton/core/SessionRegistryTest.php' );
#require_once( 'framework/krypton/core/StopWatchTest.php' );
require_once( 'framework/krypton/core/ViewTest.php' );


class Framework_Krypton_Core_AllTests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run( self::suite() );
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite( 'cmClasses/Framework/Krypton' );
		$suite->addTestSuite( 'Framework_Krypton_Core_ActionTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_CategoryFactoryTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_ComponentTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_DefinitionValidatorTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_DefinitionViewTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_FormDefinitionReaderTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_LanguageTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_LogicTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_MessengerTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_ModelTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_PageControllerTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_PageDefinitionEditorTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_PageDefinitionReaderTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_RegistryTest' ); 
		$suite->addTestSuite( 'Framework_Krypton_Core_ViewTest' ); 
		return $suite;
	}
}
if( PHPUnit_MAIN_METHOD == 'Framework_Krypton_Core_AllTests::main' )
	Framework_Krypton_Core_AllTests::main();
?>
