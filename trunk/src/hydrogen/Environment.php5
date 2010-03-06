<?php
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *
 *	Copyright (c) 2010 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.hydrogen
 *	@uses			File_Configuration_Reader
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Net_HTTP_Request_Response
 *	@uses			Net_HTTP_Session
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.03.2010
 *	@version		0.1
 */
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		cmClasses
 *	@package		framework.hydrogen
 *	@uses			File_Configuration_Reader
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Net_HTTP_Request_Response
 *	@uses			Net_HTTP_Session
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.03.2010
 *	@version		0.1
 */
class Framework_Hydrogen_Environment
{
	/**	@var	File_Configuration_Reader	$config		Configuration Object */
	protected $config;
	/**	@var	Database_BaseConnection		$dbc		Database Connection Object */
	protected $dbc;
	/**	@var	Net_HTTP_Request_Receiver	$request	HTTP Request Object */
	protected $request;
	/**	@var	Net_HTTP_Request_Response	$request	HTTP Response Object */
	protected $response;
	/**	@var	Net_HTTP_Session			$session	Session Object */
	protected $session;

	public static $configFile				= "config.ini.inc";
	public static $configDatabase			= "db.ini.inc";

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
		$this->dbc->close();
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
		$data			= parse_ini_file( self::$configFile, FALSE );
		$this->config	= new ADT_List_Dictionary( $data );
		error_reporting( $this->config->get( 'config.error_reporting' ) );
	}

	protected function initDatabase()
	{
		$data		= parse_ini_file( self::$configDatabase );
		$class		= "Database_MySQL_Connection";
		if( !empty( $data['lazy'] ) )
			$class	= "Database_MySQL_LazyConnection";
		$this->dbc	= Alg_Object_Factory::createObject( $class, array( $data['type'], $data['logfile'] ) );
		$this->dbc->connect( $data['hostname'], $data['username'], $data['password'], $data['database'] );
		$this->config['config.table_prefix']	= $data['prefix'];
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
		$this->language		= new Framework_Hydrogen_Language( $this, $this->config['languages.default'] );
		$this->language->load( 'main' );
	}

	protected function initMessenger()
	{
		$this->messenger	= new Framework_Hydrogen_Messenger( $this->session );
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
		$this->session	= new Net_HTTP_PartitionSession(
			$this->config['application.name'],
			$this->config['config.session_name']
		);
	}
}
?>