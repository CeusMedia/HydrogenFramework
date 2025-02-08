<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Configuration as ConfigurationResource;

use RuntimeException;

trait ConfigByIni
{
	use RuntimeFeature;

	/**	@var	string					$configFile			File path to base configuration */
	public static string $configFile	= 'config.ini';

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

	/**
	 *	Sets up configuration resource and loads main config file.
	 *	@access		protected
	 *	@return		static
	 *	@throws		FileNotExistingException
	 *	@throws		RuntimeException
	 */
	protected function initConfiguration(): static
	{
		$this->config = new ConfigurationResource( $this );
		$this->config->loadFile($this->options['configFile'] ?? NULL);
		$this->runtime->reach('env: config', 'Finished setup of base app configuration.');
		return $this;
	}
}
