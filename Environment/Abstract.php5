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
	/**	@var	Alg_Time_Clock				$clock			Clock Object */
	protected $clock;
	/**	@var	ADT_List_Dictionary			$config			Configuration Object */
	protected $config;
	/**	@var	CMF_Hydrogen_Application	$application	Instance of Application */
	protected $application;
	
	public static $configFile				= "config.ini.inc";

	protected $acl							= NULL;
	protected $modules						= NULL;
	/**	@var	array						$options		Set options to override static properties */
	protected $options						= array();
	/**	@var	string						$path			Absolute folder path of application */
	public $path							= NULL;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $options = array() )
	{
		$this->options		= $options;
		$this->path			= isset( $options['pathApp'] ) ? $options['pathApp'] : getCwd().'/';
		$this->initClock();
		$this->initConfiguration();																	//  --  CONFIGURATION  --  //
		$this->initModules();																		//  --  MODULE SUPPORT  --  //
		$this->onInit();
		$this->onLoad();
	}

	public function onInit(){}
	
	public function onLoad(){}
	
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

	public function getApp(){
		return $this->application;
	}
	
	/**
	 *	Initialize remote access control list.
	 *	@access		public
	 *	@return		void
	 */
	public function getAcl()
	{
		return $this->acl;
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

	public function getDisclosure()
	{
		return $this->disclosure;
	}

	public function getModules(){
		return $this->modules;
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
		if( isset( $this->$key ) && !is_null( isset( $this->$key ) ) )
			return TRUE;
		return FALSE;
	}

	public function hasAcl()
	{
		return $this->getConfig()->get( 'module.roles' );
	}

	public function hasModules()
	{
		return $this->modules !== NULL;
	}

	/**
	 *	Initialize remote access control list if roles module is installed.
	 *	Supported types:
	 *	- CMF_Hydrogen_Environment_Resource_Acl_Database
	 *	- CMF_Hydrogen_Environment_Resource_Acl_Server
	 *	@access		protected
	 *	@return		void
	 */
	protected function initAcl()
	{
		$config		= $this->getConfig();
		$type		= 'CMF_Hydrogen_Environment_Resource_Acl_AllPublic';
		if( $config->get( 'module.roles' ) ){
			$type	= $config->get( 'module.roles.acl' );
			if( !$type )
				$type	= 'CMF_Hydrogen_Environment_Resource_Acl_Database';
		}
		
		$this->acl	= Alg_Object_Factory::createObject( $type, array( $this ) );
		$this->acl->roleAccessNone	= 0;
		$this->acl->roleAccessFull	= 128;
		
		$this->acl->setPublicLinks( explode( ',', $config->get( 'module.acl.public' ) ) );
	}

	protected function initClock()
	{
		$this->clock	= new Alg_Time_Clock();
	}

	/**
	 *	Sets up configuration resource reading main config file and module config files.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initConfiguration()
	{
		$configFile	= self::$configFile;
		if( !empty( $this->options['configFile'] ) )
			$configFile	= $this->options['configFile'];
		if( !file_exists( $configFile ) )
			throw new RuntimeException( 'Config file "'.$configFile.'" not existing' );
		$data			= parse_ini_file( $configFile, FALSE );			//  parse configuration file
		$this->config	= new ADT_List_Dictionary( $data );						//  create dictionary from array
		if( $this->config->has( 'config.error.reporting' ) )					//  error reporting is defined
			error_reporting( $this->config->get( 'config.error.reporting' ) );	//  set error reporting level
	}

	protected function initDisclosure()
	{
//	$clock	= new Alg_Time_Clock();
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure( array() );
		$this->disclosure	= $disclosure->reflect( 'classes/Controller/', array( 'classPrefix' => 'Controller_' ) );
//	remark( $clock->stop() );
	}
	
	protected function initModules(){
		$config			= $this->getConfig();
#		$this->modules	= new CMF_Hydrogen_Environment_Resource_Module_Handler( $this );
#		$modules		= $this->modules->getInstalled();
		$this->modules	= new CMF_Hydrogen_Environment_Resource_Module_Library_Local( $this );
		$modules		= $this->modules->getAll();
		foreach( $modules as $moduleId => $module ){
			
			$prefix	= 'module.'.strtolower( $moduleId );											//  build config key prefix of module
			$this->config->set( $prefix, TRUE );													//  enable module in configuration
			foreach( $module->config as $key => $value ){											//  iterate module configuration pairs
				if( is_object( $value) ){															//	@todo remove
					@settype( $value->value, $value->type );										//  cast value by set type
					$this->config->set( $prefix.'.'.$key, $value->value );							//	set configuration pair
				}
				else																				//  legacy @todo remove
					$this->config->set( $prefix.'.'.$key, $value );									//  
			}
			
			$public	= explode( ',', $this->config->get( 'module.acl.public' ) );					//  get current public link list
			foreach( $module->links as $link ){														//  iterate module links
				if( $link->access == "public" ){													//  link is public
					$path	= str_replace( '/', '_', $link->path );									//  get link path
					if( !in_array( $path, $public ) )												//  link is not in public link list
						$public[]	= $path;														//  add link to public link list
				}
			}
			$this->config->set( 'module.acl.public', implode( ',', $public ) );						//  save public link list
		}
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
		if( !preg_match( '/^\w+$/', $key ) )
		{
			$message	= 'Invalid resource key "%1$s"';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		$this->$key	= $object;
	}
}
?>