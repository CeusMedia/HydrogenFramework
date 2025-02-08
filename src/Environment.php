<?php /** @noinspection PhpUnused */
/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	General bootstrap of Hydrogen environment.
 *	Will be extended by client channel environment, like Web or Console.
 *
 *
 *	Copyright (c) 2007-2025 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework;

use CeusMedia\HydrogenFramework\Environment\Features\AccessControl as AccessControlFeature;
use CeusMedia\HydrogenFramework\Environment\Features\ArrayAccess as ArrayAccessFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Cache as CacheFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Captain as CaptainFeature;
use CeusMedia\HydrogenFramework\Environment\Features\ConfigByIni as ConfigByIniFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Database as DatabaseFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Disclosure as DisclosureFeature;
use CeusMedia\HydrogenFramework\Environment\Features\GetHasSet as GetHasSetFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Language as LanguageFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Log as LogFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Logics as LogicsFeature;
use CeusMedia\HydrogenFramework\Environment\Features\ModeDetection as ModeDetectionFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Modules as ModulesFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Paths as PathsFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Php as PhpFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Features\TimeZone as TimeZoneFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Version as VersionFeature;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

use ArrayAccess;
use ReflectionException;
use RuntimeException;

/**
 *	General bootstrap of Hydrogen environment.
 *	Will be extended by client channel environment, like Web or Console.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@implements		ArrayAccess<string, mixed>
 */
abstract class Environment implements ArrayAccess
{
	use AccessControlFeature;
	use ArrayAccessFeature;
	use CacheFeature;
	use CaptainFeature;
	use ConfigByIniFeature;
	use DatabaseFeature;
	use DisclosureFeature;
	use GetHasSetFeature;
	use LanguageFeature;
	use LogFeature;
	use LogicsFeature;
	use ModeDetectionFeature;
	use ModulesFeature;
	use PathsFeature;
	use PhpFeature;
	use RuntimeFeature;
	use TimeZoneFeature;
	use VersionFeature;

	public const MODE_UNKNOWN	= 0;
	public const MODE_DEV		= 1;
	public const MODE_TEST		= 2;
	public const MODE_STAGE		= 4;
	public const MODE_LIVE		= 8;

	public const MODES		= [
		self::MODE_UNKNOWN,
		self::MODE_DEV,
		self::MODE_TEST,
		self::MODE_STAGE,
		self::MODE_LIVE,
	];

	/**	@var	string						$configFile		File path to base configuration */
	public static string $configFile		= 'config.ini';

	/**	@var	string|NULL					$path			Absolute folder path of application */
	public ?string $path					= NULL;

	/**	@var	array						$options		Set options to override static properties */
	protected array $options				= [];

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@param		array		$options 			@todo: doc
	 *	@param		boolean		$isFinal			Flag: there is no extending environment class, default: TRUE
	 *	@return		void
	 *	@todo		possible error: call to onInit is to soon of another environment if existing
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	abstract public function __construct( array $options = [], bool $isFinal = TRUE );


	/**
	 *	Tries to unbind registered environment handler objects.
	 *	@access		public
	 *	@param		array		$additionalResources	List of resource member names to be unbound, too
	 *	@param		boolean		$keepAppAlive			Flag: do not end execution right now if turned on
	 *	@return		void
	 */
	public function close( array $additionalResources = [], bool $keepAppAlive = FALSE ): void
	{
		$coreResources	= [																				//  list of resource handler member names, namely of ...
			'config',																				//  ... base application configuration handler
			'runtime',																				//  ... internal runtime handler
			'cache',																				//  ... cache handler
			'database',																				//  ... database handler
			'logic',																				//  ... logic handler
			'modules',																				//  ... module handler
			'acl',																					//  ... cache handler
		];
		$resources	= array_merge( $coreResources, array_values( $additionalResources ) );			//  extend resources by additional ones
		foreach( array_reverse( $resources ) as $resource ){										//  iterate resources backwards
			if( isset( $this->$resource ) ){														//  resource is set
				if( isset( $this->runtime ) )														//  if runtime resource is still set ...
					$this->runtime->reach( 'env: close: '.$resource );								//  ... log action on profiler
				unset( $this->$resource );															//  unbind resource
			}
		}
		if( !$keepAppAlive )																		//  application is not meant to live without this environment
			exit( 0 );																				//  so end of environment is end of application
	}

	/**
	 *	@param		string		$keyConfig
	 *	@return		string
	 */
	public function getBaseUrl( string $keyConfig = 'app.base.url' ): string
	{
		if( $this->config->get( $keyConfig ) )
			return $this->config->get( $keyConfig );
		$host	= getEnv( 'HTTP_HOST' );
		if( FALSE !== $host ){
			$scriptName	= getEnv( 'SCRIPT_NAME' );
			if( FALSE !== $scriptName ){
				$path		= dirname( $scriptName ).'/';
				$scheme		= getEnv( 'HTTPS' ) ? 'https' : 'http';
				return $scheme.'://'.$host.$path;
			}
		}
		return '';
	}

	//  --  PROTECTED  --  //


	/**
	 *	Magic function called at the end of construction.
	 *	ATTENTION: In case of overriding, you MUST bubble down using parent::__onInit();
	 *	Otherwise you will lose the trigger for hook Env::init.
	 *
	 *	@access		protected
	 *	@return		void
	 *	@throws		RuntimeException
	 */
	protected function __onInit(): void
	{
		if( $this->hasModules() )																	//  module support and modules available
			$this->modules->callHook( 'Env', 'init', $this );										//  call related module event hooks
	}
}
