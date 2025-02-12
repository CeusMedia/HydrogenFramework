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

use CeusMedia\Cache\SimpleCacheInterface;
use CeusMedia\Cache\SimpleCacheFactory;
use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Exception\Deprecation as DeprecationException;
use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction as AbstractAclResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\AllPublic as AllPublicAclResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Configuration as ConfigurationResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Disclosure as DisclosureResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Log as LogResource;
use CeusMedia\HydrogenFramework\Environment\Resource\LogicPool as LogicPoolResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Config as ModuleConfig;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface;
use CeusMedia\HydrogenFramework\Environment\Resource\Php as PhpResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Runtime as RuntimeResource;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;

use ArrayAccess;
use DomainException;
use Exception;
use InvalidArgumentException;
use RangeException;
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
class Environment implements ArrayAccess
{
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

	/**	@var	array						$defaultPaths	Map of default paths to extend base configuration */
	public static array $defaultPaths		= [
		'classes'	=> 'classes/',
		'config'	=> 'config/',
		'logs'		=> 'logs/',
		'templates'	=> 'templates/',
	];

	public static ?string $timezone			= NULL;


	/**	@var	string|NULL					$path			Absolute folder path of application */
	public ?string $path					= NULL;

	/**	@var	PhpResource					$php			Instance of PHP environment collection */
	public PhpResource $php;

	/**	@var	string						$uri			Application URI (absolute local path) */
	public string $uri						= '';

	/**	@var	string						$url			Application URI */
	public string $url						= '';

	/** @var	string						$version		Framework version */
	public string $version;


	/** @var	AbstractAclResource			$acl			Implementation of access control list */
	protected AbstractAclResource $acl;

	/**	@var	SimpleCacheInterface		$cache			Instance of simple cache adapter */
	protected SimpleCacheInterface $cache;

	/**	@var	CaptainResource				$captain		Instance of captain */
	protected CaptainResource $captain;

	/**	@var	ConfigurationResource		$config			Configuration Object */
	protected ConfigurationResource $config;

	/**	@var	object|NULL					$database		Database Connection Object */
	protected ?object $database				= NULL;

	/**	@var	LanguageResource			$language		Language support object */
	protected LanguageResource $language;

	/**	@var	LogResource|NULL			$log			Log support object */
	protected ?LogResource $log				= NULL;

	/**	@var	array						$disclosure		Map of classes ready to reflect */
	protected array $disclosure				= [];

	/**	@var	LogicPoolResource			$logic			Pool for logic class instances */
	protected LogicPoolResource $logic;

	/**	@var	integer						$mode			Environment mode (dev,test,live,...) */
	protected int $mode						= 0;

	/** @var	MessengerResource|NULL		$messenger		Messenger Object */
	protected ?MessengerResource $messenger	= NULL;

	/**	@var	LibraryInterface			$modules		Handler for installed modules */
	protected LibraryInterface $modules;

	/**	@var	array						$options		Set options to override static properties */
	protected array $options				= [];

	/**	@var	RuntimeResource				$runtime		Runtime Object */
	protected RuntimeResource $runtime;

	/**	@var	HttpRequest|Dictionary		$request		Request Object */
	private HttpRequest|Dictionary $request;

