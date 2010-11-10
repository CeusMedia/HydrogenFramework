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
	/**	@var	Database_BaseConnection			$dbc		Database Connection Object */
	protected $dbc;
	/**	@var	Net_HTTP_Request_Receiver		$request	HTTP Request Object */
	protected $request;
	/**	@var	Net_HTTP_Request_Response		$request	HTTP Response Object */
	protected $response;
	/**	@var	Net_HTTP_Session				$session	Session Object */
	protected $session;
	/** @var	CMF_Hydrogen_Environment_Resource_Messenger	$messenger	Messenger Object */
	protected $messenger;
	/** @var	CMF_Hydrogen_Environment_Resource_Language	$language	Language Object */
	protected $language;
	/**	@var	CMF_Hydrogen_Environment_Resource_Page		$page		Page Object */
	protected $page;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->initSession();																		//  --  SESSION HANDLING  --  //
		$this->initMessenger();																		//  --  UI MESSENGER  --  //
		$this->initDatabase();																		//  --  DATABASE CONNECTION  --  //
		$this->initRequest();																		//  --  HTTP REQUEST HANDLER  --  //
		$this->initResponse();																		//  --  HTTP RESPONSE HANDLER  --  //
//		$this->initFieldDefinition();																//  --  FIELD DEFINITION SUPPORT  --  //
		$this->initLanguage();																		//  --  LANGUAGE SUPPORT  --  //
		$this->initPage();
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
	protected function initDatabase()
	{
		$driver			= $this->config->get( 'database.driver' );
		$host			= $this->config->get( 'database.host' );
		$port			= $this->config->get( 'database.port' );
		$name			= $this->config->get( 'database.name' );
		$username		= $this->config->get( 'database.username' );
		$password		= $this->config->get( 'database.password' );
		$prefix			= $this->config->get( 'database.prefix' );
		$logfile		= $this->config->get( 'database.log' );
#		$lazy			= $this->config->get( 'database.lazy' );
		$charset		= $this->config->get( 'database.charset' );
		$logStatements	= $this->config->get( 'database.log.statements' );
		$logErrors		= $this->config->get( 'database.log.errors' );

		if( empty( $driver ) )
			throw new RuntimeException( 'Database driver must be set in config:database.driver' );

		$dsn		= new Database_PDO_DataSourceName( $driver, $name );
		if( $host )
			$dsn->setHost( $host );
		if( $port )
			$dsn->setPort( $port );
		if( $username )
			$dsn->setUsername( $username );
		if( $password )
			$dsn->setPassword( $password );

		$driverOptions	= array();																	// to be implemented

#		$class		= $lazy ? 'Database_MySQL_LazyConnection' : 'Database_MySQL_Connection';
#		$this->dbc	= Alg_Object_Factory::createObject( $class, array( $logfile ) );
#		$this->dbc	= new Database_MySQL_Connection( $logfile );
		$this->dbc	= new Database_PDO_Connection( $dsn, $username, $password, $driverOptions );
		if( $logStatements )
			$this->dbc->setStatementLogFile( $logStatements );
		if( $logErrors )
			$this->dbc->setErrorLogFile( $logErrors );

#		$this->dbc->connect( $host, $username, $password, $name );
#		if( $charset )
#			$this->dbc->exec( "SET NAMES '".$charset."';" );
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
		if( $this->getConfig()->get( 'app.base.url' ) )
			$this->page->setBaseHref( $this->getConfig()->get( 'app.base.url' ) );
	}

	protected function initRequest()
	{
		$this->request		= new Net_HTTP_Request();
		$this->request->fromEnv( $this->getSession() );
		$redirectUrl		= getEnv( 'REDIRECT_URL' );
		if( !empty( $redirectUrl ) )
			if( method_exists( $this, 'realizeRewrittenUrl' ) )
				$this->realizeRewrittenUrl( $this->request );
	}

	protected function initResponse()
	{
		$this->response	= new Net_HTTP_Response();
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

	protected function realizeRewrittenUrl( Net_HTTP_Request $request )
	{
		$path	= $request->getFromSource( 'path', 'get' );
		if( !trim( $path ) )
			return;

		$parts	= explode( '/', $path );
		$request->set( 'controller',	array_shift( $parts ) );
		$request->set( 'action',		array_shift( $parts ) );
		$arguments	= array();
		while( count( $parts ) )
		{
			$part = trim( array_shift( $parts ) );
			if( strlen( $part ) )
				$arguments[]	= $part;
		}
		$request->set( 'arguments', $arguments );
/*		if( $this->request->get( 'param' ) && !$this->request->get( 'controller' ) )
		{
			$parts	= explode( ".", $this->request->get( 'param' ) );
			$this->request->set( 'controller', $parts[0] );
			$this->request->set( 'action', isset( $parts[1] ) ? $parts[1] : "index" );
			$this->request->set( 'id', isset( $parts[2] ) ? $parts[2] : "0" );
		}*/
	}
}
?>