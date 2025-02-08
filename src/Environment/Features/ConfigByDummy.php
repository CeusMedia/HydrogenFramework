<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Resource\Configuration as ConfigurationResource;

trait ConfigByDummy
{
	/**	@var	ConfigurationResource	$config */
	protected ConfigurationResource $config;

	/**
	 *	Returns Configuration Object.
	 *	@access		public
	 *	@return		ConfigurationResource
	 */
	public function getConfig(): ConfigurationResource
	{
		return $this->config;
	}

	protected function initConfig(): static
	{
		$this->config		= new ConfigurationResource( $this );						//  create empty configuration object
		return $this;
	}
}