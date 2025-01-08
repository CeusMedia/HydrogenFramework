<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class Configuration extends Dictionary
{
	protected Environment $env;

	public static function loadDefaultFileAsDictionary( Environment $env ): Dictionary
	{
		$instance = new self();
		return new Dictionary( $instance->loadDefaultFile()->getAll() );
	}

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		parent::__construct();
	}

	public function loadFile( string $configFile = NULL, string $configFolder = NULL ): static
	{
		$configFile		??= $this->env::class::$configFile;
		$configFolder	??= $this->env::class::$defaultPaths['config'];
		$configFilePath	= $configFolder.$this->env::class::$configFile;												//  get config file @todo remove this old way

		$absolutePrefix	= str_starts_with( $configFilePath, '/' ) ? '' : $this->env->uri;					//  prefix with app path if not already absolute
		$absolutePath	= $absolutePrefix.$configFilePath;
		if( !file_exists( $absolutePath ) ){														//  config file not found
			$message	= 'Main config file (%1$s) not found in %2$s';
			throw FileNotExistingException::create()												//  quit with exception
				->setMessage( sprintf( $message, $configFilePath, $absolutePrefix ) )
				->setResource( $absolutePath );
		}
		$data	= parse_ini_file( $absolutePath );													//  parse configuration file (without section support)
		if( FALSE === $data )
			throw new RuntimeException( 'Reading config file failed.' );					//  quit with exception

		ksort( $data );
		$this->pairs	= $data;
		$this->realizeDefaultPathsInConfigResource();
		return $this;
	}

	/**
	 *	@param		bool		$force		Flag: save even if already set, default: no
	 *	@return		void
	 */
	protected function realizeDefaultPathsInConfigResource( bool $force = FALSE ): void
	{
		foreach( $this->env::class::$defaultPaths as $key => $value ){											//  iterate default paths
			if( !$this->has( 'path.'.$key ) || $force ){								//  path is not set in config
				if( 0 !== strlen( trim( $value ?? '' ) ) )
					$value	= rtrim( trim( $value ), '/' ).'/';
				$this->set( 'path.'.$key, $value );											//  set path in config (in memory)
			}
		}
	}
}