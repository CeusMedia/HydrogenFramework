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

	public $url;
	public $scheme;
	public $host;
	public $path;
	public $root;
	public $uri;

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
			parent::__construct( $options );
			$this->detectSelf();
			$this->initSession();																	//  setup session support
			$this->initMessenger();																	//  setup user interface messenger
			$this->initRequest();																	//  setup HTTP request handler
			$this->initResponse();																	//  setup HTTP response handler
			$this->initRouter();																	//  setup request router
	//		$this->initFieldDefinition();															//  --  FIELD DEFINITION SUPPORT  --  //
			$this->initLanguage();																	//  setup language support
			$this->initPage();																		//  
			$this->initAcl();
			$this->__onInit();																		//  
		}
		catch( Exception $e )
		{
			if( getEnv( 'HTTP_HOST' ) )
				die( UI_HTML_Exception_Page::render( $e ) );
			else
				
				remark( $e->getMessage() );
				remark( $e->getTraceAsString() );
				remark();
				exit;
		}
	}

	public function close()
	{
		unset( $this->dbc );																		//
		unset( $this->session );																	//
		unset( $this->request );																	//
		unset( $this->response );																	//
		unset( $this->messenger );																	//
		unset( $this->language );																	//
		parent::close();
	}

	protected function detectSelf(){
		$this->host		= getEnv( 'HTTP_HOST' );
		$this->root		= getEnv( 'DOCUMENT_ROOT' );
		$this->path		= dirname( getEnv( 'SCRIPT_NAME' ) ).'/';
		$this->uri		= $this->root.$this->path;
		$this->scheme	= getEnv( "HTTPS" ) ? 'https' : 'http';
		$this->url		= $this->scheme.'://'.$this->host.$this->path;
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

	protected function initMessenger()
	{
		$this->messenger	= new CMF_Hydrogen_Environment_Resource_Messenger( $this );
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
		$controller	= str_replace( '/', '-', $this->getRequest()->get( 'controller' ) );
		$action		= str_replace( '/', '-', $this->getRequest()->get( 'action' ) );
		$this->page	= new CMF_Hydrogen_Environment_Resource_Page( $this );
		$this->page->addBodyClass( 'module'.join( explode( ' ', ucwords( str_replace( '-', ' ', $controller ) ) ) ) );
		$this->page->addBodyClass( 'controller-'.$controller );
		$this->page->addBodyClass( 'action-'.$action );
		$this->page->addBodyClass( 'site-'.$controller.'-'.$action );
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
			$this->config->set( 'module.acl.inside', implode( ',', $inside ) );						//  save public link list
			$this->config->set( 'module.acl.outside', implode( ',', $outside ) );					//  save public link list
		}
		$this->clock->profiler->tick( 'env: session' );
	}
}
?>
