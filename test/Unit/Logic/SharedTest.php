<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\Logic;

use CeusMedia\Common\Exception\Deprecation;
use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\LogicPool;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Hook as ModuleHookDefinition;
use CeusMedia\HydrogenFramework\View;
use PHPUnit\Framework\TestCase;

use Logic_Shared;
use Logic_Capsuled;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Logic\Shared
 */
class SharedTest extends TestCase
{
	protected string $appPath;
	protected string $baseTestPath;
	protected Environment $env;
	protected View $view;

	public function testConstruct(): void
	{
		$logic	= Logic_Shared::getInstance( $this->env );
		self::assertEquals( $logic, Logic_Shared::getInstance( $this->env ) );
		self::assertSame( $logic->uuid, Logic_Shared::getInstance( $this->env )->uuid );

		self::assertEquals( $logic, Logic_Shared::getInstance( $this->env ) );
		self::assertSame( $logic->uuid, Logic_Shared::getInstance( $this->env )->uuid );

		//  these 2 assertion only exist until dynamic construction of logic classes is not allowed anymore
		self::assertNotEquals( $logic, new Logic_Shared( $this->env ) );
		self::assertNotSame( $logic->uuid, (new Logic_Shared( $this->env ))->uuid );
	}

	public function testConstruct_WithLogicPool(): void
	{
		$logicPool	= new LogicPool( $this->env );

		$classKey	= $logicPool->getKeyFromClassName( Logic_Shared::class );
		$instance1 = $logicPool->get( $classKey );
		self::assertIsObject( $instance1 );

		$instance2 = $logicPool->get( $classKey );
		self::assertIsObject( $instance2 );
		self::assertSame( $instance1->uuid, $instance2->uuid );
	}

	public function testCallHook(): void
	{
		$this->env->getModules()->add( ModuleDefinition::create( 'TestModule', '1', 'test.xml' )
			->addHook( new ModuleHookDefinition( 'Hook_Duplicator::onDuplicate', 'Test', 'duplicate' ) )
		);

		$logic	= Logic_Shared::getInstance( $this->env );
		$content	= $logic->duplicateContent( 1 );
		self::assertEquals( 2, $content );
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