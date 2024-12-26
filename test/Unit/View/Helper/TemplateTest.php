<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\View\Helper;

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\HydrogenFramework\View\Helper\Template as TemplateHelper;
use PHPUnit\Framework\TestCase;
use ReflectionException;


/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\View\Helper\Template
 */
class TemplateTest extends TestCase
{
	protected Environment $env;
	protected string $baseTestPath;
	protected TemplateHelper $helper;

	public function testAddData(): void
	{
		$this->helper->addData( 'key1', 'value1' );
		self::assertEquals( 'value1', $this->helper->getData( 'key1' ) );
	}

	public function testHasData(): void
	{
		$this->helper->addData( 'key1', 'value1' );
		self::assertTrue( $this->helper->hasData( 'key1' ) );
	}

	public function testSetData(): void
	{
		$this->helper->setData( ['key1' => 'value1'] );
		self::assertEquals( 'value1', $this->helper->getData( 'key1' ) );
	}

	public function testSetData_withTopic(): void
	{
		$this->helper->setData( ['key1' => 'value1'], 'list' );
		self::assertEquals( ['key1' => 'value1'], $this->helper->getData( 'list' ) );
	}

	public function testRender(): void
	{
		$view	= new View( $this->createWebEnvironment() );
		$view->addData( 'test', 'UnitTest' );
		$this->helper->addData( 'test', 'UnitTest' );
		$this->helper->setTemplateKey( 'test/test.php' );
		$this->helper->setView( $view );
		self::assertEquals( 'UnitTest', $this->helper->render() );
	}

	protected function createEnvironment(): Environment
	{
		$baseTestPath= dirname( __DIR__, 3 ).'/';
		return new Environment( [
			'pathApp'	=> '',
			'uri'		=> $baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
	}

	protected function createWebEnvironment(): WebEnvironment
	{
		$baseTestPath	= dirname( __DIR__, 3 ).'/';
		return new WebEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 */
	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 3 ).'/';
		$this->env		= $this->createEnvironment();
		$this->helper	= new TemplateHelper( $this->env );
	}
}