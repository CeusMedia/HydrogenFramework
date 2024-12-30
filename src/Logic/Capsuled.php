<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Logic;

use CeusMedia\HydrogenFramework\Environment;

class Capsuled extends Abstraction
{
	public static function getInstance( Environment $env ): static
	{
		$class	= static::class;
		return new $class( $env );
	}

	//  --  PROTECTED  --  //

	/**
	 *	Cloning this logic is not allowed.
	 *	@return		void
	 *	@codeCoverageIgnore
	 */
	protected function __clone()
	{
	}
}