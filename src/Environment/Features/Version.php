<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

trait Version
{
	/** @var	string						$version		Framework version */
	public string $version;

	/**
	 *	Detects version of Hydrogen framework by reading its INI file.
	 *	Sets found version on environment.
	 *
	 *	@return		static
	 */
	protected function detectFrameworkVersion(): static
	{
		/** @var array $frameworkConfig */
		$frameworkConfig	= parse_ini_file( dirname( __DIR__, 3 ).'/hydrogen.ini' );
		$this->version		= $frameworkConfig['version'];
		return $this;
	}
}