	/**	@var	Dictionary					$session		Session Object */
	private Dictionary $session;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@param		array		$options 			@todo: doc
	 *	@param		boolean		$isFinal			Flag: there is no extending environment class, default: TRUE
	 *	@return		void
	 *	@todo		possible error: call to onInit is to soon of another environment if existing
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function __construct( array $options = [], bool $isFinal = TRUE )
	{
//		$this->modules->callHook( 'Env', 'constructStart', $this );									//  call module hooks for end of env construction
		$this->detectFrameworkVersion();

		if( !isset( static::$defaultPaths['cache'] ) )
			static::$defaultPaths['cache']	= sys_get_temp_dir().'/cache/';

		$this->options		= $options;																//  store given environment options
		$this->path			= rtrim( $options['pathApp'] ?? getCwd(), '/' ) . '/';	//  detect application path
		$this->uri			= rtrim( $options['uri'] ?? getCwd(), '/' ) . '/';															//  detect application base URI

		$this->setTimeZone();

		$this->session	= new Dictionary();
		$this->initRuntime();																		//  setup runtime clock
		$this->initConfiguration();																	//  setup configuration
		$this->detectMode();
		$this->initLog();																			//  setup logger
		$this->initPhp();																			//  setup PHP environment
		$this->initCaptain();																		//  setup captain
		$this->initLogic();																			//  setup logic pool
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
//		$this->initLanguage();

		if( NULL !== $this->log ){
			$strategies	= [LogResource::STRATEGY_MODULE_HOOKS, ...$this->log->getStrategies()];		//  prepend hook based logging strategy
			$this->log->setStrategies( array_unique( $strategies ) );								//  set new logging strategies list
		}

		if( !$isFinal )
			return;
		$this->captain->callHook( 'Env', 'constructEnd', $this );									//  call module hooks for end of env construction
		$this->__onInit();																			//  default callback for construction end
	}

