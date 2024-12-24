<?php

namespace CeusMedia\HydrogenFramework\Controller;

use CeusMedia\HydrogenFramework\Environment\Console as ConsoleEnvironment;

abstract class Console
{
	/** @var ConsoleEnvironment $env */
	protected ConsoleEnvironment $env;

	public function __construct( ConsoleEnvironment $env )
	{
		$this->setEnv( $env );
	}

	protected function setEnv( ConsoleEnvironment $env ): self
	{
		$this->env	= $env;
		return $this;
	}
}