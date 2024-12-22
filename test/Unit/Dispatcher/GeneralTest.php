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

	public function testGetClassNameVariationsByPath(): void
	{
		$list	= General::getClassNameVariationsByPath( 'test' );
		self::assertIsArray( $list );
		self::assertEquals( ['Test', 'TEST', 'test'], $list );

		$list	= General::getClassNameVariationsByPath( 'topic/test' );
		self::assertIsArray( $list );
		self::assertEquals( [
			'Topic_Test',
    		'Topic_TEST',
    		'Topic_test',
    		'TOPIC_Test',
    		'TOPIC_TEST',
    		'TOPIC_test',
    		'topic_Test',
    		'topic_TEST',
    		'topic_test',
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
			'pathApp'	=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
		$this->env->getRequest()->set( '__controller', '' );
		$this->env->getRequest()->set( '__action', '' );

		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();
	}
}