	/**
	 *	@param		string		$key
	 *	@return		mixed|null
	 *	@throws		Exception
	 */
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
	 *	@param		string		$key
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 *	@throws		DomainException			if no resource is registered by given key
	 *	@throws		DeprecationException	if key is 'clock', since replaced by 'runtime'
	 */
	public function get( string $key, bool $strict = TRUE ): object|NULL
	{
		if( isset( $this->$key ) )
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
			throw new DomainException( sprintf( $message, $key ) );
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

	/**
	 *	@return		SimpleCacheInterface
	 */
	public function getCache(): SimpleCacheInterface
	{
		return $this->cache;
	}

	/**
	 *	@return		CaptainResource
	 */
	public function getCaptain(): CaptainResource
	{
		return $this->captain;
	}

	/**
	 *	@return		RuntimeResource
	 *	@throws		Exception
	 *	@deprected	use getRuntime instead
	 */
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
	 *	@return		ConfigurationResource
	 */
	public function getConfig(): ConfigurationResource
	{
		return $this->config;
	}

	/**
	 *	@return		object|NULL
	 */
	public function getDatabase(): ?object
	{
		return $this->database;
	}

	/**
	 * @return array|null
	 * @throws Exception
	 * @deprecated
	 */
	public function getDisclosure(): ?array
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.8' )
			->setExceptionVersion( '0.9' )
			->message( 'Environment::getDisclosure is deprecated. Use module Resource_Disclosure instead' );
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

	/**
	 *	@return		LogResource|NULL
	 */
	public function getLog(): ?LogResource
	{
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
	 *	Returns Messenger Object.
	 *	@access		public
	 *	@return		MessengerResource|NULL
	 */
	public function getMessenger(): ?MessengerResource
	{
		return $this->messenger;
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
	 *	Returns initially set up handler for (usually local) module library.
	 *	@access		public
	 *	@return		LibraryInterface
	 */
	public function getModules(): LibraryInterface
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
	public function getPath( string $key, bool $strict = TRUE ): ?string
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
		/** @var PhpResource $resource */
		$resource	= $this->get( 'php' );
		return $resource;
	}

	/**
	 *	@return		HttpRequest|Dictionary
	 */
	public function getRequest(): HttpRequest|Dictionary
	{
		return $this->request ?? new Dictionary();
	}

	/**
	 *	@return		RuntimeResource
	 */
	public function getRuntime(): RuntimeResource
	{
		/** @var RuntimeResource $resource */
		$resource	= $this->get( 'runtime' );
		return $resource;
	}

	/**
	 *	@return		PartitionSession|Dictionary
	 */
	public function getSession(): PartitionSession|Dictionary
	{
		return $this->session;
	}

	/**
	 *	Indicates whether a resource is an available object by its access method key.
	 *	@access		public
	 *	@param		string		$key		Resource access method key, i.e. session, language, request
	 *	@return		boolean
	 */
	public function has( string $key ): bool
	{
		$method		= 'get'.ucFirst( $key );
		$callable	= [$this, $method];
		if( is_callable( $callable ) )
			if( is_object( call_user_func( $callable ) ) )
				return TRUE;
		if( $this->$key ?? FALSE )
			return TRUE;
		return FALSE;
	}

	/**
	 *	@todo this is totally outdated - refactor if possible
	 *	@deprecated
	 */
	public function hasAcl(): ?string
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.9' )
			->setExceptionVersion( '0.9.1' )
			->message( 'Environment::hasAcl is deprecated' );
		return $this->getConfig()->get( 'module.roles' );
	}

	/**
	 *	@param		string		$moduleId
	 *	@return		bool
	 */
	public function hasModule( string $moduleId ): bool
	{
		return $this->hasModules() && $this->modules->has( $moduleId );
	}

	/**
	 *	@return		bool
	 */
	public function hasModules(): bool
	{
		return 0 !== $this->modules->count();
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

	public function isInDevMode(): bool
	{
		return self::MODE_DEV === ( $this->mode & self::MODE_DEV );
	}

	public function isInLiveMode(): bool
	{
		return self::MODE_LIVE === ( $this->mode & self::MODE_LIVE );
	}

	public function isInStageMode(): bool
	{
		return self::MODE_STAGE === ( $this->mode & self::MODE_STAGE );
	}

	public function isInTestMode(): bool
	{
		return self::MODE_TEST === ( $this->mode & self::MODE_TEST );
	}

	/**
	 *	@param		mixed		$offset
	 *	@return		bool
	 */
	public function offsetExists( mixed $offset ): bool
	{
//		return property_exists( $this, $key );														//  PHP 5.3
		return isset( $this->{strval( $offset )} );																//  PHP 5.2
	}

	/**
	 *	@param		mixed		$offset
	 *	@return		object|NULL
	 *	@throws		Exception
	 */
	public function offsetGet( mixed $offset ): object|NULL
	{
		return $this->get( strval( $offset ) );
	}

	/**
	 *	@param		mixed		$offset
	 *	@param		mixed		$value
	 *	@return		void
	 */
	public function offsetSet( mixed $offset, mixed $value ): void
	{
		$this->set( strval( $offset ), $value );
	}

	public function offsetUnset( mixed $offset ): void
	{
		$this->remove( strval( $offset ) );
	}

	public function remove( string $key ): self
	{
		$this->$key	= NULL;
		return $this;
	}

	/**
	 *	@param		string		$key
	 *	@param		object		$object
	 *	@return		$this
	 */
	public function set( string $key, object $object ): self
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
	 *	Sets environment mode.
	 *	Disabled for productive environments aka environments in LIVE mode.
	 *	@param		int		$mode		One of ::MODES
	 *	@return		static
	 *	@throws		RuntimeException	if current environments is in LIVE mode
	 *	@throws		RangeException		if an invalid mode has been given
	 */
	public function setMode( int $mode ): static
	{
		if( $this->isInLiveMode() )
			throw new RuntimeException( 'Setting environment mode is disabled on productive environments' );
		if( !in_array( $mode, self::MODES, TRUE ) )
			throw new RangeException( 'Invalid mode' );
		$this->mode	= $mode;
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
	 *	@throws		RuntimeException
	 */
	public function setPath( string $key, string $path, bool $override = TRUE, bool $strict = TRUE ): self
	{
		if( $this->hasPath( $key ) && !$override && $strict )
			throw new RuntimeException( 'Path "'.$key.'" is already set' );
		$this->config->set( 'path.'.$key, $path );
		return $this;
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

	/**
	 *	Detects version of Hydrogen framework by reading its INI file.
	 *	Sets found version on environment.
	 *
	 *	@return		static
	 */
	protected function detectFrameworkVersion(): static
	{
		/** @var array $frameworkConfig */
		$frameworkConfig	= parse_ini_file( dirname( __DIR__ ).'/hydrogen.ini' );
		$this->version		= $frameworkConfig['version'];
		return $this;
	}

	protected function detectMode(): static
	{
		/** @var array $modes */
		$modes	= preg_split( '/[_.:;>#@\/-]/', strtolower( $this->config->get( 'app.mode', 'production' ) ) );
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
		return $this;
	}

	/**
	 *	Initialize remote access control list if roles module is installed.
	 *	Calls hook and applies return class name. Otherwise, use all-public handler.
	 *	@access		protected
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function initAcl(): static
	{
		$type		= AllPublicAclResource::class;
		if( $this->hasModules() ){																	//  module support and modules available
			$payload	= ['className' => NULL];
			$isHandled	= $this->getCaptain()->callHook( 'Env', 'initAcl', $this, $payload );	//  call related module event hooks
			if( $isHandled && NULL !== $payload['className'] )
				$type	= $payload['className'];
		}
		/** @var Abstraction $acl */
		$acl	= ObjectFactory::createObject( $type, array( $this ) );
		$this->acl	= $acl;

		$linksPublic		= [];
		$linksPublicOutside	= [];
		$linksPublicInside	= [];
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
		return $this;
	}

	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function initCache(): static
	{
		$this->cache	= SimpleCacheFactory::createStorage('Noop' );
		$this->modules->callHook( 'Env', 'initCache', $this );						//  call related module event hooks
		$this->runtime->reach( 'env: initCache', 'Finished setup of cache' );
		return $this;
	}

	protected function initCaptain(): static
	{
		$this->captain	= new CaptainResource( $this );
		$this->runtime->reach( 'env: initCaptain', 'Finished setup of event handler.' );
		return $this;
	}

	/**
	 *	@return		static
	 *	@throws		DeprecationException
	 */
	protected function initClock(): static
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Use initRuntime() instead' );
		$this->initRuntime();
		return $this;
	}

	/**
	 *	Sets up configuration resource and loads main config file.
	 *	@access		protected
	 *	@return		static
	 *	@throws		FileNotExistingException
	 *	@throws		RuntimeException
	 */
	protected function initConfiguration(): static
	{
		$this->config	= new ConfigurationResource( $this );
		$this->config->loadFile( $this->options['configFile'] ?? NULL );
		$this->runtime->reach( 'env: config', 'Finished setup of base app configuration.' );
		return $this;
	}

	/**
	 *	Sets up database support.
	 *	Calls hook Env::initDatabase to get resource.
	 *	Calls hook Database::init if resource is available and retrieved
	 *	@access		protected
	 *	@return		static
	 *	@todo		implement database connection pool/manager
	 *	@throws		ReflectionException
	 */
	protected function initDatabase(): static
	{
		$data	= ['managers' => []];
		$this->captain->callHook( 'Env', 'initDatabase', $this, $data );									//  call events hooked to database init
		if( count( $data['managers'] ) ){
			$this->database	= current( $data['managers'] );
			$this->modules->callHook( 'Database', 'init', $this->database );									//  call events hooked to database init
		}
		$this->runtime->reach( 'env: database', 'Finished setup of database connection.' );
		return $this;
	}

	/**
	 *	@access		protected
	 *	@return		static
	 *	@todo		why not keep the resource object instead of reflected class list? would need refactoring of resource and related modules, thou...
	 *	@todo		extract to resource module: question is where to store the resource? in env again?
	 *	@todo		to be deprecated in 0.9: please use module Resource_Disclosure instead
	 */
	protected function initDisclosure(): static
	{
		$disclosure			= new DisclosureResource( [] );
		$this->disclosure	= $disclosure->reflect( 'classes/Controller/', ['classPrefix' => 'Controller_'] );
		$this->runtime->reach( 'env: disclosure', 'Finished setup of self disclosure handler.' );
		return $this;
	}

	/**
	 *	Creates a logger factory and log channels depending on configuration.
	 *	Uses several strategies to report to different or multiple log targets.
	 * 	Strategies defined in framework environment resource:
	 *		- APP_DEFAULT		= enqueue to file 'logs/app.log'
	 *		- APP_TYPED			- enqueue to file 'logs/app.TYPE.log' for types as info, note, warn, error or exception
	 *		- MODULE_HOOKS		- call default module hooks for specific handling
	 *		- CUSTOM_HOOKS		- call custom module hooks for specific handling
	 *		- CUSTOM_CALLBACK	- call injected method, for testing
	 *		- MEMORY			- log in memory, for testing
	 *
	 *	@access		protected
	 *	@return		static
	 */
	protected function initLog(): static
	{
		$this->log	= new LogResource( $this );
		$this->log->setStrategies( [LogResource::STRATEGY_APP_DEFAULT] );
		return $this;
	}

	protected function initLanguage(): static
	{
		$this->language		= new LanguageResource( $this );
		$this->runtime->reach( 'env: language' );
		return $this;
	}

	protected function initLogic(): static
	{
		$this->logic		= new LogicPoolResource( $this );
		$this->runtime->reach( 'env: logic', 'Finished setup of logic pool.' );
		return $this;
	}

	/**
	 *	Sets up a handler for (usually local) module library.
	 *	@access		protected
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		FileNotExistingException	if strict and file is not existing or given path is not a file
	 *	@throws		IoException					if strict and file is not readable
	 *	@todo		remove support for base_config::module.acl.public
	 */
	protected function initModules(): static
	{
		$this->runtime->reach( 'env: initModules: start', 'Started setup of modules.' );
		$public	= [];
		if( strlen( trim( $this->config->get( 'module.acl.public', '' ) ) ) ){
			Deprecation::getInstance()
				->setErrorVersion( '0.8.7.2' )
				->setExceptionVersion( '0.8.9' )
				->message( 'Using config::module.acl.public is deprecated. Use ACL instead!' );
			$public	= explode( ',', $this->config->get( 'module.acl.public' ) );					//  get current public link list
		}

		$this->modules	= new LocalModuleLibraryResource( $this );
		$this->modules->stripFeatures( [
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
		] );

		foreach( $this->modules->getAll() as $moduleId => $module ){								//  iterate all local app modules
			$prefix	= 'module.'.strtolower( $moduleId );											//  build config key prefix of module
			$this->config->set( $prefix, TRUE );													//  enable module in configuration
			foreach( $module->config as $key => $value ){											//  iterate module configuration pairs
				if( $value instanceof ModuleConfig && NULL !== $value->type ){						//  is module config definition object
					/** @var  $value */
					@settype( $value->value, $value->type );										//  cast value by set type
					$this->config->set( $prefix.'.'.$key, $value->value );							//  set configuration pair
				}
				else																				//  legacy @todo remove
					$this->config->set( $prefix.'.'.$key, $value );									//
			}

			foreach( $module->links as $link ){														//  iterate module links
				if( $link->access == "public" ){													//  link is public
					$link->path	= $link->path ?: 'index/index';
					$path		= str_replace( '/', '_', $link->path );									//  get link path
					if( !in_array( $path, $public ) )												//  link is not in public link list
						$public[]	= $path;														//  add link to public link list
				}
			}
		}
		if( !( $this instanceof RemoteEnvironment ) )
			$this->modules->callHook( 'Env', 'initModules', $this );								//  call related module event hooks
		$this->config->set( 'module.acl.public', implode( ',', array_unique( $public ) ) );			//  save public link list
		$this->runtime->reach( 'env: initModules: end', 'Finished setup of modules.' );
		return $this;
	}

	protected function initPhp(): static
	{
		$this->php	= new PhpResource( $this );
		return $this;
	}

	protected function initRuntime(): static
	{
		$this->runtime	= new RuntimeResource( $this );
		$this->runtime->reach( 'env: initRuntime', 'Finished setup of profiler.' );
		return $this;
	}

	/**
	 *	Sets time zone by given static time zone key.
	 *	Defaults to system time zone.
	 *
	 *	@return		static
	 */
	protected function setTimeZone(): static
	{
		date_default_timezone_set( @date_default_timezone_get() );									//  avoid having no timezone set
		if( !empty( static::$timezone ) )															//  a timezone has be set externally before
			date_default_timezone_set( static::$timezone );											//  set this timezone
		return $this;
	}
}
