<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\Dispatcher;

use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Dispatcher\General;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use PHPUnit\Framework\TestCase;


/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Environment\Resource\LogicPool
 */
class GeneralTest extends TestCase
{
	protected WebEnvironment $env;
	protected string $baseTestPath;

	public function testDispatch_Level1(): void
	{
		$this->env->getRequest()->set( '__controller', 'test' );
		$this->env->getRequest()->set( '__action', 'test' );
		$dispatcher = new General( $this->env );
		$result	= $dispatcher->dispatch();
		self::assertEquals( time(), $result );
	}

	public function testDispatch_Level2(): void
	{
		$this->env->getRequest()->set( '__controller', 'topic/test' );
		$this->env->getRequest()->set( '__action', 'test' );
		$dispatcher = new General( $this->env );
		$result	= $dispatcher->dispatch();
		self::assertEquals( 'Test', $result );
	}

	public function testDispatch_Level2WithCaseTolerance(): void
	{
		$this->env->getRequest()->set( '__controller', 'html/test' );
		$this->env->getRequest()->set( '__action', 'test' );
		$dispatcher = new General( $this->env );
		$result	= $dispatcher->dispatch();
		self::assertEquals( 'HTML', $result );
	}

	public function testGetClassNameVariationsByPath_Level1(): void
	{
		$list	= General::getClassNameVariationsByPath( 'test' );
		self::assertIsArray( $list );
		self::assertEquals( ['Test', 'TEST', 'test'], $list );

		$list	= General::getClassNameVariationsByPath( 'test', TRUE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Test', 'TEST'], $list );

		$list	= General::getClassNameVariationsByPath( 'test', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Test'], $list );
	}

	public function testGetClassNameVariationsByPath_Level1Empty(): void
	{
		$list	= General::getClassNameVariationsByPath( '' );
		self::assertIsArray( $list );
		self::assertEquals( [], $list );
	}

	public function testGetClassNameVariationsByPath_Level1TooLong(): void
	{
		General::$maxPathPartLength	= 24;
		$list	= General::getClassNameVariationsByPath( 'TooLongToBeAValidPathPart' );
		self::assertIsArray( $list );
		self::assertEquals( [], $list );
	}

	public function testGetClassNameVariationsByPath_Level1SkipNumbers(): void
	{
		$list	= General::getClassNameVariationsByPath( 'test/1' );
		self::assertIsArray( $list );
		self::assertEquals( ['Test', 'TEST', 'test'], $list );

		$list	= General::getClassNameVariationsByPath( 'test/1', TRUE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Test', 'TEST'], $list );

		$list	= General::getClassNameVariationsByPath( 'test/1', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Test'], $list );
	}

	public function testGetClassNameVariationsByPath_Level1SkipWordsOnUppercase(): void
	{
		$list	= General::getClassNameVariationsByPath( 'add', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Add'], $list );

		$list	= General::getClassNameVariationsByPath( 'add', TRUE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Add'], $list );
	}

	public function testGetClassNameVariationsByPath_Level1CamelCase(): void
	{
		$list	= General::getClassNameVariationsByPath( 'topic_test', FALSE, FALSE, TRUE );
		self::assertIsArray( $list );
		self::assertEquals( ['TopicTest'], $list );
	}

	public function testGetClassNameVariationsByPath_Level2(): void
	{
		$list	= General::getClassNameVariationsByPath( 'topic/test' );
		self::assertIsArray( $list );
		self::assertEquals( [
			'Topic_Test',
			'Topic_TEST',
			'Topic_test',
			'topic_Test',
			'topic_TEST',
			'topic_test',
		], $list );

		$list	= General::getClassNameVariationsByPath( 'topic/test', TRUE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( [
			'Topic_Test',
			'Topic_TEST',
		], $list );

		$list	= General::getClassNameVariationsByPath( 'topic/test', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( [
			'Topic_Test',
		], $list );
	}

	public function testGetClassNameVariationsByPath_Level2AlternativePathDivider(): void
	{
		General::$pathDivider	= '.';

		$list		= General::getClassNameVariationsByPath( 'topic.test', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Topic_Test'], $list );

		$list		= General::getClassNameVariationsByPath( 'topic.test.add', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Topic_Test_Add'], $list );
	}

	public function testGetClassNameVariationsByPath_Level2TooLong(): void
	{
		General::$maxPathPartLength	= 24;

		$list	= General::getClassNameVariationsByPath( 'Valid/TooLongToBeAValidPathPart' );
		self::assertIsArray( $list );
		self::assertEquals( ['Valid'], $list );

		$list	= General::getClassNameVariationsByPath( 'TooLongToBeAValidPathPart/Valid' );
		self::assertIsArray( $list );
		self::assertEquals( [], $list );
	}

	public function testGetClassNameVariationsByPath_Level2SkipNumbers(): void
	{
		$list		= General::getClassNameVariationsByPath( 'topic/test/1', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Topic_Test'], $list );

		$list	= General::getClassNameVariationsByPath( 'topic/1/test', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Topic'], $list );

		$list	= General::getClassNameVariationsByPath( 'topic/1/test/1', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Topic'], $list );
	}

	public function testGetClassNameVariationsByPath_Level2SkipWordsOnUppercase(): void
	{
		$list	= General::getClassNameVariationsByPath( 'test/add', FALSE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Test_Add'], $list );

		$list	= General::getClassNameVariationsByPath( 'test/add', TRUE, FALSE );
		self::assertIsArray( $list );
		self::assertEquals( ['Test_Add', 'TEST_Add'], $list );
	}

	public function testGetClassNameVariationsByPath_Level2CamelCase(): void
	{
		$list	= General::getClassNameVariationsByPath( 'topic_test/add', FALSE, FALSE, TRUE );
		self::assertIsArray( $list );
		self::assertEquals( ['TopicTest_Add'], $list );

		$list	= General::getClassNameVariationsByPath( 'topic_test/multiple_add', FALSE, FALSE, TRUE );
		self::assertIsArray( $list );
		self::assertEquals( ['TopicTest_MultipleAdd'], $list );
	}

	public function testGetClassNameVariationsByPath_Level2WithAll(): void
	{
		General::$pathDivider			= ':';
		General::$camelcaseDivider		= '.';
		$list	= General::getClassNameVariationsByPath( 'topic.test:html:multiple.add:1:2', TRUE, FALSE, TRUE );
		self::assertIsArray( $list );
		self::assertEquals( [
			'TopicTest_Html_MultipleAdd',
			'TopicTest_HTML_MultipleAdd',
		], $list );

		General::$uppercaseMaxLength	= 3;
		$list	= General::getClassNameVariationsByPath( 'topic.test:html:multiple.add:1:2', TRUE, FALSE, TRUE );
		self::assertIsArray( $list );
		self::assertEquals( [
			'TopicTest_Html_MultipleAdd',
		], $list );
	}

	public function testGetPrefixedClassInstanceByPathOrFirstClassNameGuess_Level1(): void
	{
		$result	= General::getPrefixedClassInstanceByPathOrFirstClassNameGuess( $this->env, 'Controller_', 'test' );
		self::assertIsObject( $result );
		self::assertInstanceOf( Controller::class, $result );
		self::assertEquals( 'Controller_Test', $result::class );
	}

	public function testGetPrefixedClassInstanceByPathOrFirstClassNameGuess_Level2(): void
	{
		$result	= General::getPrefixedClassInstanceByPathOrFirstClassNameGuess( $this->env, 'Controller_', 'topic/test' );
		self::assertIsObject( $result );
		self::assertInstanceOf( Controller::class, $result );
		self::assertEquals( 'Controller_Topic_Test', $result::class );
	}

	public function testGetPrefixedClassInstanceByPathOrFirstClassNameGuess_Level2WithCaseTolerance(): void
	{
		$result	= General::getPrefixedClassInstanceByPathOrFirstClassNameGuess( $this->env, 'Controller_', 'html/test' );
		self::assertIsObject( $result );
		self::assertInstanceOf( Controller::class, $result );
		self::assertEquals( 'Controller_HTML_Test', $result::class );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 2 ).'/';
		$this->env		= new WebEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
		$this->env->getRequest()->set( '__controller', '' );
		$this->env->getRequest()->set( '__action', '' );

		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();

		General::$pathDivider		= '/';
		General::$maxPathPartLength	= 32;
	}
}