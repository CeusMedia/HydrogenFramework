<?php
/**
 *	Early version of PHP environment resource.
 *
 *	Copyright (c) 2018-2025 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\ADT\Constant;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Php\Version as VersionResource;

use Exception;
use RangeException;
use RuntimeException;

/**
 *	Early version of PHP environment resource.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Php
{
	public VersionResource $version;

	protected Environment $env;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->version	= new VersionResource();
		$this->applyConfig();																		//  apply PHP configuration from config file
	}

	/**
	 *	...
	 *	@return		void
	 *	@throws		RuntimeException
	 */
	public function applyConfig()
	{
		$settings	= $this->env->getConfig()->getAll( 'php.', TRUE );				//  get PHP configuration from config file
		foreach( $settings as $key => $value ){														//  iterate set PHP configuration pairs
			try{																					//  try since there could be unknown constants
				$this->applyConfigPair( (string) $key, (string) $value );							//  apply config pair to PHP configuration
			} catch( Exception $e ){																//  detection of PHP configuration key or value failed
				$message	= sprintf( 'PHP configuration failed: %s', $e->getMessage() );	//  render exception message
				throw new RuntimeException( $message, 0, $e );								//  quit with exception
			}
		}
	}

	/**
	 *	...
	 *	@param		string				$key
	 *	@param		string|int|float	$value
	 *	@return		void
	 */
	public function applyConfigPair( string $key, $value )
	{
		if( ini_get( $key ) === FALSE )
			throw new RangeException( 'Unknown PHP configuration key: '.$key );
		if( preg_match( '/^([A-Z_]+(\s*,\s*))+$/', (string) $value ) ){						//  value is list of constants
			$intVal = 0;																			//  prepare empty integer value
			/** @var array<string> $parts */
			$parts	= preg_split( '/\s*,\s*/', (string) $value );
			foreach( $parts as $item ){																//  iterate found constants
				if( !Constant::has( $item ) )														//  constant is undefined
					throw new RangeException( 'Unknown global constant: '.$item );			//  quit with exception
				$intVal	|= Constant::get( $item );													//  otherwise apply constant
			}
			$value	= $intVal;																		//  set config value by constants
		}
		ini_set( $key, (string) $value );															//  apply to PHP configuration
	}

	/**
	 *	Returns current version of PHP.
	 *	@return		string
	 */
	public function getCurrentVersion(): string
	{
		return $this->version->get();
	}
}
