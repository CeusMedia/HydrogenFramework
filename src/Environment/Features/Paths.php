<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\ConfigByIni as ConfigFeature;
use RangeException;
use RuntimeException;

trait Paths
{
	use ConfigFeature;

	/**	@var	array						$defaultPaths	Map of default paths to extend base configuration */
	public static array $defaultPaths		= [
		'classes'	=> 'classes/',
		'config'	=> 'config/',
		'logs'		=> 'logs/',
		'templates'	=> 'templates/',
	];

	/**
	 *	Return configured path by path key.
	 *	@access		public
	 *	@param		string		$key		...
	 *	@param		boolean		$strict		Flag: ... (default: yes)
	 *	@return		string|NULL
	 *	@throws		RangeException			if path is not configured using strict mode
	 */
	public function getPath( string $key, bool $strict = TRUE ): ?string
	{
		if( $strict && !$this->hasPath( $key ) )
			throw new RangeException( 'Path "'.$key.'" is not configured' );
		return $this->config->get( 'path.'.$key );
	}

	/**
	 *	Indicated whether a path is configured path key.
	 *	@access		public
	 *	@param		string		$key		...
	 *	@return		boolean
	 */
	public function hasPath( string $key ): bool
	{
		return $this->config->has( 'path.'.$key );
	}

	/**
	 *	Sets configured path in config instance.
	 *	This will NOT affect config files - only config instance in memory.
	 *	Returns self for chaining.
	 *	@access		public
	 *	@param		string		$key		Path key to set in config instance
	 *	@param		string		$path		Path to set in config instance
	 *	@param		boolean		$override	Flag: override path if already existing and strict mode off, default: yes
	 *	@param		boolean		$strict		Flag: throw exception if already existing, default: yes
	 *	@return		static
	 *	@throws		RuntimeException
	 */
	public function setPath( string $key, string $path, bool $override = TRUE, bool $strict = TRUE ): static
	{
		if( $this->hasPath( $key ) && !$override && $strict )
			throw new RuntimeException( 'Path "'.$key.'" is already set' );

		if( !isset( static::$defaultPaths['cache'] ) )
			static::$defaultPaths['cache']	= sys_get_temp_dir().'/cache/';

		$this->config->set( 'path.'.$key, $path );
		return $this;
	}
}
