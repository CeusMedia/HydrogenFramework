<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\Environment;

use CeusMedia\HydrogenFramework\Environment;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected Environment $env;
	protected string $baseTestPath;

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 2 ) . '/';
		$this->env		= new Environment( [
			'pathApp'	=> '',
			'uri'		=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
		$this->env->setMode( Environment::MODE_TEST );
		$this->env->getCaptain()->setLogCalls( TRUE );

		$pathLogs	= $this->env->path.$this->env->getPath( 'logs' );
		@unlink( $pathLogs.'app.log' );
		@unlink( $pathLogs.'app.info.log' );
		@unlink( $pathLogs.'app.exception.log' );
		@unlink( $pathLogs.'hook_calls.log' );
	}
}