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
 *	@package		Hydrogen.Environment
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
 *	@package		Hydrogen.Environment
 *	@extends		CMF_Hydrogen_Environment_Abstract
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Net_HTTP_Request_Response
 *	@uses			Net_HTTP_Session
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Web extends CMF_Hydrogen_Environment_Abstract
{
	public static $classRouter			= 'CMF_Hydrogen_Environment_Router_Single';
	public static $configKeyBaseHref	= 'app.base.url';

	/**	@var	CMF_Hydrogen_Environment_Resource_Database_PDO	$dbc		Database Connection Object */
	protected $dbc;
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

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		ob_start();
		try
		{
			parent::__construct();
			$this->initSession();																		//  --  SESSION HANDLING  --  //
			$this->initMessenger();																		//  --  UI MESSENGER  --  //
			$this->initDatabase();																		//  --  DATABASE CONNECTION  --  //
			$this->initRequest();																		//  --  HTTP REQUEST HANDLER  --  //
			$this->initResponse();																		//  --  HTTP RESPONSE HANDLER  --  //
			$this->initRouter();																		//  --  HTTP REQUEST HANDLER  --  //
	//		$this->initFieldDefinition();																//  --  FIELD DEFINITION SUPPORT  --  //
			$this->initLanguage();																		//  --  LANGUAGE SUPPORT  --  //
			$this->initPage();
			$this->initAcl();
		}
		catch( Exception $e )
		{
			die( UI_HTML_Exception_Page::render( $e ) );
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

	public function getDatabase()
	{
		return $this->dbc;
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
	 *	@return		Server			Resource to communicate with chat server
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

	/**
	 *	Sets up database support.
	 *	@todo		implement pdo driver options (in config also)
	 */
	public function initDatabase()
	{
		$this->dbc	= new CMF_Hydrogen_Environment_Resource_Database_PDO( $this );
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
	}

	protected function initMessenger()
	{
		$this->messenger	= new CMF_Hydrogen_Environment_Resource_Messenger( $this );
	}

	/**
	 *	Initialize resource to communicate with chat server.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initPage( $pageJavaScripts = TRUE, $packStyleSheets = TRUE )
	{
		$this->page	= new CMF_Hydrogen_Environment_Resource_Page( $this );
		$this->page->setPackaging( $pageJavaScripts, $packStyleSheets );
		if( $this->getConfig()->get( self::$configKeyBaseHref ) )
			$this->page->setBaseHref( $this->getConfig()->get( self::$configKeyBaseHref ) );
	}

	protected function initRequest()
	{
		$this->request		= new Net_HTTP_Request();
		$this->request->fromEnv( $this->has( 'session' ) );
	}

	protected function initResponse()
	{
		$this->response	= new Net_HTTP_Response();
	}

	protected function initRouter( $routerClass = NULL )
	{
		$classRouter	= $routerClass ? $routerClass : self::$classRouter;
		$this->router	= Alg_Object_Factory::createObject( $classRouter, array( $this ) );
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
	}
}
?>