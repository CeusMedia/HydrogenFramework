<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkTest\Environment;

use CeusMedia\HydrogenFramework\Environment;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected Environment $env;
	protected string $baseTestPath;

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 1 ).'/';
		$this->env		= new Environment( ['pathApp' => $this->baseTestPath.'assets/app/'] );
		$this->env->setMode( Environment::MODE_TEST );
		$this->env->getCaptain()->setLogCalls( TRUE );

		$pathLogs	= $this->env->path.$this->env->getPath( 'logs' );
		@unlink( $pathLogs.'app.log' );
		@unlink( $pathLogs.'app.info.log' );
		@unlink( $pathLogs.'app.exception.log' );
		@unlink( $pathLogs.'hook_calls.log' );
	}
}