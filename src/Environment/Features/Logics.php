<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\LogicPool as LogicPoolResource;

trait Logics
{
	use RuntimeFeature;

	/**	@var	LogicPoolResource			$logic			Pool for logic class instances */
	protected LogicPoolResource $logic;

	/**
	 *	Returns Logic Pool Object.
	 *	@access		public
	 *	@return		LogicPoolResource
	 */
	public function getLogic(): LogicPoolResource
	{
		return $this->logic;
	}

	protected function initLogic(): static
	{
		$this->logic		= new LogicPoolResource( $this );
		$this->runtime->reach( 'env: logic', 'Finished setup of logic pool.' );
		return $this;
	}
}
