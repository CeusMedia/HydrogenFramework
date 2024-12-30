<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\Environment\Resource;

use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Environment\Resource\LogicPool;
use DomainException;
use PHPUnit\Framework\TestCase;

use Logic_Example;
use RuntimeException;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Environment\Resource\LogicPool
 */
class LogicPoolTest extends TestCase
{
	protected WebEnvironment $env;
	protected LogicPool $pool;
	protected string $baseTestPath;

	/**
	 *	@covers		::add
	 *	@covers		::get
	 *	@covers		::has
	 *	@covers		::index
	 *	@covers		::isInstantiated
	 *	@covers		::remove
	 *	@covers		::set
	 *	@covers		::createInstance
	 */
	public function testGeneralAccess(): void
	{
		$object	= (object) [ 'foo' => 'bar' ];
		$object	= new Logic_Example( $this->env );


		$this->pool->add( 'addedObject', $object );
		self::assertTrue( $this->pool->has( 'addedObject' ) );
		self::assertTrue( $this->pool->isInstantiated( 'addedObject' ) );
		self::assertIsObject( $this->pool->get( 'addedObject' ) );
		self::assertInstanceOf( Logic_Example::class, $this->pool->get( 'addedObject' ) );
		self::assertSame( ['addedObject'], $this->pool->index() );

		$this->pool->add( 'addedByObject', new Logic_Example( $this->env ) );
		self::assertTrue( $this->pool->has( 'addedByObject' ) );
		self::assertTrue( $this->pool->isInstantiated( 'addedByObject' ) );
		self::assertIsObject( $this->pool->get( 'addedByObject' ) );
		self::assertInstanceOf( Logic_Example::class, $this->pool->get( 'addedByObject' ) );
		self::assertSame( ['addedObject', 'addedByObject'], $this->pool->index() );

		$this->pool->add( 'addedByClass', Logic_Example::class );
		self::assertTrue( $this->pool->has( 'addedByClass' ) );
		self::assertFalse( $this->pool->isInstantiated( 'addedByClass' ) );
		self::assertIsObject( $this->pool->get( 'addedByClass' ) );
		self::assertInstanceOf( Logic_Example::class, $this->pool->get( 'addedByClass' ) );
		self::assertSame( ['addedObject', 'addedByObject', 'addedByClass'], $this->pool->index() );

		$this->pool->remove( 'addedObject' );
		self::assertSame( ['addedByObject', 'addedByClass'], $this->pool->index() );
		$this->pool->remove( 'addedByObject' );
		self::assertSame( ['addedByClass'], $this->pool->index() );
		$this->pool->remove( 'addedByClass' );
		self::assertSame( [], $this->pool->index() );

		$this->assertTrue( TRUE );
	}

	/**
	 *	@covers		::add
	 *	@covers		::get
	 *	@covers		::has
	 *	@covers		::index
	 *	@covers		::isInstantiated
	 *	@covers		::remove
	 *	@covers		::set
	 *	@covers		::createInstance
	 */
	public function testGeneralAccess_Exception(): void
	{
		$this->expectException( \InvalidArgumentException::class );
		$object	= (object) [ 'foo' => 'bar' ];
		$this->pool->add( 'addedObject', $object );
	}

	/**
	 *	@covers		::get
	 */
	public function testGet_WithInvalid_WillThrowException(): void
	{
		$this->expectException( DomainException::class );
		$this->pool->get( 'NotExisting' );
	}

	/**
	 *	@covers		::getKeyFromClassName
	 */
	public function testGetKeyFromClassName(): void
	{
		self::assertEquals( 'myResource', $this->pool->getKeyFromClassName( 'Logic_My_Resource' ) );
		self::assertEquals( 'example', $this->pool->getKeyFromClassName( Logic_Example::class ) );
	}

	/**
	 *	@covers		::getKeyFromClassName
	 */
	public function testGetKeyFromClassName_WithEmpty_ThrowsException(): void
	{
		$this->expectException( \InvalidArgumentException::class );
		self::assertEquals( 'myResource', $this->pool->getKeyFromClassName( '' ) );
	}

