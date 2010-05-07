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
 *	@package		Hydrogen
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
 *	@package		Hydrogen
 *	@uses			File_Configuration_Reader
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
class Framework_Hydrogen_Environment
{
	/**	@var	File_Configuration_Reader		$config		Configuration Object */
	protected $config;
	/**	@var	Database_BaseConnection			$dbc		Database Connection Object */
	protected $dbc;
	/**	@var	Net_HTTP_Request_Receiver		$request	HTTP Request Object */
	protected $request;
	/**	@var	Net_HTTP_Request_Response		$request	HTTP Response Object */
	protected $response;
	/**	@var	Net_HTTP_Session				$session	Session Object */
	protected $session;
	/** @var	Framework_Hydrogen_Messenger	$messenger	Messenger Object */
	protected $messenger;

	public static $configFile				= "config.ini.inc";

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@param		string		$logicClassName			Class Name of Logic Class, must be loaded before
	 *	@return		void
	 */
	public function __construct()
	{
		$this->initConfiguration();																	//  --  CONFIGURATION  --  //
		$this->initSession();																		//  --  SESSION HANDLING  --  //
		$this->initMessenger();																		//  --  UI MESSENGER  --  //
		$this->initDatabase();																		//  --  DATABASE CONNECTION  --  //
		$this->initLanguage();																		//  --  LANGUAGE SUPPORT  --  //
		$this->initRequest();																		//  --  HTTP REQUEST HANDLER  --  //
		$this->initResponse();																		//  --  HTTP RESPONSE HANDLER  --  //
//		$this->initFieldDefinition();																//  --  FIELD DEFINITION SUPPORT  --  //
	}

	public function close()
	{
		unset( $this->dbc );																		//
		unset( $this->session );																	//
		unset( $this->request );																	//
		unset( $this->response );																	//
		unset( $this->messenger );																	//
		unset( $this->language );																	//
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

	/**
	 *	Returns Language Object.
	 *	@access		public
	 *	@return		Framework_Hydrogen_Language
	 */
	public function getLanguage()
	{
		return $this->language;
	}
	
	/**
	 *	Returns Messenger Object.
	 *	@access		public
	 *	@return		Framework_Hydrogen_Messenger
	 */
	public function getMessenger()
	{
		return $this->messenger;
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

	protected function initConfiguration()
	{
		$data			= parse_ini_file( self::$configFile, FALSE );			//  parse configuration file
		$this->config	= new ADT_List_Dictionary( $data );						//  create dictionary from array
		if( $this->config->has( 'config.error.reporting' ) )					//  error reporting is defined
			error_reporting( $this->config->get( 'config.error.reporting' ) );	//  set error reporting level
	}

	/**
	 *	Sets up database support.
	 *	@todo		implement pdo driver options (in config also)
	 */
	protected function initDatabase()
	{
		$driver		= $this->config->get( 'database.driver' );
		$host		= $this->config->get( 'database.host' );
		$port		= $this->config->get( 'database.port' );
		$name		= $this->config->get( 'database.name' );
		$username	= $this->config->get( 'database.username' );
		$password	= $this->config->get( 'database.password' );
		$prefix		= $this->config->get( 'database.prefix' );
		$logfile	= $this->config->get( 'database.log' );
#		$lazy		= $this->config->get( 'database.lazy' );
		$charset	= $this->config->get( 'database.charset' );

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
#		$this->dbc->connect( $host, $username, $password, $name );
#		if( $charset )
#			$this->dbc->exec( "SET NAMES '".$charset."';" );
	}

/*	protected function initFieldDefinition()
	{
		$this->definition	= new Framework_Hydrogen_FieldDefinition(
			"config/",
			$this->config['config.use_cache'],
			$this->config['config.cache_path']
		);
		$this->definition->setChannel( "html" );
	}
*/
	protected function initLanguage()
	{
		$this->language		= new Framework_Hydrogen_Language( $this );
	}

	protected function initMessenger()
	{
		$this->messenger	= new Framework_Hydrogen_Messenger( $this );
	}

	protected function initRequest()
	{
		$this->request		= new Net_HTTP_Request_Receiver();
/*		if( $this->request->get( 'param' ) && !$this->request->get( 'controller' ) )
		{
			$parts	= explode( ".", $this->request->get( 'param' ) );
			$this->request->set( 'controller', $parts[0] );
			$this->request->set( 'action', isset( $parts[1] ) ? $parts[1] : "index" );
			$this->request->set( 'id', isset( $parts[2] ) ? $parts[2] : "0" );
		}*/
	}

	protected function initResponse()
	{
		$this->response	= new Net_HTTP_Request_Response();
	}

	protected function initSession()
	{
		$this->session	= new Net_HTTP_Session();
		return;
		$this->session	= new Net_HTTP_PartitionSession(
			$this->config['application.name'],
			$this->config['config.session.name']
		);
	}
}
?>