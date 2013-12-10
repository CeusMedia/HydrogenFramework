<?php
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *
 *	Copyright (c) 2007-2012 Christian Würker (ceusmedia.com)
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
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@abstract		Must be extended for application needs.
 *	@implements		CMF_Hydrogen_Environment
 *	@implements		ArrayAccess
 *	@uses			ADT_List_Dictionary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			decide whether to use onInit or onLoad and remove the other
 *	@todo		call to onInit is to soon of another environment is existing
 */
abstract class CMF_Hydrogen_Environment_Abstract implements CMF_Hydrogen_Environment, ArrayAccess
{
	/** @var	CMF_Hydrogen_Environment_Resource_Acl_Abstract	$acl	Implementation of access control list */
	protected $acl;
	/**	@var	CMF_Hydrogen_Application	$application	Instance of Application */
	protected $application;
	/**	@var	CMM_SEA_Adapter_Interface	$cache			Instance of cache adapter */
	protected $cache;
	/**	@var	Alg_Time_Clock				$clock			Clock Object */
	protected $clock;
	/**	@var	ADT_List_Dictionary			$config			Configuration Object */
	protected $config;
	/**	@var	CMF_Hydrogen_Environment_Resource_Database_PDO	$dbc		Database Connection Object */
	protected $dbc;
	
	public static $configFile				= "config.ini.inc";

	/**	@var	CMF_Hydrogen_Environment_Resource_LogicPool				$logic		Pool for logic class instances */
	protected $logic						= array();
	/**	@var	CMF_Hydrogen_Environment_Resource_Module_Library_Local	$modules	Handler for local modules */
	protected $modules						= array();
	/**	@var	array						$options		Set options to override static properties */
	protected $options						= array();
	/**	@var	string						$path			Absolute folder path of application */
	public $path							= NULL;

	public static $defaultPaths					= array(
		'classes'	=> 'classes/',
		'locales'	=> 'locales/',
		'logs'		=> 'logs/',
		'templates'	=> 'templates/',
	);
	
	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 *	@todo		call to onInit is to soon of another environment is existing
	 */
	public function __construct( $options = array() )
	{
		self::$defaultPaths['cache']	= sys_get_temp_dir().'/cache/';
		$this->options		= $options;																//  store given environment options
		$this->path			= isset( $options['pathApp'] ) ? $options['pathApp'] : getCwd().'/';	//  detect application path
		$this->initClock();																			//  setup clock
		$this->initConfiguration();																	//  setup configuration
		$this->initLogic();
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		if( $this->modules )
			$this->modules->callHook( 'Env', 'constructEnd', $this );
	}

	public function __onInit(){
		if( $this->modules )																		//  module support and modules available
			$this->modules->callHook( 'Env', 'init', $this );										//  call related module event hooks
	}

	public function __onLoad(){}

	public function __get( $key )
	{
		return $this->get( $key );
	}

