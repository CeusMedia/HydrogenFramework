<?php
/**
 *	General bootstrap of Hydrogen environment.
 *	Will be extended by client channel environment, like Web or Console.
 *
 *
 *	Copyright (c) 2007-2016 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	General bootstrap of Hydrogen environment.
 *	Will be extended by client channel environment, like Web or Console.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment implements ArrayAccess{

	const MODE_UNKNOWN	= 0;
	const MODE_DEV		= 1;
	const MODE_TEST		= 2;
	const MODE_STAGE	= 4;
	const MODE_LIVE		= 8;

	/** @var	CMF_Hydrogen_Environment_Resource_Acl_Abstract			$acl			Implementation of access control list */
	protected $acl;

	/**	@var	CMF_Hydrogen_Application								$application	Instance of Application */
	protected $application;

	/**	@var	CMM_SEA_Adapter_Interface								$cache			Instance of cache adapter */
	protected $cache;

	/**	@var	CMF_Hydrogen_Environment_Resource_Captain				$captain		Instance of captain */
	protected $captain;

	/**	@var	Alg_Time_Clock											$clock			Clock Object */
	protected $clock;

	/**	@var	ADT_List_Dictionary										$config			Configuration Object */
	protected $config;

	/**	@var	string													$configPath		Folder path to base configuration */
	public static $configPath				= "config/";

	/**	@var	string													$configFile		File path to base configuration */
	public static $configFile				= "config.ini";

	/**	@var	CMF_Hydrogen_Environment_Resource_Database_PDO			$dbc			Database Connection Object */
	protected $dbc;

	/**	@var	array													$defaultPaths	Map of default paths to extend base configuration */
	public static $defaultPaths				= array(
		'classes'	=> 'classes/',
		'contents'	=> 'contents/',
		'images'	=> 'contents/images/',
		'locales'	=> 'contents/locales/',
		'scripts'	=> 'contents/scripts/',
		'themes'	=> 'contents/themes/',
		'logs'		=> 'logs/',
		'templates'	=> 'templates/',
	);

	/**	@var	array													$disclosure		Map of classes ready to reflect */
	protected $disclosure;

	/**	@var	CMF_Hydrogen_Environment_Resource_LogicPool				$logic			Pool for logic class instances */
	protected $logic;

	/**	@var	integer													$mode			Environment mode (dev,test,live,...) */
	protected $mode							= 0;

	/**	@var	CMF_Hydrogen_Environment_Resource_Module_Library_Local	$modules		Handler for local modules */
	protected $modules						= array();

	/**	@var	array													$options		Set options to override static properties */
	protected $options						= array();

	/**	@var	string													$path			Absolute folder path of application */
	public $path							= NULL;

	/**	@var	string													$uri			Application URI (absolute local path) */
	public $uri;

	public static $timezone					= NULL;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@param		array		$options 			@todo: doc
	 *	@param		boolean		$isFinal			Flag: there is no extending environment class, default: TRUE
	 *	@return		void
	 *	@todo		possible error: call to onInit is to soon of another environment if existing
	 */
	public function __construct( $options = array(), $isFinal = TRUE ){
		$pattern			= '/^'.preg_quote( self::$configPath, '/' ).'/';								//  fix for migration
		self::$configFile	= preg_replace( $pattern, '', self::$configFile );						//  @todo remove in 0.8.6

		self::$defaultPaths['cache']	= sys_get_temp_dir().'/cache/';
		$this->options		= $options;																//  store given environment options
		$this->path			= isset( $options['pathApp'] ) ? $options['pathApp'] : getCwd().'/';	//  detect application path
		$this->uri			= getCwd().'/';															//  detect application base URI

		date_default_timezone_set( @date_default_timezone_get() );									//  avoid having no timezone set
		if( !empty( self::$timezone ) )																//  a timezone has be set externally before
			date_default_timezone_set( self::$timezone );											//  set this timezone

		$this->initClock();																			//  setup clock
		$this->initConfiguration();																	//  setup configuration
		$this->initCaptain();																		//  setup captain
		$this->initLogic();																			//  setup logic pool
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		$this->initLog();																			//  setup clock
		if( !$isFinal )
			return;
		$this->modules->callHook( 'Env', 'constructEnd', $this );									//  call module hooks for end of env construction
		$this->__onInit();																			//  default callback for construction end
	}

	/**
	 *	Magic function called at the end of construction.
	 *	ATTENTION: In case of overriding, you MUST bubble down using parent::__onInit();
	 *	Otherwise you will lose the trigger for hook Env::init.
	 *
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(){
		if( $this->hasModules() )																	//  module support and modules available
			$this->modules->callHook( 'Env', 'init', $this );										//  call related module event hooks
	}

	public function __get( $key ){
		return $this->get( $key );
	}

	/**
	 *	Tries to unbind registered environment handler objects.
	 *	@access		public
	 *	@param		array		$additionalResources	List of resource member names to be unbound, too
	 *	@param		boolean		$keepAppAlive			Flag: do not end execution right now if turned on
	 *	@return		void
	 */
	public function close( $additionalResources = array(), $keepAppAlive = FALSE ){
		$resources	= array(																		//  list of resource handler member names, namely of ...
			'config',																				//  ... base application configuration handler
			'clock',																				//  ... internal clock handler
			'cache',																				//  ... cache handler
			'dbc',																					//  ... database handler
			'logic',																				//  ... logic handler
			'modules',																				//  ... module handler
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

	protected function detectMode(){
		$modes	= preg_split( '/[_.:;>#@\/-]/', strtolower( $this->config->get( 'app.mode' ) ) );
		foreach( $modes as $mode ){
			switch( $mode ){
				case 'dev':
				case 'devel':
					$this->mode		|= CMF_Hydrogen_Environment::MODE_DEV;
					break;
				case 'test':
				case 'testing':
					$this->mode		|= CMF_Hydrogen_Environment::MODE_TEST;
					break;
				case 'stage':
				case 'staging':
					$this->mode		|= CMF_Hydrogen_Environment::MODE_STAGE;
					break;
				case 'live':
				case 'production':
					$this->mode		|= CMF_Hydrogen_Environment::MODE_LIVE;
					break;
			}
		}
	}

	public function get( $key, $strict = TRUE ){
		if( isset( $this->$key ) && !is_null( $this->$key ) )
			return $this->$key;
		if( $strict ){
			$message	= 'No environment resource found for key "%1$s"';
			throw new RuntimeException( sprintf( $message, $key ) );
		}
		return NULL;
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

	public function getCaptain(){
		return $this->captain;
	}

	public function getClock(){
		return $this->clock;
	}

	/**
	 *	Returns Configuration Object.
	 *	@access		public
	 *	@return		File_Configuration_Reader
	 */
	public function getConfig(){
		return $this->config;
	}

	public function getDatabase(){
		return $this->dbc;
	}

	public function getDisclosure(){
		return $this->disclosure;
	}

	/**
	 *	Returns Language Object.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Environment_Resource_Language
	 */
	public function getLanguage(){
		return $this->language;
	}

	public function getLog(){
		return $this->log;
	}

	/**
	 *	Returns Logic Pool Object.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Environment_Resource_LogicPool
	 */
	public function getLogic(){
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
	public function has( $key ){
		$method	= 'get'.ucFirst( $key );
		if( is_callable( array( $this, $method ) ) )
			if( is_object( call_user_func( array( $this, $method ) ) ) )
				return TRUE;
		if( isset( $this->$key ) && !is_null( isset( $this->$key ) ) )
			return TRUE;
		return FALSE;
	}

	public function hasAcl(){
		return $this->getConfig()->get( 'module.roles' );
	}

	public function hasModule( $moduleId ){
		if( !$this->hasModules() )
			return FALSE;
		return $this->getModules()->has( $moduleId );
	}

	public function hasModules(){
		return $this->modules !== NULL;
	}

	/**
	 *	Initialize remote access control list if roles module is installed.
	 *	Supported types:
	 *	- CMF_Hydrogen_Environment_Resource_Acl_Database
	 *	- CMF_Hydrogen_Environment_Resource_Acl_Server
	 *	@access		protected
	 *	@return		void
	 *	@todo		remove support for old repo and modules
	 *	@todo		remove support for old public links of discontinued ACL module
	 */
	protected function initAcl()
	{
		$config		= $this->getConfig();
		$type		= 'CMF_Hydrogen_Environment_Resource_Acl_AllPublic';

		//  @deprecated		to be removed
		//  @todo			remove this support for old repo and modules
		if( $config->get( 'module.roles' ) ){													//  check for roles module @deprected
			if( !( $type = $config->get( 'module.roles.acl' ) ) )								//  take ACL class from module config
				$type	= 'CMF_Hydrogen_Environment_Resource_Acl_Database';						//  otherwise apply database ACL
		}

		if( $config->get( 'module.resource_authentication' ) ){									//  check for authentication module
			if( !( $type == $config->get( 'module.resource_users.acl' ) ) )						//  take ACL class from module config
				$type	= 'CMF_Hydrogen_Environment_Resource_Acl_Database';						//  otherwise apply database ACL
		}

		$this->acl	= Alg_Object_Factory::createObject( $type, array( $this ) );
		$this->acl->roleAccessNone	= 0;
		$this->acl->roleAccessFull	= 128;

		//  @deprecated		module "ACL" is not existing anymore, modules are providing public links by configuration
		//	@todo			remove this support and check replacement in all modules
		$this->acl->setPublicLinks( explode( ',', $config->get( 'module.acl.public' ) ) );
		$this->acl->setPublicInsideLinks( explode( ',', $config->get( 'module.acl.inside' ) ) );
		$this->acl->setPublicOutsideLinks( explode( ',', $config->get( 'module.acl.outside' ) ) );

		//  @todo			this is the new code for the todo above, working with modules with defined links
		//  @todo			still, all older modules need to be checked and migrated (see chat modules and chat server)
		//  @todo			and what about links configured in config/pages.json ???
		$linksPublic		= array();
		$linksPublicOutside	= array();
		$linksPublicInside	= array();
		foreach( $this->getModules()->getAll() as $module ){
			foreach( $module->links as $link ){
				switch( $link->access ){
					case 'outside':
						$linksPublicOutside[]	= str_replace( "/", "_", $link->path );
						break;
					case 'inside':
						$linksPublicInside[]	= str_replace( "/", "_", $link->path );
						break;
					case 'public':
						$linksPublic[]	= str_replace( "/", "_", $link->path );
						break;
				}
			}
		}
		if( $linksPublic )
			$this->acl->setPublicLinks( $linksPublic );
		if( $linksPublicOutside )
			$this->acl->setPublicOutsideLinks( $linksPublicOutside );
		if( $linksPublicInside )
			$this->acl->setPublicInsideLinks( $linksPublicInside );

		$this->clock->profiler->tick( 'env: initAcl', 'Finished setup of access control list.' );
	}

	protected function initCache(){
		$this->cache	= new CMF_Hydrogen_Environment_Resource_CacheDummy();
		if( $this->modules )																		//  module support and modules available
			$this->modules->callHook( 'Env', 'initCache', $this );									//  call related module event hooks
		$this->clock->profiler->tick( 'env: initCache', 'Finished setup of cache' );
	}

	protected function initCaptain(){
		$this->captain	= new CMF_Hydrogen_Environment_Resource_Captain( $this );
		$this->clock->profiler->tick( 'env: initCaptain', 'Finished setup of event handler.' );
	}

	protected function initClock()
	{
		$this->clock	= new Alg_Time_Clock();
		$this->clock->profiler	= new CMF_Hydrogen_Environment_Resource_Profiler();
		$this->clock->profiler->tick( 'env: initClock', 'Finished setup of profiler.' );
	}

	/**
	 *	Sets up configuration resource reading main config file and module config files.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initConfiguration()
	{
		$configFile	= static::$configPath.self::$configFile;										//  get config file @todo remove this old way
		if( !empty( $this->options['configFile'] ) )												//  get config file from options @todo enforce this new way
			$configFile	= $this->options['configFile'];												//  get config file from options
		if( !file_exists( $configFile ) ){															//  config file not found
			$message	= sprintf( 'Config file "%s" not existing', $configFile );
			throw new CMF_Hydrogen_Environment_Exception( $message );								//  quit with exception
		}

		$data			= parse_ini_file( $configFile, FALSE );										//  parse configuration file (without section support)

		$configHost	= static::$configPath.getEnv( 'HTTP_HOST' ).'.ini';
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
		$this->detectMode();
		$this->clock->profiler->tick( 'env: config', 'Finished setup of base app configuration.' );
	}

	/**
	 *	Sets up database support.
	 *	@access		protected
	 *	@todo		remove deprecation in 0.7.0
	 *	@return		void
	 */
	protected function initDatabase()
	{
		if( !$this->modules || !$this->getModules()->has( 'Resource_Database' ) )					//  database module is not available
			return;
		$this->dbc	= new Resource_Database( $this );												//  try to configure and connect database
		$this->modules->callHook( 'Database', 'init', $this->dbc );									//  call events hooked to database init
		$this->clock->profiler->tick( 'env: database', 'Finished setup of database connection.' );
	}

	/**
	 *	@todo  		why not keep the resource object instead of reflected class list? would need refactoring of resource and related modules, thou...
	 *	@todo  		extract to resource module: question is where to store the resource? in env again?
	 *	@todo  		to be deprecated in 0.9: please use module Resource_Disclosure instead
	 */
	protected function initDisclosure()
	{
//	$clock	= new Alg_Time_Clock();
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure( array() );
		$this->disclosure	= $disclosure->reflect( 'classes/Controller/', array( 'classPrefix' => 'Controller_' ) );
//	remark( $clock->stop() );
		$this->clock->profiler->tick( 'env: disclosure', 'Finished setup of self disclosure handler.' );
	}

	/**
	 *	@todo  		extract to resource module
	 *	@todo  		extract to resource module: question is where to store the resource? in env again?
	 *	@todo  		to be deprecated in 0.9: please use module Resource_Disclosure instead
	 */
	protected function initLog(){
		$this->log	= new CMF_Hydrogen_Environment_Resource_Log( $this );
	}

	protected function initLanguage()
	{
		$this->language		= new CMF_Hydrogen_Environment_Resource_Language( $this );
		$this->clock->profiler->tick( 'env: language' );
	}

	protected function initLogic(){
		$this->logic		= new CMF_Hydrogen_Environment_Resource_LogicPool( $this );
		$this->clock->profiler->tick( 'env: logic', 'Finished setup of logic pool.' );
	}

	/**
	 *	@access		protected
	 *	@return		void
	 *	@todo		remove support for base_config::module.acl.public
	 */
	protected function initModules(){
		$this->modules	= new CMF_Hydrogen_Environment_Resource_Module_Library_Local( $this );
		$public			= explode( ',', $this->config->get( 'module.acl.public' ) );				//  get current public link list
		foreach( $this->modules->getAll() as $moduleId => $module ){								//  iterate all local app modules
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
					$link->path	= $link->path ? $link->path : 'index/index';
					$path	= str_replace( '/', '_', $link->path );									//  get link path
					if( !in_array( $path, $public ) )												//  link is not in public link list
						$public[]	= $path;														//  add link to public link list
				}
			}
		}
		if( !$this->captain )																		//  just in case custom env did not init captain
			$this->initCaptain();																	//  init caption for handling hooks
		if( !( $this instanceof CMF_Hydrogen_Environment_Remote ) )
			$this->modules->callHook( 'Env', 'initModules', $this );								//  call related module event hooks
		$this->config->set( 'module.acl.public', implode( ',', array_unique( $public ) ) );			//  save public link list
		$this->clock->profiler->tick( 'env: initModules', 'Finished setup of modules.' );
	}

	public function offsetExists( $key ){
//		return property_exists( $this, $key );														//  PHP 5.3
		return isset( $this->$key );																//  PHP 5.2
	}

	public function offsetGet( $key ){
		return $this->get( $key );
	}

	public function offsetSet( $key, $value ){
		return $this->set( $key, $value );
	}

	public function offsetUnset( $key ){
		return $this->remove( $key );
	}

	public function remove( $key ){
		$this->$key	= NULL;
	}

	public function set( $key, $object ){
		if( !is_object( $object ) ){
			$message	= 'Given resource "%1$s" is not an object';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		if( !preg_match( '/^\w+$/', $key ) ){
			$message	= 'Invalid resource key "%1$s"';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		$this->$key	= $object;
	}
}
?>
