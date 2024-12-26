<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest;

use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;
use PHPUnit\Framework\TestCase;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\View
 */
class ViewTest extends TestCase
{
	protected string $appPath;
	protected string $baseTestPath;
	protected WebEnvironment $env;
	protected View $view;

	public function testAddData(): void
	{
		$this->view->addData( 'key1', 'value1' );
		self::assertEquals( 'value1', $this->view->getData( 'key1' ) );
	}

	public function testHasData(): void
	{
		$this->view->addData( 'key1', 'value1' );
		self::assertTrue( $this->view->hasData( 'key1' ) );
	}

	public function testSetData(): void
	{
		$this->view->setData( ['key1' => 'value1'] );
		self::assertEquals( 'value1', $this->view->getData( 'key1' ) );
	}

	public function testSetData_withTopic(): void
	{
		$this->view->setData( ['key1' => 'value1'], 'list' );
		self::assertEquals( ['key1' => 'value1'], $this->view->getData( 'list' ) );
	}

	public function testHasContentFile(): void
	{
		self::assertTrue( $this->view->hasContentFile( 'test.html' ) );
	}

	public function testHasContentFile_withInvalidFile(): void
	{
		self::assertFalse( $this->view->hasContentFile( 'notExisting.html' ) );
	}

	public function testLoadContentFile(): void
	{
		$expected	= FileReader::load( $this->appPath.'contents/locales/de/test.html' );
		$this->view->setData( ['key' => 'value'] );
		self::assertEquals( $expected, $this->view->loadContentFile( 'test.html' ) );
	}

	public function testLoadContentFile_throwsOnInvalidFile(): void
	{
		$this->expectException( FileNotExistingException::class );
		$this->view->loadContentFile( 'notExisting.html' );
	}

	public function testLoadTemplate(): void
	{
		$this->view->addData( 'test', 'UnitTest::View::testLoadTemplate' );
		$result	= $this->view->loadTemplate( 'test', 'test' );
		self::assertEquals( 'UnitTest::View::testLoadTemplate', $result );
	}

	public function testLoadTemplateFile(): void
	{
		$this->view->addData( 'test', 'UnitTest::View::testLoadTemplateFile' );
		$result	= $this->view->loadTemplateFile( 'test/test.php' );
		self::assertEquals( 'UnitTest::View::testLoadTemplateFile', $result );
	}

	public function testPopulateTexts(): void
	{
		$expected	= file_get_contents( $this->appPath.'contents/locales/de/test.html' );
		$texts	= $this->view->populateTexts( ['test'], '', [], '' );
		self::assertEquals( ['test' => $expected], $texts );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 1 ).'/';
		$this->appPath		= $this->baseTestPath.'assets/app/';
		$this->env		= new WebEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $this->appPath,
			'isTest'	=> TRUE,
		] );

//		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();

		$this->view		= new View( $this->env );
	}
}