	/**
	 *	Tries to unbind registered environment handler objects.
	 *	@access		public
	 *	@param		array		$additionalResources	List of resource member names to be unbound, too
	 *	@param		boolean		$keepAppAlive			Flag: do not end execution right now if turned on
	 *	@return		void
	 */
	public function close( $additionalResources = array(), $keepAppAlive = FALSE )
	{
		$resources	= array(																		//  list of resource handler member names, namely of ...
			'config',																				//  ... base application configuration handler
			'clock',																				//  ... internal clock handler
			'logic',																				//  ... logic handler 
			'modules',																				//  ... module handler
			'dbc',																					//  ... database handler
			'cache',																				//  ... cache handler
			'acl',																					//  ... cache handler
		);
		$resources	= array_merge( $resources, array_values( $additionalResources ) );
		foreach( array_reverse( $resources ) as $resource ){										//  iterate resources backwards
			if( isset( $this->$resource ) ){														//  resource is set
				if( isset( $this->clock ) )															//  if clock resource is still set ...
					$this->clock->profiler->tick( 'env: close: '.$resource );						//  ... log action on profiler
				unset( $this->$resource );															//  unbind resource
			}
		}
		unset( $this->application );																//  unbind relation to application instance object
		if( !$keepAppAlive )																		//  application is not meant to live without this environment
			exit( 0 );																				//  so end of environment is end of application
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
	 *	@return		CMF_Hydrogen_Environment_Resource_Acl_Abstract	Instance of access control list object
	 */
	public function getAcl(){
		return $this->acl;
	}

	public function getBaseUrl( $keyConfig = 'app.base.url' ){
		if( $this->config && $this->config->get( $keyConfig ) )
			return $this->config->get( $keyConfig );
		$host	= getEnv( 'HTTP_HOST' );
		if( $host ){
			$path	= dirname( getEnv( 'SCRIPT_NAME' ) ).'/';
			$scheme	= getEnv( 'HTTPS' ) ? 'https' : 'http';
			return $scheme.'://'.$host.$path;
		}
		return NULL;
	}

	public function getCache(){
		return $this->cache;
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

	public function getDatabase()
	{
		return $this->dbc;
	}

	public function getDisclosure()
	{
		return $this->disclosure;
	}

#	public function getLog()
#	{
#		return $this->log;
#	}

	/**
	 *	Returns Logic Pool Object.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Environment_Resource_LogicPool
	 */
	public function getLogic()
	{
		return $this->logic;
	}

	/**
	 *	Returns handler for local module library.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Environment_Resource_Module_Library_Local
	 */
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
		$this->acl->setPublicInsideLinks( explode( ',', $config->get( 'module.acl.inside' ) ) );
		$this->acl->setPublicOutsideLinks( explode( ',', $config->get( 'module.acl.outside' ) ) );
		$this->clock->profiler->tick( 'env: acl' );
	}

	protected function initCache(){
		$cache	= NULL;
		if( class_exists( 'CMM_SEA_Factory' ) ){
			$factory	= new CMM_SEA_Factory();
			$cache		= $factory->newStorage( 'Noop' );
			if( $this->modules->has( 'Resource_Cache' ) ){
				$config		= (object) $this->config->getAll( 'module.resource_cache.' );
				$type		= $config->type;
				$resource	= $config->resource ? $config->resource : NULL;
				$context	= $config->context ? $config->context : NULL;
				$expiration	= $config->expiration ? (int) $config->expiration : 0;

				if( $type == 'PDO' ){
					if( !$this->dbc )
						throw new RuntimeException( 'A database connection is needed for PDO cache adapter' );
					$resource	= array( $this->dbc, $this->dbc->getPrefix().$resource );
				}
				$cache	= $factory->newStorage( $type, $resource, $context, $expiration );
			}
		}
		if( !$cache )
			$cache	= new CMF_Hydrogen_Environment_Resource_CacheDummy();
		$this->cache	= $cache;
		$this->clock->profiler->tick( 'env: cache' );
	}

	protected function initClock()
	{
		$this->clock	= new Alg_Time_Clock();
		$this->clock->profiler	= new CMF_Hydrogen_Environment_Resource_Profiler();
	}

	/**
	 *	Sets up configuration resource reading main config file and module config files.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initConfiguration()
	{
		$configFile	= self::$configFile;															//  get config file @todo remove this old way
		if( !empty( $this->options['configFile'] ) )												//  get config file from options @todo enforce this new way
			$configFile	= $this->options['configFile'];												//  get config file from options
		if( !file_exists( $configFile ) ){															//  config file not found
			$message	= sprintf( 'Config file "%s" not existing', $configFile );
			throw new CMF_Hydrogen_Environment_Exception( $message );								//  quit with exception
		}

		$data			= parse_ini_file( $configFile, FALSE );										//  parse configuration file (without section support)

		$configHost	= 'config/'.getEnv( 'HTTP_HOST' ).'.ini';
		if( file_exists( $configHost ) )
			$data	= array_merge( $data, parse_ini_file( $configHost, FALSE ) );
		foreach( $data as $key => $value ){															//  iterate config pairs for evaluation
			$data[$key]	= trim( $value );															//  trim value string
			if( in_array( strtolower( $data[$key] ), array( "yes", "ja" ) ) )						//  value *means* yes
				$data[$key]	= TRUE;																	//  change value to boolean TRUE
			else if( in_array( strtolower( $data[$key] ), array( "no", "nein" ) ) )					//  value *means* no
				$data[$key]	= FALSE;																//  change value to boolean FALSE
		}

		foreach( self::$defaultPaths as $key => $value ){
			if( isset( $data['path.'.$key] ) )
				$value	= $data['path.'.$key];
			$value	= preg_replace( "@/+$@", "", $value )."/";
			$data['path.'.$key]	= $value;
		}
		ksort( $data );
		$this->config	= new ADT_List_Dictionary( $data );											//  create dictionary from array
		if( $this->config->has( 'config.error.reporting' ) )										//  error reporting is defined
			error_reporting( $this->config->get( 'config.error.reporting' ) );						//  set error reporting level
		$this->clock->profiler->tick( 'env: config' );
	}

	/**
	 *	Sets up database support.
	 *	@access		protected
	 *	@todo		remove deprecation in 0.7.0
	 *	@return		void
	 */
	protected function initDatabase()
	{
		$hasModule	= $this->getModules()->has( 'Resource_Database' );								//  module for database connection is enabled
		$hasConfig	= $this->config->get( 'database.driver' );										//  database connection is configured in main config (deprecated)
		if( $hasModule || $hasConfig )																//  database connection has been configured
			$this->dbc	= new CMF_Hydrogen_Environment_Resource_Database_PDO( $this );				//  try to configure and connect database
		$this->clock->profiler->tick( 'env: database' );
	}

	protected function initDisclosure()
	{
//	$clock	= new Alg_Time_Clock();
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure( array() );
		$this->disclosure	= $disclosure->reflect( 'classes/Controller/', array( 'classPrefix' => 'Controller_' ) );
//	remark( $clock->stop() );
		$this->clock->profiler->tick( 'env: disclosure' );
	}

#	protected function initLog(){
#		$this->log	= CMF_Hydrogen_Environment_Resource_Log( $this );
#	}
	protected function initLogic()
	{
		$this->logic		= new CMF_Hydrogen_Environment_Resource_LogicPool( $this );
		$this->clock->profiler->tick( 'env: logic' );
	}
	protected function initModules(){
#		$this->modules	= new CMF_Hydrogen_Environment_Resource_Module_Handler( $this );
#		$modules		= $this->modules->getInstalled();
		$this->modules	= new CMF_Hydrogen_Environment_Resource_Module_Library_Local( $this );
		$modules		= $this->modules->getAll();
		$public			= explode( ',', $this->config->get( 'module.acl.public' ) );				//  get current public link list
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

			foreach( $module->links as $link ){														//  iterate module links
				if( $link->access == "public" ){													//  link is public
					$path	= str_replace( '/', '_', $link->path );									//  get link path
					if( !in_array( $path, $public ) )												//  link is not in public link list
						$public[]	= $path;														//  add link to public link list
				}
			}
		}
		$this->modules->callHook( 'Env', 'initModules', $this );										//  call related module event hooks
		$this->config->set( 'module.acl.public', implode( ',', $public ) );						//  save public link list
		$this->clock->profiler->tick( 'env: modules' );
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
