<?php
/**
 *	Early version of PHP environment resource.
 *
 *	Copyright (c) 2018-2020 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Early version of PHP environment resource.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Php
{
	public $version;

	protected $env;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env		= $env;
		$this->version	= new CMF_Hydrogen_Environment_Resource_Php_Version();
		$this->applyConfig();																		//  apply PHP configuration from config file
	}

	public function applyConfig()
	{
		$settings	= $this->env->getConfig()->getAll( 'php.', TRUE );								//  get PHP configuration from config file
		foreach( $settings as $key => $value ){														//  iterate set PHP configuration pairs
			try{																					//  try since there could by unknown constants
				$this->applyConfigPair( $key, $value );												//  apply config pair to PHP configuration
			} catch( Exception $e ){																//  detection of PHP configuration key or value failed
				$message	= sprintf( 'PHP configuration failed: %s', $e->getMessage() );			//  render exception message
				throw new RuntimeException( $message, 0, $e );										//  quit with exception
			}
		}
	}

	public function applyConfigPair( $key, $value )
	{
		if( ini_get( $key ) === FALSE )
			throw new RangeException( 'Unknown PHP configuration key: '.$key );
		if( preg_match( '/^([A-Z_]+(\s*,\s*))+$/', $value ) ){										//  value is list of constants
			$intVal = 0;																			//  prepare empty integer value
			foreach( preg_split( '/\s*,\s*/', $value ) as $item ){									//  iterate found constants
				if( !ADT_Constant::has( $item ) )													//  constant is undefined
					throw new RangeException( 'Unknown global constant: '.$item );					//  quit with exception
				$intVal	|= ADT_Constant::get( $item );												//  otherwise apply constant
			}
			$value	= $intVal;																		//  set config value by constants
		}
		ini_set( $key, $value );																	//  apply to PHP configuration
	}

	public function getCurrentVersion(): string
	{
		return $this->version->get();
	}
}
