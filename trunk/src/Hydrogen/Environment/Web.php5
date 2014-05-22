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
 *	@package		Hydrogen.Environment
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
 *	@package		Hydrogen.Environment
 *	@extends		CMF_Hydrogen_Environment_Abstract
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Net_HTTP_Request_Response
 *	@uses			Net_HTTP_Session
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Web extends CMF_Hydrogen_Environment_Abstract
{
	public static $classRouter			= 'CMF_Hydrogen_Environment_Router_Single';
	public static $configKeyBaseHref	= 'app.base.url';

	/**	@var	Net_HTTP_Request_Receiver						$request	HTTP Request Object */
	protected $request;
	/**	@var	Net_HTTP_Request_Response						$request	HTTP Response Object */
	protected $response;
	/**	@var	CMF_Hydrogen_Environment_Router_Abstract		$router		Router Object */
	protected $router;
	/**	@var	Net_HTTP_Session								$session	Session Object */
	protected $session;
	/** @var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger	Messenger Object */
	protected $messenger;
	/** @var	CMF_Hydrogen_Environment_Resource_Language		$language	Language Object */
	protected $language;
	/**	@var	CMF_Hydrogen_Environment_Resource_Page			$page		Page Object */
	protected $page;

	/**	@var	string											$host		Detected HTTP host */
	public $host;
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

	public static $defaultPaths					= array(
		'classes'	=> 'classes/',
		'images'	=> 'images/',
		'locales'	=> 'locales/',
		'logs'		=> 'logs/',
		'scripts'	=> 'javascripts/',
		'styles'	=> 'styles/',
		'templates'	=> 'templates/',
		'themes'	=> 'themes/',
	);

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $options = array() )
	{
		ob_start();
		try
		{
			parent::__construct( $options, FALSE );
			$this->detectSelf();
			$this->initSession();																	//  setup session support
			$this->initMessenger();																	//  setup user interface messenger
			$this->initRequest();																	//  setup HTTP request handler
			$this->initResponse();																	//  setup HTTP response handler
			$this->initRouter();																	//  setup request router
	//		$this->initFieldDefinition();															//  --  FIELD DEFINITION SUPPORT  --  //
			$this->initLanguage();																	//  setup language support
			$this->initPage();																		//  
			$this->initAcl();																		//  
			$this->modules->callHook( 'Env', 'constructEnd', $this );								//  call module hooks for end of env construction
			$this->__onInit();																		//  default callback for construction end
			$this->clock->profiler->tick( 'Environment (Web): construction end' );					//  log time of construction
		}
		catch( Exception $e )
		{
			if( getEnv( 'HTTP_HOST' ) )
				die( UI_HTML_Exception_Page::render( $e ) );
			else{
				remark( $e->getMessage() );
				remark( $e->getTraceAsString() );
				remark();
				exit;
			}
		}
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
	 *	Detects basic environmental web and local information.
	 *	Notes global scheme, host, relative application path and absolute application URL.
	 *	Notes local document root path, relative application path and absolute application URI.
	 *	@access		protected
	 *	@return		void
	 *	@throws		CMF_Hydrogen_Environment_Exception	if application has been executed outside a valid web server environment or no HTTP host has been provided by web server
	 *	@throws		CMF_Hydrogen_Environment_Exception	if no document root path has been provided by web server
	 *	@throws		CMF_Hydrogen_Environment_Exception	if no script file path has been provided by web server
	 */
	protected function detectSelf()
	{
#		if( !getEnv( 'HTTP_HOST' ) )																//  application has been executed outside a valid web server environment or no HTTP host has been provided by web server
#			throw new CMF_Hydrogen_Environment_Exception( 'This application needs to be executed within by a web server' );
#		if( !getEnv( 'DOCUMENT_ROOT' ) )															//  no document root path has been provided by web server
#			throw new CMF_Hydrogen_Environment_Exception( 'Your web server needs to provide a document root path' );
#		if( !getEnv( 'SCRIPT_NAME' ) )																//  no script file path has been provided by web server
#			throw new CMF_Hydrogen_Environment_Exception( 'Your web server needs to provide the running scripts file path' );

		$path	= dirname( getEnv( 'SCRIPT_NAME' ) );												//  get requested working path
		$port	= getEnv( 'SERVER_PORT' );															//  get used HTTP port

		$this->host		= $host = getEnv( 'HTTP_HOST' );											//  note requested HTTP host name
		$this->port		= $port = $port == 80 ? '' : $port;											//  note requested HTTP port
		$this->root		= $root	= getEnv( 'DOCUMENT_ROOT' );										//  note document root of web server or virtual host
		$this->path		= $path = $path !== "/" ? $path.'/' : $path;								//  note requested working path
		$this->scheme	= getEnv( "HTTPS" ) ? 'https' : 'http';										//  note used URL scheme
		$this->url		= $this->scheme.'://'.$host.( $port ? ':'.$port : '' ).$path;				//  note calculated base application URI
		$this->uri		= $root.$path;																//  note calculated absolute base application path
	}

	/**
	 *	Returns Language Object.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Environment_Resource_Language
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 *	Returns Messenger Object.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Environment_Resource_Messenger
	 */
	public function getMessenger()
	{
		return $this->messenger;
	}

	/**
	 *	Get resource to communicate with chat server.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Environment_Resource_Page
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 *	Returns Router Object.
	 *	@access		public
	 *	@return		CMF_Hydrogen_Router_Abstract
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 *	Returns Request Object.
	 *	@access		public
	 *	@return		Net_HTTP_Request_Receiver
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 *	Returns HTTP Response Object.
	 *	@access		public
	 *	@return		Net_HTTP_Request_Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 *	Returns Session Object.
	 *	@access		public
	 *	@return		Net_HTTP_Session
	 */
	public function getSession()
	{
		return $this->session;
	}

/*	protected function initFieldDefinition()
	{
		$this->definition	= new CMF_Hydrogen_FieldDefinition(
			"config/",
			$this->config['config.use_cache'],
			$this->config['config.cache_path']
		);
		$this->definition->setChannel( "html" );
	}
*/
	protected function initLanguage()
	{
		$this->language		= new CMF_Hydrogen_Environment_Resource_Language( $this );
		$this->clock->profiler->tick( 'env: language' );
	}

	protected function initMessenger( $enabled = "auto" )
	{
		if( $enabled === "auto" )																	//  auto detect mode
			$enabled	= preg_match( "/html/", getEnv( 'HTTP_ACCEPT' ) );							//  enabled if HTML is requested
		$this->messenger	= new CMF_Hydrogen_Environment_Resource_Messenger( $this, $enabled );
		$this->clock->profiler->tick( 'env: messenger' );
	}

	/**
	 *	Initialize page frame resource.
	 *	@access		protected
	 *	@param		boolean		$pageJavaScripts	Flag: compress JavaScripts, default: TRUE
	 *	@param		boolean		$packStyleSheets	Flag: compress Stylesheet, default: TRUE 
	 *	@return		void
	 */
	protected function initPage( $pageJavaScripts = TRUE, $packStyleSheets = TRUE )
	{
		$this->page	= new CMF_Hydrogen_Environment_Resource_Page( $this );
		$this->page->setPackaging( $pageJavaScripts, $packStyleSheets );
		$this->page->setBaseHref( $this->getBaseUrl( self::$configKeyBaseHref ) );
		$this->page->applyModules();

		$words		= $this->getLanguage()->getWords( 'main' );
		if( is_array( $words ) && isset( $words['main']['title'] ) )
			$this->page->setTitle( $words['main']['title'] );
		$this->clock->profiler->tick( 'env: page' );
	}

	protected function initRequest()
	{
		$this->request		= new Net_HTTP_Request();
		$this->request->fromEnv( FALSE/*$this->has( 'session' )*/ );
		$this->clock->profiler->tick( 'env: request' );
	}

	protected function initResponse()
	{
		$this->response	= new Net_HTTP_Response();
		$this->clock->profiler->tick( 'env: response' );
	}

	protected function initRouter( $routerClass = NULL )
	{
		$classRouter	= $routerClass ? $routerClass : self::$classRouter;
		$this->router	= Alg_Object_Factory::createObject( $classRouter, array( $this ) );
		$this->clock->profiler->tick( 'env: router' );
	}

	protected function initSession( $keyPartitionName = NULL, $keySessionName = NULL )
	{
		$partitionName	= md5( getCwd() );
		$sessionName	= 'sid';
		if( $keyPartitionName && $this->config->get( $keyPartitionName ) )
			$partitionName	= $this->config->get( $keyPartitionName );
		if( $keySessionName && $this->config->get( $keySessionName ) )
			$sessionName	= $this->config->get( $keySessionName );

		$this->session	= new Net_HTTP_PartitionSession(
			$partitionName,
			$sessionName
		);

		$isInside	= (int) $this->session->get( 'userId' );
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
		$this->clock->profiler->tick( 'env: session' );
		if( $this->modules )
			$this->modules->callHook( 'Session', 'init', $this->session );
	}
}
?>
