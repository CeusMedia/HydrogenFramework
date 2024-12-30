<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\Logic;

use CeusMedia\Common\Exception\Deprecation;
use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\LogicPool;
use CeusMedia\HydrogenFramework\View;
use PHPUnit\Framework\TestCase;

use Logic_Capsuled;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\View
 */
class CapsuledTest extends TestCase
{
	protected string $appPath;
	protected string $baseTestPath;
	protected Environment $env;
	protected View $view;

	public function testConstruct(): void
	{
		$logic	= Logic_Capsuled::getInstance( $this->env );
		self::assertNotEquals( $logic, Logic_Capsuled::getInstance( $this->env ) );
		self::assertNotSame( $logic->uuid, Logic_Capsuled::getInstance( $this->env )->uuid );

		self::assertNotEquals( $logic, new Logic_Capsuled( $this->env ) );
		self::assertNotSame( $logic->uuid, (new Logic_Capsuled( $this->env ))->uuid );
	}

	public function testConstruct_WithLogicPool(): void
	{
		$logicPool	= new LogicPool( $this->env );

		$classKey	= $logicPool->getKeyFromClassName( Logic_Capsuled::class );
		$capsule1 = $logicPool->get( $classKey );
		self::assertIsObject( $capsule1 );

		$capsule2 = $logicPool->get( $classKey );
		self::assertIsObject( $capsule2 );
		self::assertNotSame( $capsule1->uuid, $capsule2->uuid );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 2 ).'/';
		$this->appPath		= $this->baseTestPath.'assets/app/';
		$this->env		= new Environment( [
			'pathApp'	=> '',
			'uri'		=> $this->appPath,
			'isTest'	=> TRUE,
		] );

		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();
	}
}