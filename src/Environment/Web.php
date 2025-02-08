<?php /** @noinspection PhpUnused */
/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Setup for Resource Environment for Hydrogen Applications.
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
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPageFrame;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Features\Cache as CacheFeature;
use CeusMedia\HydrogenFramework\Environment\Features\ConfigByIni as ConfigByIniFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Cookie as CookieFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Page as PageFeature;
use CeusMedia\HydrogenFramework\Environment\Features\MessengerBySession as MessengerBySessionFeature;
use CeusMedia\HydrogenFramework\Environment\Features\RequestByHttp as RequestByHttpFeature;
use CeusMedia\HydrogenFramework\Environment\Features\ResponseByHttp as ResponseByHttpFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Router as RouterFeature;
use CeusMedia\HydrogenFramework\Environment\Features\SelfDetectionByHttp as SelfDetectionByHttpFeature;
use CeusMedia\HydrogenFramework\Environment\Features\SessionByHttpPartition as SessionFeature;

use Exception;
use InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;
use ReflectionException;

/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			extend from (namespaced) Environment after all modules are migrated to 0.9
 */
class Web extends Environment
{
	use CacheFeature;
	use ConfigByIniFeature;
	use CookieFeature;
	use PageFeature;
	use RequestByHttpFeature;
	use ResponseByHttpFeature;
	use MessengerBySessionFeature;
	use RouterFeature;
	use SelfDetectionByHttpFeature;
	use SessionFeature;

	public static string $configKeyBaseHref	= 'app.base.url';

	/**	@var	array					$defaultPaths	Map of default paths to extend base configuration */
	public static array $defaultPaths			= [
		'config'	=> 'config/',
		'classes'	=> 'classes/',
		'contents'	=> 'contents/',
		'images'	=> 'contents/images/',
		'locales'	=> 'contents/locales/',
		'scripts'	=> 'contents/scripts/',
		'themes'	=> 'contents/themes/',
		'logs'		=> 'logs/',
		'templates'	=> 'templates/',
	];

	protected array $resourcesToClose	= [];

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@param		array		$options
	 *	@return		void
	 *	@throws		InvalidArgumentException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function __construct( array $options = [], bool $isFinal = TRUE )
	{
		try{
//			parent::__construct( $options, FALSE );
			$this->runBaseEnvConstruction( $options, $isFinal && FALSE );
			$this->detectSelf( !( $options['isTest'] ?? FALSE ) );
			$this->initSession();																	//  setup session support
			$this->initMessenger();																	//  setup user interface messenger
			$this->initCookie();																	//  setup cookie support
			$this->initRequest();																	//  setup HTTP request handler
			$this->initResponse();																	//  setup HTTP response handler
			$this->initRouter();																	//  setup request router
			$this->initLanguage();																	//  setup language support
			$this->initPage();																		//
			$this->initAcl();																		//
			$this->modules->callHook( 'Env', 'constructEnd', $this );					//  call module hooks for end of env construction
			$this->__onInit();																		//  default callback for construction end
			$this->runtime->reach( 'Environment (Web): construction end' );					//  log time of construction
		}
		catch( Exception $e ){
			if( getEnv( 'HTTP_HOST' ) )
				print( HtmlExceptionPage::render( $e ) );
			else{
				print( $e->getMessage().PHP_EOL );
				print( $e->getTraceAsString().PHP_EOL.PHP_EOL );
			}
			exit;
		}
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
		parent::close( array_merge( [																//  delegate closing with these resources, too
			'request',																				//  HTTP request handler
			'response',																				//  HTTP response handler
			'session',																				//  HTTP session handler
			'messenger',																			//  application message handler
			'language',																				//  language handler
		], array_values( $additionalResources ) ), $keepAppAlive );									//  add additional resources and carry exit flag
	}

	/**
	 *	Redirects to given URI, allowing URIs external to current application.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	Alias for restart with parameters $allowForeignHost set to TRUE.
	 *	Similar to: $this->restart( 'http://foreign.tld/', NULL, TRUE );
	 *
	 *	HTTP status will be 200 or second parameter.
	 *
	 *	@access		public
	 *	@param		string			$uri				URI to request, may be external
	 *	@param		integer|NULL	$status				HTTP status code to send, default: NULL -> 200
	 *	@return		void
	 *	@todo		check for better HTTP status
	 */
	public function relocate( string $uri, int $status = NULL ): void
	{
		$this->restart( $uri, $status, TRUE );
	}

