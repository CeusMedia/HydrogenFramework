<?php
/**
 *	General bootstrap of Hydrogen environment.
 *	Will be extended by client channel environment, like Web or Console.
 *
 *
 *	Copyright (c) 2007-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework;

use CeusMedia\HydrogenFramework\Environment\Exception as Exception;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction as AbstractAclResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\AllPublic as AllPublicAclResource;
use CeusMedia\HydrogenFramework\Environment\Resource\CacheDummy as DummyCacheResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Disclosure as DisclosureResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Log as LogResource;
use CeusMedia\HydrogenFramework\Environment\Resource\LogicPool as LogicPoolResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Php as PhpResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Runtime as RuntimeResource;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;

use ADT_List_Dictionary as Dictionary;
use Alg_Object_Factory as ObjectFactory;
use ArrayAccess;
use InvalidArgumentException;
use RangeException;
use RuntimeException;

/**
 *	General bootstrap of Hydrogen environment.
 *	Will be extended by client channel environment, like Web or Console.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Environment implements ArrayAccess
{
	const MODE_UNKNOWN	= 0;
	const MODE_DEV		= 1;
	const MODE_TEST		= 2;
	const MODE_STAGE	= 4;
	const MODE_LIVE		= 8;

	const MODES			= [
		self::MODE_UNKNOWN,
		self::MODE_DEV,
		self::MODE_TEST,
		self::MODE_STAGE,
		self::MODE_LIVE,
	];

	/**	@var	string						$configFile		File path to base configuration */
	public static $configFile				= 'config.ini';

	/**	@var	array						$defaultPaths	Map of default paths to extend base configuration */
	public static $defaultPaths				= array(
		'classes'	=> 'classes/',
		'config'	=> 'config/',
		'logs'		=> 'logs/',
	);

	public static $timezone					= NULL;

	/**	@var	string						$path			Absolute folder path of application */
	public $path							= NULL;

	/**	@var	PhpResource					$php			Instance of PHP environment collection */
	public $php;

	/**	@var	string						$uri			Application URI (absolute local path) */
	public $uri;

	/**	@var	string						$url			Application URI */
	public $url;

	/** @var	string						$version		Framework version */
	public $version;

	/** @var	AbstractAclResource			$acl			Implementation of access control list */
	protected $acl;

	/**	@var	object						$cache			Instance of cache adapter */
	protected $cache;

	/**	@var	CaptainResource				$captain		Instance of captain */
	protected $captain;

	/**	@var	Dictionary					$config			Configuration Object */
	protected $config;

	/**	@var	object						$database		Database Connection Object */
	protected $database;

	/**	@var	LanguageResource			$language		Language support object */
	protected $language;

	/**	@var	LogResource					$log			Log support object */
	protected $log;

	/**	@var	array						$disclosure		Map of classes ready to reflect */
	protected $disclosure;

	/**	@var	LogicPoolResource                                                                                                                                                                                                              				$logic			Pool for logic class instances */
	protected $logic;

	/**	@var	integer						$mode			Environment mode (dev,test,live,...) */
	protected $mode							= 0;

	/**	@var	LocalModuleLibraryResource	$modules		Handler for local modules */
	protected $modules						= array();

	/**	@var	array						$options		Set options to override static properties */
	protected $options						= array();

	/**	@var	RuntimeResource				$runtime		Runtime Object */
	protected $runtime;

	/**	@var	Dictionary					$request		Request Object */
	protected $request;

	/**	@var	Dictionary					$session		Session Object */
	protected $session;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@param		array		$options 			@todo: doc
	 *	@param		boolean		$isFinal			Flag: there is no extending environment class, default: TRUE
	 *	@return		void
	 *	@todo		possible error: call to onInit is to soon of another environment if existing
	 */
	public function __construct( array $options = array(), bool $isFinal = TRUE )
	{
		$frameworkConfig	= parse_ini_file( dirname( __DIR__ ).'/hydrogen.ini' );
		$this->version		= $frameworkConfig['version'];

		static::$defaultPaths['cache']	= sys_get_temp_dir().'/cache/';
		$this->options		= $options;																//  store given environment options
		$this->path			= isset( $options['pathApp'] ) ? $options['pathApp'] : getCwd().'/';	//  detect application path
		$this->uri			= getCwd().'/';															//  detect application base URI

		date_default_timezone_set( @date_default_timezone_get() );									//  avoid having no timezone set
		if( !empty( static::$timezone ) )															//  a timezone has be set externally before
			date_default_timezone_set( static::$timezone );											//  set this timezone

		$this->initRuntime();																		//  setup runtime clock
		$this->initConfiguration();																	//  setup configuration
		$this->initPhp();																			//  setup PHP environment
		$this->initCaptain();																		//  setup captain
		$this->initLogic();																			//  setup logic pool
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		$this->initLog();																			//  setup logger
		if( !$isFinal )
			return;
		$this->modules->callHook( 'Env', 'constructEnd', $this );									//  call module hooks for end of env construction
		$this->__onInit();																			//  default callback for construction end
	}

	public function __get( string $key )
	{
		if( $key === 'clock' )
			return $this->get( 'runtime' );
		return $this->get( $key );
	}

	/**
	 *	Tries to unbind registered environment handler objects.
	 *	@access		public
	 *	@param		array		$additionalResources	List of resource member names to be unbound, too
	 *	@param		boolean		$keepAppAlive			Flag: do not end execution right now if turned on
	 *	@return		void
	 */
	public function close( array $additionalResources = array(), bool $keepAppAlive = FALSE )
	{
		$resources	= array(																		//  list of resource handler member names, namely of ...
			'config',																				//  ... base application configuration handler
			'runtime',																				//  ... internal runtime handler
			'cache',																				//  ... cache handler
			'database',																				//  ... database handler
			'logic',																				//  ... logic handler
			'modules',																				//  ... module handler
			'acl',																					//  ... cache handler
		);
		$resources	= array_merge( $resources, array_values( $additionalResources ) );
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

	public function get( string $key, bool $strict = TRUE )
	{
		if( isset( $this->$key ) && !is_null( $this->$key ) )
			return $this->$key;
		if( $key === 'clock' ){
			Deprecation::getInstance()
				->setErrorVersion( '0.8.7.9' )
				->setExceptionVersion( '0.9' )
				->message( 'Use $[this->]env->get( \'runtime\' ) or $[this->]env->runtime instead' );
			return $this->runtime;
		}

		if( $strict ){
			$message	= 'No environment resource found for key "%1$s"';
			throw new RuntimeException( sprintf( $message, $key ) );
		}
		return NULL;
	}

	/**
	 *	Initialize remote access control list.
	 *	@access		public
	 *	@return		AbstractAclResource	Instance of access control list object
	 */
	public function getAcl(): AbstractAclResource
	{
		return $this->acl;
	}

	public function getBaseUrl( string $keyConfig = 'app.base.url' ): string
	{
		if( $this->config && $this->config->get( $keyConfig ) )
			return $this->config->get( $keyConfig );
		$host	= getEnv( 'HTTP_HOST' );
		if( $host ){
			$path	= dirname( getEnv( 'SCRIPT_NAME' ) ).'/';
			$scheme	= getEnv( 'HTTPS' ) ? 'https' : 'http';
			return $scheme.'://'.$host.$path;
		}
		return '';
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function getCaptain(): CaptainResource
	{
		return $this->captain;
	}

	public function getClock(): RuntimeResource
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Environment clock $env->getClock() is deprecated. Use $env->getRuntime() instead' );
		return $this->runtime;
	}

	/**
	 *	Returns Configuration Object.
	 *	@access		public
	 *	@return		Dictionary
	 */
	public function getConfig(): Dictionary
	{
		return $this->config;
	}

	public function getDatabase()
	{
		return $this->database;
	}

	public function getDisclosure()
	{
		return $this->disclosure;
	}

	/**
	 *	Returns Language Object.
	 *	@access		public
	 *	@return		LanguageResource
	 */
	public function getLanguage(): LanguageResource
	{
		return $this->language;
	}

	public function getLog(){
		return $this->log;
	}

	/**
	 *	Returns Logic Pool Object.
	 *	@access		public
	 *	@return		LogicPoolResource
	 */
	public function getLogic(): LogicPoolResource
	{
		return $this->logic;
	}

	/**
	 *	Returns mode of environment.
	 *	@access		public
	 *	@return		integer
	 */
	public function getMode(): int
	{
		return $this->mode;
	}

	/**
	 *	Returns handler for local module library.
	 *	@access		public
	 *	@return		LocalModuleLibraryResource
	 */
	public function getModules(): LocalModuleLibraryResource
	{
		return $this->modules;
	}

	/**
	 *	Return configured path by path key.
	 *	@access		public
	 *	@param		string		$key		...
	 *	@param		boolean		$strict		Flag: ... (default: yes)
	 *	@return		string|NULL
	 *	@throws		RangeException			if path is not configured using strict mode
	 */
	public function getPath( string $key, bool $strict = TRUE )
	{
		if( $strict && !$this->hasPath( $key ) )
			throw new RangeException( 'Path "'.$key.'" is not configured' );
		return $this->config->get( 'path.'.$key );
	}

	/**
	 *	Returns PHP configuration and version management.
	 *	@access		public
	 *	@return		PhpResource
	 */
	public function getPhp(): PhpResource
	{
		return $this->php;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function getRuntime(): RuntimeResource
	{
		return $this->runtime;
	}

	public function getSession()
	{
		return $this->session;
	}

	/**
	 *	Indicates wheter a resource is an available object by its access method key.
	 *	@access		public
	 *	@param		string		$key		Resource access method key, ie. session, language, request
	 *	@return		boolean
	 */
	public function has( string $key ): bool
	{
		$method	= 'get'.ucFirst( $key );
		if( is_callable( array( $this, $method ) ) )
			if( is_object( call_user_func( array( $this, $method ) ) ) )
				return TRUE;
		if( isset( $this->$key ) && !is_null( isset( $this->$key ) ) )
			return TRUE;
		return FALSE;
	}

	/**
	 *	@todo this is totally outdated - refactor if possible
	 */
	public function hasAcl()
	{
		return $this->getConfig()->get( 'module.roles' );
	}

	public function hasModule( string $moduleId ): bool
	{
		if( !$this->hasModules() )
			return FALSE;
		return $this->getModules()->has( $moduleId );
	}

	public function hasModules(): bool
	{
		return $this->modules !== NULL;
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

	//  --  PROTECTED  --  //

	/**
	 *	Magic function called at the end of construction.
	 *	ATTENTION: In case of overriding, you MUST bubble down using parent::__onInit();
	 *	Otherwise you will lose the trigger for hook Env::init.
	 *
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit()
	{
		if( $this->hasModules() )																	//  module support and modules available
			$this->modules->callHook( 'Env', 'init', $this );										//  call related module event hooks
	}

	protected function detectMode()
	{
		$modes	= preg_split( '/[_.:;>#@\/-]/', strtolower( $this->config->get( 'app.mode' ) ) );
		foreach( $modes as $mode ){
			switch( $mode ){
				case 'dev':
				case 'devel':
					$this->mode		|= self::MODE_DEV;
					break;
				case 'test':
				case 'testing':
					$this->mode		|= self::MODE_TEST;
					break;
				case 'stage':
				case 'staging':
					$this->mode		|= self::MODE_STAGE;
					break;
				case 'live':
				case 'production':
					$this->mode		|= self::MODE_LIVE;
					break;
			}
		}
	}

	/**
	 *	Initialize remote access control list if roles module is installed.
	 *	Calls hook and applies return class name. Otherwise use all-public handler.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initAcl()
	{
		$config		= $this->getConfig();
		$type		= AllPublicAclResource::class;
		if( $this->hasModules() ){																	//  module support and modules available
			$payload	= (object) ['className' => NULL];
			$isHandled	= $this->modules->callHook( 'Env', 'initAcl', $this, $payload );			//  call related module event hooks
			if( $isHandled )
				$type	= $payload->className;
		}
		$this->acl	= ObjectFactory::createObject( $type, array( $this ) );
		$this->acl->roleAccessNone	= 0;
		$this->acl->roleAccessFull	= 128;

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
		if( 0 !== count( $linksPublic ) )
			$this->acl->setPublicLinks( $linksPublic );
		if( 0 !== count( $linksPublicOutside ) )
			$this->acl->setPublicOutsideLinks( $linksPublicOutside );
		if( 0 !== count( $linksPublicInside ) )
			$this->acl->setPublicInsideLinks( $linksPublicInside );

		$this->runtime->reach( 'env: initAcl', 'Finished setup of access control list.' );
	}

	protected function initCache()
	{
		$this->cache	= new DummyCacheResource();
		if( $this->modules )																		//  module support and modules available
			$this->modules->callHook( 'Env', 'initCache', $this );									//  call related module event hooks
		$this->runtime->reach( 'env: initCache', 'Finished setup of cache' );
	}

	protected function initCaptain()
	{
		$this->captain	= new CaptainResource( $this );
		$this->runtime->reach( 'env: initCaptain', 'Finished setup of event handler.' );
	}

	protected function initClock()
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Use initRuntime() instead' );
		$this->initRuntime();
	}

	/**
	 *	Sets up configuration resource reading main config file and module config files.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initConfiguration()
	{
		$configFile	= static::$defaultPaths['config'].static::$configFile;							//  get config file @todo remove this old way
		if( !empty( $this->options['configFile'] ) )												//  get config file from options @todo enforce this new way
			$configFile	= $this->options['configFile'];												//  get config file from options
		if( !file_exists( $configFile ) ){															//  config file not found
			$message	= sprintf( 'Config file "%s" not existing', $configFile );
			throw new Exception( $message );														//  quit with exception
		}
		$data			= parse_ini_file( $configFile, FALSE );										//  parse configuration file (without section support)
		ksort( $data );
		$this->config	= new Dictionary( $data );													//  create dictionary from array

		/*  -- DEFAULT PATHS  --  */
		foreach( static::$defaultPaths as $key => $value ){											//  iterate default paths
			if( !$this->config->has( 'path.'.$key ) ){												//  path is not set in config
				if( 0 !== strlen( trim( $value ) ) )
					$value	= rtrim( trim( $value ), '/' ).'/';
				$this->config->set( 'path.'.$key, $value );											//  set path in config (in memory)
			}
		}

		$this->detectMode();
		$this->runtime->reach( 'env: config', 'Finished setup of base app configuration.' );
	}

	/**
	 *	Sets up database support.
	 *	Calls hook Env::initDatabase to get resource.
	 *	Calls hook Database::init if resource is available and retrieved
	 *	@access		protected
	 *	@return		void
	 */
	protected function initDatabase()
	{
		$data	= (object) array( 'managers' => array() );
		$this->captain->callHook( 'Env', 'initDatabase', $this, $data );									//  call events hooked to database init
		if( count( $data->managers ) ){
			$this->database	= current( $data->managers );
			$this->modules->callHook( 'Database', 'init', $this->database );									//  call events hooked to database init
		}
		$this->runtime->reach( 'env: database', 'Finished setup of database connection.' );
	}

	/**
	 *	@todo  		why not keep the resource object instead of reflected class list? would need refactoring of resource and related modules, thou...
	 *	@todo  		extract to resource module: question is where to store the resource? in env again?
	 *	@todo  		to be deprecated in 0.9: please use module Resource_Disclosure instead
	 */
	protected function initDisclosure()
	{
		$disclosure	= new DisclosureResource( array() );
		$this->disclosure	= $disclosure->reflect( 'classes/Controller/', array( 'classPrefix' => 'Controller_' ) );
		$this->runtime->reach( 'env: disclosure', 'Finished setup of self disclosure handler.' );
	}

	/**
	 *	@todo  		extract to resource module
	 *	@todo  		extract to resource module: question is where to store the resource? in env again?
	 *	@todo  		to be deprecated in 0.9: please use module Resource_Disclosure instead
	 */
	protected function initLog()
	{
		$this->log	= new LogResource( $this );
	}

	protected function initLanguage()
	{
		$this->language		= new LanguageResource( $this );
		$this->runtime->reach( 'env: language' );
	}

	protected function initLogic()
	{
		$this->logic		= new LogicPoolResource( $this );
		$this->runtime->reach( 'env: logic', 'Finished setup of logic pool.' );
	}

	/**
	 *	@access		protected
	 *	@return		void
	 *	@todo		remove support for base_config::module.acl.public
	 */
	protected function initModules()
	{
		$this->runtime->reach( 'env: initModules: start', 'Started setup of modules.' );
		$public	= array();
		if( strlen( trim( $this->config->get( 'module.acl.public' ) ) ) ){
			Deprecation::getInstance()
				->setErrorVersion( '0.8.7.2' )
				->setExceptionVersion( '0.8.9' )
				->message( 'Using config::module.acl.public is deprecated. Use ACL instead!' );
			$public	= explode( ',', $this->config->get( 'module.acl.public' ) );					//  get current public link list
		}

		$this->modules	= new LocalModuleLibraryResource( $this );
		$this->modules->stripFeatures( array(
			'sql',
			'versionLog',
			'companies',
			'authors',
			'licenses',
			'price',
			'file',
			'uri',
			'category',
			'description',
		) );

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
		if( !( $this instanceof RemoteEnvironment ) )
			$this->modules->callHook( 'Env', 'initModules', $this );								//  call related module event hooks
		$this->config->set( 'module.acl.public', implode( ',', array_unique( $public ) ) );			//  save public link list
		$this->runtime->reach( 'env: initModules: end', 'Finished setup of modules.' );
	}

	protected function initPhp()
	{
		$this->php	= new PhpResource( $this );
	}

	protected function initRuntime()
	{
		$this->runtime	= new RuntimeResource( $this );
		$this->runtime->reach( 'env: initRuntime', 'Finished setup of profiler.' );
	}

	public function offsetExists( $key )
	{
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

	public function remove( string $key ): self
	{
		$this->$key	= NULL;
		return $this;
	}

	public function set( string $key, $object ): self
	{
		if( !is_object( $object ) ){
			$message	= 'Given resource "%1$s" is not an object';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		if( !preg_match( '/^\w+$/', $key ) ){
			$message	= 'Invalid resource key "%1$s"';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		$this->$key	= $object;
		return $this;
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
	 *	@return		self
	 */
	public function setPath( string $key, string $path, bool $override = TRUE, $strict = TRUE ): self
	{
		if( $this->hasPath( $key ) && !$override && $strict )
			throw new RuntimeException( 'Path "'.$key.'" is already set' );
		$this->config->set( 'path.'.$key, $path );
		return $this;
	}
}
