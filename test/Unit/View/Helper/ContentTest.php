<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\View\Helper;

use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Content as ContentHelper;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
	protected Environment $env;
	protected string $baseTestPath;
	protected ContentHelper $helper;

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

	public function testSetFileLey_throwsOnInvalidFile(): void
	{
		$this->expectException( FileNotExistingException::class );
		$this->helper->setFileKey( 'notExisting.html' );
	}

	public function testSetData_withTopic(): void
	{
		$this->helper->setData( ['key1' => 'value1'], 'list' );
		self::assertEquals( ['key1' => 'value1'], $this->helper->getData( 'list' ) );
	}

	public function testRender(): void
	{
		$expected	= file_get_contents( $this->baseTestPath.'contents/locales/de/test.html' );
		$this->helper->setData( ['key' => 'value'] );
		self::assertEquals( $expected, $this->helper->render() );
	}
	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 3 ).'/';
		$this->env		= new Environment( [
			'pathApp'	=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
		$this->helper	= new ContentHelper( $this->env );
	}
}