	/**
	 *	@covers		::getKeyFromClassName
	 */
	public function testGetKeyFromClassName_WithInvalid_ThrowsException(): void
	{
		$this->expectException( \InvalidArgumentException::class );
		$this->pool->getKeyFromClassName( 'Controller_Test' );
	}

	/**
	 *	@covers		::__get
	 *	@covers		::__isset
	 *	@covers		::__set
	 *	@covers		::__unset
	 *	@covers		::add
	 *	@covers		::get
	 *	@covers		::has
	 *	@covers		::isInstantiated
	 *	@covers		::remove
	 *	@covers		::set
	 *	@covers		::createInstance
	 */
	public function testMagicAccess(): void
	{
		$object	= new Logic_Example( $this->env );

		$this->pool->addedObject	= $object;
		self::assertTrue( isset( $this->pool->addedObject ) );
		self::assertTrue( $this->pool->isInstantiated( 'addedObject' ) );
		self::assertIsObject( $this->pool->addedObject );
		self::assertInstanceOf( Logic_Example::class, $this->pool->addedObject );
		unset( $this->pool->addedObject );
		self::assertFalse( isset( $this->pool->addedObject ) );

		$this->pool->addedByObject	= new Logic_Example( $this->env );
		self::assertTrue( isset( $this->pool->addedByObject ) );
		self::assertTrue( $this->pool->isInstantiated( 'addedByObject' ) );
		self::assertIsObject( $this->pool->addedByObject );
		self::assertInstanceOf( Logic_Example::class, $this->pool->addedByObject );
		unset( $this->pool->addedByObject );
		self::assertFalse( isset( $this->pool->addedByObject ) );

		$this->pool->addedByClass	= Logic_Example::class;
		self::assertTrue( isset( $this->pool->addedByClass ) );
		self::assertFalse( $this->pool->isInstantiated( 'addedByClass' ) );
		self::assertIsObject( $this->pool->addedByClass );
		self::assertTrue( $this->pool->isInstantiated( 'addedByClass' ) );
		self::assertInstanceOf( Logic_Example::class, $this->pool->addedByClass );
		unset( $this->pool->addedByClass );
		self::assertFalse( isset( $this->pool->addedByClass ) );
	}

	/**
	 *	@covers		::__get
	 *	@covers		::__isset
	 *	@covers		::__set
	 *	@covers		::__unset
	 *	@covers		::add
	 *	@covers		::get
	 *	@covers		::has
	 *	@covers		::isInstantiated
	 *	@covers		::remove
	 *	@covers		::set
	 *	@covers		::createInstance
	 */
	public function testMagicAccess_Exception(): void
	{
		$this->expectException( \InvalidArgumentException::class );
		$object	= (object) [ 'foo' => 'bar' ];
		$this->pool->addedObject	= $object;
	}

	/**
	 *	@covers		::remove
	 */
	public function testRemove(): void
	{
		$this->pool->add( 'Test1', 'Test1' );
		self::assertContains( 'Test1', $this->pool->index() );
		$this->pool->remove( 'Test1' );
		self::assertNotContains( 'Test1', $this->pool->index() );
	}

	/**
	 *	@covers		::remove
	 */
	public function testRemove_NotRegistered_WillThrowException(): void
	{
		$this->expectException( RuntimeException::class );
		$this->pool->remove( 'Test1' );
	}

	public function testSet_WithAlreadySet_WillThrowException(): void
	{
		$this->pool->add( 'Test1', 'Test1' );
		$this->expectException( RuntimeException::class );
		$this->pool->add( 'Test1', 'Test1' );
	}

	public function testSet_WithInvalid_WillThrowException(): void
	{
		$this->expectException( \InvalidArgumentException::class );
		$noLogicObject	= new \Hook_Duplicator( $this->env );
		$this->pool->set( 'someKey', $noLogicObject );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 3 ).'/';
		$this->env		= new WebEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
		$this->pool	= new LogicPool( $this->env );

		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();
	}
}