	/**
	 *	Redirects by requesting a URI.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	By default, redirect URIs are request path within the current application, e.g. "./[CONTROLLER]/[ACTION]"
	 *	ATTENTION: For browser compatibility local paths should start with "./"
	 *
	 *	If seconds parameter is set to TRUE, redirects to a path inside the current controller.
	 *	Therefore, the given URI needs to be a path inside the current controller.
	 *	This would look like this: $this->restart( '[ACTION]', TRUE );
	 *	Of course you can append actions arguments and parameters.
	 *
	 *	If third parameter is set to a valid HTTP status code, the code and its HTTP status text will be set for response.
	 *
	 *	If forth parameter is set to TRUE, redirects to URIs outside the current domain are allowed.
	 *	This would look like this: $this->restart( 'https://foreign.tld/', FALSE, NULL, TRUE );
	 *	There is a shorter alias: $this->relocate( 'https://foreign.tld/' );
	 *
	 *	@access		public
	 *	@param		string|NULL		$uri				URI to request
	 *	@param		integer|NULL	$status				HTTP status code to send, default: NULL -> 200
	 *	@param		boolean			$allowForeignHost	Flag: allow redirection outside application base URL, default: no
	 *	@param		integer			$modeFrom			How to handle FROM parameter from request or for new request, not handled atm
	 *	@return		void
	 *	@link		https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection HTTP status codes
	 *	@todo		implement automatic lookout for "from" request parameter
	 *	@todo		implement handling of FROM request parameter, see controller constants
	 *	@todo		concept and implement anti-loop {@see http://dev.(ceusmedia.de)/cmKB/?MTI}
	 */
	public function restart( ?string $uri = '', ?int $status = NULL, bool $allowForeignHost = FALSE, int $modeFrom = 0 ): void
	{
		$base	= '';
		if( !str_starts_with( $uri ?? '', 'http' ) ){												//  URI is not starting with HTTP scheme
			$base	= $this->getBaseUrl();															//  get application base URI
		}
		if( !$allowForeignHost ){																	//  redirect to foreign domain not allowed
			$scheme		= getEnv( 'HTTPS' ) ? 'https' : 'http';
			$hostFrom	= parse_url( $scheme.'://'.getEnv( 'HTTP_HOST' ), PHP_URL_HOST );		//  current host domain
			$hostTo		= parse_url( $base.$uri, PHP_URL_HOST );						//  requested host domain
			if( $hostFrom !== $hostTo ){															//  both are not matching
				$message	= 'Redirection to foreign host is not allowed.';						//  error message
				if( $this->has( 'messenger' ) ){													//  messenger is available
					$this->getMessenger()?->noteFailure( $message );								//  note message
//	broken			$this->modules->callHook( 'App', 'onException', $this );						//  call module hooks for end of env construction
					$this->restart( NULL, NULL, TRUE );						//  redirect to start
				}
				print( $message );																	//  otherwise print message
				exit;																				//  and exit
			}
		}
	#	$this->database->close();																	//  close database connection
	#	$this->session->close();																	//  close session
		if( $status )																				//  an HTTP status code is to be set
			HttpStatus::sendHeader( $status );														//  send HTTP status code header
		header( "Location: ".$base.$uri );													//  send HTTP redirect header

		$link	= HtmlTag::create( 'a', $base.$uri, array( 'href' => $base.$uri ) );
		$text	= HtmlTag::create( 'small', 'Redirecting to '.$link.' ...' );
		$page	= new HtmlPageFrame();
		$page->addMetaTag( 'http-equiv', 'refresh', '0; '.$base.$uri );
		$page->addBody( $text );
		print( $page->build() );
		exit;																						//  and exit application
	}

	//  --  PROTECTED  --  //

	/**
	 *	Sets up configuration resource reading main config file and module config files.
	 *	@access		protected
	 *	@return		static
	 */
	protected function initConfiguration(): static
	{
		parent::initConfiguration();

		/*  -- HOST BASED CONFIG  --  */
//		$configHost	= self::$defaultPaths['config'].getEnv( 'HTTP_HOST' ).'.ini';
		$configHost	= $this->config->get( 'path.config' ).getEnv( 'HTTP_HOST' ).'.ini';
		if( file_exists( $configHost ) ){															//  config file for host is existing
			$lines	= (array) parse_ini_file( $configHost );										//  read host config pairs
			foreach( $lines as $key => $value ){													//  iterate pairs
				if( preg_match( '/^[\d.]+$/', $value ) )										//  value is integer or float
					$value	= (float) $value;														//  convert value to numeric
				else if( in_array( strtolower( $value ), array( "yes", "true" ) ) )					//  value *means* yes
					$value	= TRUE;																	//  change value to boolean TRUE
				else if( in_array( strtolower( $value ), array( "no", "false" ) ) )					//  value *means* no
					$value	= FALSE;																//  change value to boolean FALSE
				$this->config->set( $key, $value );
			}
		}
//		$this->runtime->reach( 'env: config', 'Finished setup of web app configuration.' );
		return $this;
	}

	protected function registerResourceToClose( string $resourceKey ): static
	{
		$this->resourcesToClose[]	= $resourceKey;
		return $this;
	}

	/**
	 *	@param		array		$options 			@todo: doc
	 *	@param		boolean		$isFinal			Flag: there is no extending environment class, default: TRUE
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function runBaseEnvConstruction( array $options = [], bool $isFinal = TRUE ): void
	{
//		$this->modules->callHook( 'Env', 'constructStart', $this );									//  call module hooks for end of env construction
		$this->detectFrameworkVersion();

		$this->options		= $options;																//  store given environment options
		$this->path			= rtrim( $options['pathApp'] ?? getCwd(), '/' ) . '/';	//  detect application path
		$this->uri			= rtrim( $options['uri'] ?? getCwd(), '/' ) . '/';		//  detect application base URI

		$this->setTimeZone();
		$this->initRuntime();																		//  setup runtime clock
		$this->initConfiguration();																	//  setup configuration
		$this->initCaptain();																		//  setup captain
		$this->initModules();																		//  setup module support
		$this->initSession();
		$this->detectMode();
		$this->initLog();																			//  setup logger
		$this->initPhp();																			//  setup PHP environment
		$this->initLogic();																			//  setup logic pool
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
//		$this->initLanguage();

		if( !$isFinal )
			return;
		$this->captain->callHook( 'Env', 'constructEnd', $this );						//  call module hooks for end of env construction
		$this->__onInit();																			//  default callback for construction end
	}
}
