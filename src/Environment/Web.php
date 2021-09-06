<?php
/**
 *	Setup for Resource Environment for Hydrogen Applications.
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
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Exception as EnvironmentException;
use CeusMedia\HydrogenFramework\Environment\Router\Single as SingleRouter;
use CeusMedia\HydrogenFramework\Environment\Router\Abstraction as AbstractRouter;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Page as PageResource;

use Alg_Object_Factory as ObjectFactory;
use Net_HTTP_Cookie as HttpCookie;
use Net_HTTP_Request as HttpRequest;
use Net_HTTP_Response as HttpResponse;
use Net_HTTP_PartitionSession as HttpPartitionSession;
use Net_HTTP_Status as HttpStatus;
use UI_HTML_Exception_Page as HtmlExceptionPage;
use UI_HTML_Tag as HtmlTag;
use UI_HTML_PageFrame as HtmlPageFrame;

use Exception;
use RuntimeException;

/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Web extends Environment
{
	public static $classRouter			= SingleRouter::class;

	public static $configKeyBaseHref	= 'app.base.url';

	/**	@var	HttpRequest								$request	HTTP Request Object */
	protected $request;

	/**	@var	HttpResponse								$response	HTTP Response Object */
	protected $response;

	/**	@var	AbstractRouter		$router		Router Object */
	protected $router;

	/**	@var	HttpPartitionSession						$session	Session Object */
	protected $session;

	/**	@var	HttpCookie									$cookie		Cookie Object */
	protected $cookie;

	/** @var	MessengerResource		$messenger	Messenger Object */
	protected $messenger;

	/** @var	LanguageResource		$language	Language Object */
	protected $language;

	/**	@var	PageResource			$page		Page Object */
	protected $page;

	/**	@var	string											$host		Detected HTTP host */
	public $host;

	/**	@var	int												$port		Detected HTTP port */
	public $port;

	/**	@var	string											$path		Detected HTTP path */
	public $path;

	/**	@var	string											$root		Detected  */
	public $root;

	/**	@var	string											$scheme		Detected  */
	public $scheme;

	/**	@var	string											$uri		Detected  */
	public $uri;

	/**	@var	string											$url		Detected application base URL */
	public $url;

	/**	@var	array											$defaultPaths	Map of default paths to extend base configuration */
	public static $defaultPaths				= array(
		'config'	=> 'config/',
		'classes'	=> 'classes/',
		'contents'	=> 'contents/',
		'images'	=> 'contents/images/',
		'locales'	=> 'contents/locales/',
		'scripts'	=> 'contents/scripts/',
		'themes'	=> 'contents/themes/',
		'logs'		=> 'logs/',
		'templates'	=> 'templates/',
	);

	protected $resourcesToClose					= array();

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $options = array() )
	{
		ob_start();
		try{
			parent::__construct( $options, FALSE );
			$this->detectSelf();
			$this->initSession();																	//  setup session support
			$this->initMessenger();																	//  setup user interface messenger
			$this->initCookie();																	//  setup cookie support
			$this->initRequest();																	//  setup HTTP request handler
			$this->initResponse();																	//  setup HTTP response handler
			$this->initRouter();																	//  setup request router
			$this->initLanguage();																	//  setup language support
			$this->initPage();																		//
			$this->initAcl();																		//
			$this->modules->callHook( 'Env', 'constructEnd', $this );								//  call module hooks for end of env construction
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
	public function close( array $additionalResources = array(), bool $keepAppAlive = FALSE )
	{
		$resources	= array(
			'session',																				//  HTTP session handler
			'request',																				//  HTTP request handler
			'response',																				//  HTTP response handler
			'messenger',																			//  application message handler
			'language',																				//  language handler
		);
		parent::close( $resources );																//  close environment and application execution
	}

	/**
	 *	Returns Cookie Object.
	 *	@access		public
	 *	@return		HttpCookie
	 *	@throws		RuntimeException		if cookie support has not been initialized
	 */
	public function getCookie(): HttpCookie
	{
		if( !is_object( $this->cookie ) )
			throw new RuntimeException( 'Cookie resource not initialized within environment' );
		return $this->cookie;
	}

	/**
	 *	Returns Messenger Object.
	 *	@access		public
	 *	@return		MessengerResource
	 */
	public function getMessenger(): ?MessengerResource
	{
		return $this->messenger;
	}

	/**
	 *	Get resource to communicate with chat server.
	 *	@access		public
	 *	@return		PageResource
	 */
	public function getPage(): PageResource
	{
		return $this->page;
	}

	/**
	 *	Returns Router Object.
	 *	@access		public
	 *	@return		AbstractRouter
	 */
	public function getRouter(): AbstractRouter
	{
		return $this->router;
	}

	/**
	 *	Returns Request Object.
	 *	@access		public
	 *	@return		HttpRequest
	 */
	public function getRequest(): HttpRequest
	{
		return $this->request;
	}

	/**
	 *	Returns HTTP Response Object.
	 *	@access		public
	 *	@return		HttpResponse
	 */
	public function getResponse(): HttpResponse
	{
		return $this->response;
	}

	/**
	 *	Returns Session Object.
	 *	@access		public
	 *	@return		HttpPartitionSession
	 */
	public function getSession(): HttpPartitionSession
	{
		return $this->session;
	}

	/**
	 *	Redirects by setting different Controller and Action.
	 *	Attention: This will *NOT* effect the URL in browser nor need cURL requests to allow forwarding.
	 *	Attention: This is not recommended, please use restart in favour.
	 *	@access		public
	 *	@param		string		$controller		Controller to be called, default: index
	 *	@param		string		$action			Action to be called, default: index
	 *	@param		array		$arguments		List of arguments to add to URL
	 *	@param		array		$parameters		Map of additional parameters to set in request
	 *	@return		void
	 *	@deprecated	redirecting only works in hooks within dispatching, use restart in controllers
	 *	@todo		remove in 0.9 and handle todo in Hook::redirect
	 */
	public function redirect( string $controller = 'index', string $action = "index", array $arguments = array(), array $parameters = array() )
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.6.4' )
			->setExceptionVersion( '0.8.9' )
			->message( 'Redirecting is usable for hooks within dispatching, only. Please use restart instead!' );

		$request	= $this->getRequest();
		$request->set( '__controller', $controller );
		$request->set( '__action', $action );
		$request->set( '__arguments', $arguments );
		foreach( $parameters as $key => $value )
			if( !empty( $key ) )
				$request->set( $key, $value );
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
	 *	@param		string		$uri				URI to request, may be external
	 *	@param		integer		$status				HTTP status code to send, default: NULL -> 200
	 *	@return		void
	 *	@todo		kriss: check for better HTTP status
	 */
	public function relocate( string $uri, int $status = NULL )
	{
		$this->restart( $uri, $status, TRUE );
	}

	/**
	 *	Redirects by requesting a URI.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	By default, redirect URIs are are request path within the current application, eg. "./[CONTROLLER]/[ACTION]"
	 *	ATTENTION: For browser compatibility local paths should start with "./"
	 *
	 *	If seconds parameter is set to TRUE, redirects to a path inside the current controller.
	 *	Therefore the given URI needs to be a path inside the current controller.
	 *	This would look like this: $this->restart( '[ACTION]', TRUE );
	 *	Of course you can append actions arguments and parameters.
	 *
	 *	If third parameter is set to a valid HTTP status code, the code and its HTTP status text will be set for response.
	 *
	 *	If forth parameter is set to TRUE, redirects to URIs outside the current domain are allowed.
	 *	This would look like this: $this->restart( 'http://foreign.tld/', FALSE, NULL, TRUE );
	 *	There is a shorter alias: $this->relocate( 'http://foreign.tld/' );
	 *
	 *	@access		public
	 *	@param		string		$uri				URI to request
	 *	@param		integer		$status				HTTP status code to send, default: NULL -> 200
	 *	@param		boolean		$allowForeignHost	Flag: allow redirection outside application base URL, default: no
	 *	@param		integer		$modeFrom			How to handle FROM parameter from request or for new request, not handled atm
	 *	@return		void
	 *	@link		https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection HTTP status codes
	 *	@todo		kriss: implement automatic lookout for "from" request parameter
	 *	@todo		kriss: implement handling of FROM request parameter, see controller constants
	 *	@todo		kriss: concept and implement anti-loop {@see http://dev.(ceusmedia.de)/cmKB/?MTI}
	 */
	public function restart( ?string $uri = '', ?int $status = NULL, bool $allowForeignHost = FALSE, int $modeFrom = 0 )
	{
		$base	= "";
		if( !preg_match( "/^http/", $uri ) ){														//  URI is not starting with HTTP scheme
			$base	= $this->getBaseUrl();															//  get application base URI
		}
		if( !$allowForeignHost ){																	//  redirect to foreign domain not allowed
			$scheme		= getEnv( 'HTTPS' ) ? 'https' : 'http';
			$hostFrom	= parse_url( $scheme.'://'.getEnv( 'HTTP_HOST' ), PHP_URL_HOST );			//  current host domain
			$hostTo		= parse_url( $base.$uri, PHP_URL_HOST );									//  requested host domain
			if( $hostFrom !== $hostTo ){															//  both are not matching
				$message	= 'Redirection to foreign host is not allowed.';						//  error message
				if( $this->has( 'messenger' ) ){													//  messenger is available
					$this->getMessenger()->noteFailure( $message );									//  note message
					$this->modules->callHook( 'App', 'onException', $this );						//  call module hooks for end of env construction
					$this->restart( NULL, NULL, TRUE );												//  redirect to start
				}
				print( $message );																	//  otherwise print message
				exit;																				//  and exit
			}
		}
	#	$this->database->close();																	//  close database connection
	#	$this->session->close();																	//  close session
		if( $status )																				//  a HTTP status code is to be set
			HttpStatus::sendHeader( (int) $status );												//  send HTTP status code header
		header( "Location: ".$base.$uri );															//  send HTTP redirect header

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
	 *	Detects basic environmental web and local information.
	 *	Notes global scheme, host, relative application path and absolute application URL.
	 *	Notes local document root path, relative application path and absolute application URI.
	 *	@access		protected
	 *	@param		boolean		$strict			Flag: strict mode: throw exceptions
	 *	@return		void
	 *	@throws		EnvironmentException	if strict mode and application has been executed outside a valid web server environment or no HTTP host has been provided by web server
	 *	@throws		EnvironmentException	if strict mode and no document root path has been provided by web server
	 *	@throws		EnvironmentException	if strict mode and no script file path has been provided by web server
	 */
	protected function detectSelf( bool $strict = TRUE )
	{
		if( $strict ){
			if( !getEnv( 'HTTP_HOST' ) ){															//  application has been executed outside a valid web server environment or no HTTP host has been provided by web server
				throw new EnvironmentException(
					'This application needs to be executed within by a web server'
				);
			}
			if( !getEnv( 'DOCUMENT_ROOT' ) ){														//  no document root path has been provided by web server
				throw new EnvironmentException(
					'Your web server needs to provide a document root path'
				);
			}
			if( !getEnv( 'SCRIPT_NAME' ) ){															//  no script file path has been provided by web server
				throw new EnvironmentException(
					'Your web server needs to provide the running scripts file path'
				 );
			}
		}

		$this->scheme	= getEnv( "HTTPS" ) ? 'https' : 'http';										//  note used URL scheme
		$defaultPort	= $this->scheme === 'https' ? 443 : 80;										//  default port depends on HTTP scheme
		$this->host		= preg_replace( "/:[0-9]{2,5}$/", "", getEnv( 'HTTP_HOST' ) );				//  note requested HTTP host name without port
		$this->port		= getEnv( 'SERVER_PORT' ) == $defaultPort ? '' : getEnv( 'SERVER_PORT' );	//  note requested HTTP port
		$hostWithPort	= $this->host.( $this->port ? ':'.$this->port : '' );						//  append port if different from default port
		$this->root		= getEnv( 'DOCUMENT_ROOT' );												//  note document root of web server or virtual host
		$this->path		= preg_replace( "@^/$@", "", dirname( getEnv( 'SCRIPT_NAME' ) ) )."/";		//  note requested working path
		$this->url		= $this->scheme.'://'.$hostWithPort.$this->path;							//  note calculated base application URI
		$this->uri		= $this->root.$this->path;													//  note calculated absolute base application path
		$this->runtime->reach( 'env: self detection' );
	}

	/**
	 *	Sets up configuration resource reading main config file and module config files.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initConfiguration()
	{
		parent::initConfiguration();

		/*  -- HOST BASED CONFIG  --  */
//		$configHost	= self::$defaultPaths['config'].getEnv( 'HTTP_HOST' ).'.ini';
		$configHost	= $this->config->get( 'path.config' ).getEnv( 'HTTP_HOST' ).'.ini';
		if( file_exists( $configHost ) ){															//  config file for host is existing
			foreach( parse_ini_file( $configHost, FALSE ) as $key => $value ){						//  read host config pairs
				if( preg_match( '/^[0-9.]+$/', $value ) )											//  value is integer or float
					$value	= (float) $value;														//  convert value to numeric
				else if( in_array( strtolower( $value ), array( "yes", "true" ) ) )					//  value *means* yes
					$value	= TRUE;																	//  change value to boolean TRUE
				else if( in_array( strtolower( $value ), array( "no", "false" ) ) )					//  value *means* no
					$value	= FALSE;																//  change value to boolean FALSE
				$this->config->set( $key, $value );
			}
		}
//		$this->runtime->reach( 'env: config', 'Finished setup of web app configuration.' );
	}

	/**
	 *	Initialize cookie resource instance.
	 *	@access		protected
	 *	@return		void
	 *	@throws		RuntimeException			if cookie resource has not been initialized before
	 */
	protected function initCookie()
	{
		if( !$this->url )
			throw new RuntimeException( 'URL not detected yet, run detectSelf beforehand' );
		$this->cookie	= new HttpCookie(
			parse_url( $this->url, PHP_URL_PATH ),
			parse_url( $this->url, PHP_URL_HOST ),
			(bool) getEnv( 'HTTPS' )
		);
	}

	protected function initMessenger( $enabled = "auto" )
	{
		if( $enabled === "auto" )																	//  auto detect mode
			$enabled	= preg_match( "/html/", getEnv( 'HTTP_ACCEPT' ) );							//  enabled if HTML is requested
		$this->messenger	= new MessengerResource( $this, $enabled );
		$this->runtime->reach( 'env: messenger' );
	}

	/**
	 *	Initialize page frame resource.
	 *	@access		protected
	 *	@param		boolean		$pageJavaScripts	Flag: compress JavaScripts, default: TRUE
	 *	@param		boolean		$packStyleSheets	Flag: compress Stylesheet, default: TRUE
	 *	@return		void
	 */
	protected function initPage( bool $pageJavaScripts = TRUE, bool $packStyleSheets = TRUE )
	{
		$this->page	= new PageResource( $this );
		$this->page->setPackaging( $pageJavaScripts, $packStyleSheets );
		$this->page->setBaseHref( $this->getBaseUrl( self::$configKeyBaseHref ) );
		$this->page->applyModules();

		$words		= $this->getLanguage()->getWords( 'main', FALSE );
		if( is_array( $words ) && isset( $words['main']['title'] ) )
			$this->page->setTitle( $words['main']['title'] );
		$this->runtime->reach( 'env: page' );
	}

	/**
	 *	Initialize HTTP request resource instance.
	 *	Request data will be imported from given web server environment.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initRequest()
	{
		$this->request		= new HttpRequest();
		$this->request->fromEnv( FALSE/*$this->has( 'session' )*/ );
		$this->runtime->reach( 'env: request' );
	}

	/**
	 *	Initialize HTTP respone resource instance.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initResponse()
	{
		$this->response	= new HttpResponse();
		$this->runtime->reach( 'env: response' );
	}

	protected function initRouter( string $routerClass = NULL )
	{
		$classRouter	= $routerClass ? $routerClass : self::$classRouter;
		$this->router	= ObjectFactory::createObject( $classRouter, array( $this ) );
		$this->runtime->reach( 'env: router' );
	}

	protected function initSession( string $keyPartitionName = NULL, string $keySessionName = NULL )
	{
		$partitionName	= md5( getCwd() );
		$sessionName	= 'sid';
		if( $keyPartitionName && $this->config->get( $keyPartitionName ) )
			$partitionName	= $this->config->get( $keyPartitionName );
		if( $keySessionName && $this->config->get( $keySessionName ) )
			$sessionName	= $this->config->get( $keySessionName );

		$this->session	= new HttpPartitionSession(
			$partitionName,
			$sessionName
		);
		$this->runtime->reach( 'env: session: construction' );

		// @todo check if this old workaround public URL paths extended by module is still needed and remove
		$isInside	= (int) $this->session->get( 'auth_user_id' );
		$inside		= explode( ',', $this->config->get( 'module.acl.inside' ) );					//  get current inside link list
		$outside	= explode( ',', $this->config->get( 'module.acl.outside' ) );					//  get current outside link list
		if( $this->modules ){
			foreach( $this->modules->getAll() as $module ){
				foreach( $module->links as $link ){													//  iterate module links
					$link->path	= $link->path ? $link->path : 'index/index';
					if( $link->access == "inside" ){												//  link is inside public
						$path	= str_replace( '/', '_', $link->path );								//  get link path
						if( !in_array( $path, $inside ) )											//  link is not in public link list
							$inside[]	= $path;													//  add link to public link list
					}
					if( $link->access == "outside" ){												//  link is outside public
						$path	= str_replace( '/', '_', $link->path );								//  get link path
						if( !in_array( $path, $inside ) )											//  link is not in public link list
							$outside[]	= $path;													//  add link to public link list
					}
				}
			}
			$this->config->set( 'module.acl.inside', implode( ',', array_unique( $inside ) ) );		//  save public link list
			$this->config->set( 'module.acl.outside', implode( ',', array_unique( $outside ) ) );	//  save public link list
		}
		if( $this->modules )
			$this->modules->callHook( 'Session', 'init', $this->session );
		$this->runtime->reach( 'env: session: init done' );
	}

	protected function registerResourceToClose( string $resourceKey )
	{
		$this->resourcesToClose[]	= $resourceKey;
	}
}
