<?php
/**
 *	TestUnit of Framework_Krypton_View_Component_Template.
 *	@package		Tests.framework.krypton.view.component
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_View_Component_Template
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			12.04.2009
 *	@version		0.1
 */
require_once( 'PHPUnit/Framework/TestCase.php' ); 
require_once '../autoload.php5';
import( 'de.ceus-media.framework.krypton.view.component.Template' );
import( 'de.ceus-media.framework.krypton.core.Registry' );
/**
 *	TestUnit of Framework_Krypton_View_Component_Template.
 *	@package		Tests.framework.krypton.view.component
 *	@extends		PHPUnit_Framework_TestCase
 *	@uses			Framework_Krypton_View_Component_Template
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			12.04.2009
 *	@version		0.1
 */
final class Framework_Krypton_View_Component_TemplateTest extends PHPUnit_Framework_TestCase
{
	protected $path;
	protected $registry;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->path		= dirname( __FILE__ )."/";
		$config			= array(
			'paths.templates'	=> $this->path,
		);
		$this->registry	= Framework_Krypton_Core_Registry::getInstance();
		$this->registry->set( 'config', $config, TRUE );
	}
	
	/**
	 *	Setup for every Test.
	 *	@access		public
	 *	@return		void
	 */
	public function setUp()
	{
	}
	
	/**
	 *	Cleanup after every Test.
	 *	@access		public
	 *	@return		void
	 */
	public function tearDown()
	{
	}

	/**
	 *	Tests Method 'getTemplateUri'.
	 *	@access		public
	 *	@return		void
	 */
	public function testGetTemplate()
	{
		$template	= new Framework_Krypton_View_Component_TemplateInstance( "template.html" );
		$assertion	= file_get_contents( $this->path."template.html" );
		$creation	= $template->getTemplate();
		$this->assertEquals( $assertion, $creation );
	}

	/**
	 *	Tests Method 'getTemplateUri'.
	 *	@access		public
	 *	@return		void
	 */
	public function testGetTemplateUri()
	{
		$template	= new Framework_Krypton_View_Component_TemplateInstance( );
		
		$assertion	= $this->path."test.html";
		$creation	= $template->executeProtectedMethod( 'getTemplateUri', "test" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= $this->path."test/test.html";
		$creation	= $template->executeProtectedMethod( 'getTemplateUri', "test/test" );
		$this->assertEquals( $assertion, $creation );

		$assertion	= $this->path."test/test.html";
		$creation	= $template->executeProtectedMethod( 'getTemplateUri', "test.test" );
		$this->assertEquals( $assertion, $creation );
	}

	/**
	 *	Tests Method 'setTemplate'.
	 *	@access		public
	 *	@return		void
	 */
	public function testSetTemplate1()
	{
		$template	= new Framework_Krypton_View_Component_TemplateInstance( "template" );
		$assertion	= "template";
		$creation	= $template->getProtectedVar( 'fileName' );
		$this->assertEquals( $assertion, $creation );
	}		

	/**
	 *	Tests Method 'setTemplate'.
	 *	@access		public
	 *	@return		void
	 */
	public function testSetTemplate2()
	{
		$template	= new Framework_Krypton_View_Component_TemplateInstance( "template" );
		$assertion	= file_get_contents( $this->path."template.html" );
		$creation	= $template->getProtectedVar( 'template' );
		$this->assertEquals( $assertion, $creation );
	}		

	/**
	 *	Tests Exception of Method 'setTemplate'.
	 *	@access		public
	 *	@return		void
	 */
	public function testSetTemplateException()
	{
		$this->setExpectedException( 'Exception_Template' );
		$template	= new Framework_Krypton_View_Component_TemplateInstance( "not_existing" );
	}		
}
class Framework_Krypton_View_Component_TemplateInstance extends Framework_Krypton_View_Component_Template
{
	public function __construct( $fileName = NULL )
	{
		if( $fileName )
			parent::__construct( $fileName );
	}

	public function getProtectedVar( $varName )
	{
		if( !in_array( $varName, array_keys( get_object_vars( $this ) ) ) )
			throw new Exception( 'Var "'.$varName.'" is not declared.' );
		return $this->$varName;
	}

	public function executeProtectedMethod( $method, $content, $comment = NULL )
	{
		if( !method_exists( $this, $method ) )
			throw new Exception( 'Method "'.$method.'" is not callable.' );
		return $this->$method( $content, $comment );
	}
}

?>