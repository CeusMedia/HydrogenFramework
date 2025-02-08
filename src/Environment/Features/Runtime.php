<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Resource\Runtime as RuntimeResource;

trait Runtime
{
	/**	@var	RuntimeResource				$runtime		Runtime Object */
	protected RuntimeResource $runtime;

	/**
	 *	@return		RuntimeResource
	 */
	public function getRuntime(): RuntimeResource
	{
		return $this->runtime;
	}

	protected function initRuntime(): static
	{
		$this->runtime	= new RuntimeResource( $this );
		$this->runtime->reach( 'env: initRuntime', 'Finished setup of profiler.' );
		return $this;
	}
}
