<?php
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
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
 *	@category		cmFrameworks
 *	@package		Hydrogen.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Resource
 *	@abstract		Must be extended for application needs.
 *	@implements		CMF_Hydrogen_Environment
 *	@implements		ArrayAccess
 *	@uses			ADT_List_Dictionary
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
abstract class CMF_Hydrogen_Environment_Abstract implements CMF_Hydrogen_Environment, ArrayAccess
{
	/**	@var	Alg_Time_Clock				$clock		Clock Object */
	protected $clock;
	/**	@var	ADT_List_Dictionary			$config		Configuration Object */
	protected $config;

	public static $configFile				= "config.ini.inc";

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->initClock();
		$this->initConfiguration();																	//  --  CONFIGURATION  --  //
	}

	public function __get( $key )
	{
		return $this->get( $key );
	}

	public function close()
	{
		unset( $this->config );																		//
		unset( $this->clock );																		//
	}

	public function get( $key )
	{
		if( isset( $this->$key ) && !is_null( $this->$key ) )
			return $this->$key;
		$message	= 'No environment resource found for key "%1$s"';
		throw new RuntimeException( sprintf( $message, $key ) );
	}

	public function getClock()
	{
		return $this->clock;
	}

	/**
	 *	Returns Configuration Object.
	 *	@access		public
	 *	@return		File_Configuration_Reader
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 *	Indicates wheter a resource is an available object by its access method key.
	 *	@access		public
	 *	@param		string		$key		Resource access method key, ie. session, language, request
	 *	@return		boolean
	 */
	public function has( $key )
	{
		$method	= 'get'.ucFirst( $key );
		if( is_callable( array( $this, $method ) ) )
			if( is_object( call_user_func( array( $this, $method ) ) ) )
				return TRUE;
		return FALSE;
	}

	public function initClock()
	{
		$this->clock	= new Alg_Time_Clock();
	}

	protected function initConfiguration()
	{
		if( !file_exists( self::$configFile ) )
			throw new RuntimeException( 'Config file "'.self::$configFile.'" not existing' );
		$data			= parse_ini_file( self::$configFile, FALSE );			//  parse configuration file
		$this->config	= new ADT_List_Dictionary( $data );						//  create dictionary from array
		if( $this->config->has( 'config.error.reporting' ) )					//  error reporting is defined
			error_reporting( $this->config->get( 'config.error.reporting' ) );	//  set error reporting level
	}

	public function offsetExists( $key )
	{
//		return property_exists( $this, $key );														//  PHP 5.3
		return isset( $this->$key );																//  PHP 5.2
	}

	public function offsetGet( $key )
	{
		return $this->get( $key );
	}

	public function offsetSet( $key, $value )
	{
		return $this->set( $key, $value );
	}

	public function offsetUnset( $key )
	{
		return $this->remove( $key );
	}

	public function remove( $key )
	{
		$this->$key	= NULL;
	}

	public function set( $key, $object )
	{
		if( !is_object( $object ) )
		{
			$message	= 'Given resource "%1$s" is not an object';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		if( !preg_match( '/^\w$/', $key ) )
		{
			$message	= 'Invalid resource key "%1$s"';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		$this->$key	= $object;
	}
}
?>