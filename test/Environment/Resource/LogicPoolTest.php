<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkTest\Environment\Resource;

use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\LogicPool;
use PHPUnit\Framework\TestCase;

use Logic_Example;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Environment\Resource\LogicPool
 */
class LogicPoolTest extends TestCase
{
	protected LogicPool $logic;
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
		$this->logic->add( 'addedObject', $object );
		self::assertTrue( $this->logic->has( 'addedObject' ) );
		self::assertTrue( $this->logic->isInstantiated( 'addedObject' ) );
		self::assertIsObject( $this->logic->get( 'addedObject' ) );
		self::assertInstanceOf( \stdClass::class, $this->logic->get( 'addedObject' ) );
		self::assertSame( ['addedObject'], $this->logic->index() );

		$this->logic->add( 'addedByObject', new Logic_Example() );
		self::assertTrue( $this->logic->has( 'addedByObject' ) );
		self::assertTrue( $this->logic->isInstantiated( 'addedByObject' ) );
		self::assertIsObject( $this->logic->get( 'addedByObject' ) );
		self::assertInstanceOf( Logic_Example::class, $this->logic->get( 'addedByObject' ) );
		self::assertSame( ['addedObject', 'addedByObject'], $this->logic->index() );

		$this->logic->add( 'addedByClass', Logic_Example::class );
		self::assertTrue( $this->logic->has( 'addedByClass' ) );
		self::assertFalse( $this->logic->isInstantiated( 'addedByClass' ) );
		self::assertIsObject( $this->logic->get( 'addedByClass' ) );
		self::assertInstanceOf( Logic_Example::class, $this->logic->get( 'addedByClass' ) );
		self::assertSame( ['addedObject', 'addedByObject', 'addedByClass'], $this->logic->index() );

		$this->logic->remove( 'addedObject' );
		self::assertSame( ['addedByObject', 'addedByClass'], $this->logic->index() );
		$this->logic->remove( 'addedByObject' );
		self::assertSame( ['addedByClass'], $this->logic->index() );
		$this->logic->remove( 'addedByClass' );
		self::assertSame( [], $this->logic->index() );

		$this->assertTrue( TRUE );
	}

	/**
	 *	@covers		::getKeyFromClassName
	 */
	public function testGetKeyFromClassName(): void
	{
		self::assertEquals( 'myResource', $this->logic->getKeyFromClassName( 'Logic_My_Resource' ) );
		self::assertEquals( 'example', $this->logic->getKeyFromClassName( Logic_Example::class ) );
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
		$object	= (object) [ 'foo' => 'bar' ];
		$this->logic->addedObject	= $object;
		self::assertTrue( isset( $this->logic->addedObject ) );
		self::assertTrue( $this->logic->isInstantiated( 'addedObject' ) );
		self::assertIsObject( $this->logic->addedObject );
		self::assertInstanceOf( \stdClass::class, $this->logic->addedObject );
		unset( $this->logic->addedObject );
		self::assertFalse( isset( $this->logic->addedObject ) );

		$this->logic->addedByObject	= new Logic_Example();
		self::assertTrue( isset( $this->logic->addedByObject ) );
		self::assertTrue( $this->logic->isInstantiated( 'addedByObject' ) );
		self::assertIsObject( $this->logic->addedByObject );
		self::assertInstanceOf( Logic_Example::class, $this->logic->addedByObject );
		unset( $this->logic->addedByObject );
		self::assertFalse( isset( $this->logic->addedByObject ) );

		$this->logic->addedByClass	= Logic_Example::class;
		self::assertTrue( isset( $this->logic->addedByClass ) );
		self::assertFalse( $this->logic->isInstantiated( 'addedByClass' ) );
		self::assertIsObject( $this->logic->addedByClass );
		self::assertTrue( $this->logic->isInstantiated( 'addedByClass' ) );
		self::assertInstanceOf( Logic_Example::class, $this->logic->addedByClass );
		unset( $this->logic->addedByClass );
		self::assertFalse( isset( $this->logic->addedByClass ) );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 2 ).'/';
		$this->env		= new Environment( ['pathApp' => $this->baseTestPath.'assets/app/'] );
		$this->logic	= new LogicPool( $this->env );

		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();
	}
}

