<?php
/**
 *	Setup for Resource Environment for Tool Applications.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@package		framework.tool
 *	@uses			File_Configuration_Reader
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Net_HTTP_Request_Response
 *	@uses			Net_HTTP_Session
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			27.05.2008
 *	@version		0.1
 */
import( 'de.ceus-media.file.configuration.Reader' );
import( 'de.ceus-media.net.http.request.Receiver' );
import( 'de.ceus-media.net.http.request.Response' );
import( 'de.ceus-media.net.http.Session' );
/**
 *	Setup for Resource Environment for Tool Applications.
 *	@category		cmClasses
 *	@package		framework.tool
 *	@uses			File_Configuration_Reader
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Net_HTTP_Request_Response
 *	@uses			Net_HTTP_Session
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			27.05.2008
 *	@version		0.1
 */
class Framework_Tool_Environment
{
	/**	@var	File_Configuration_Reader	$config		Configuration Object */
	private $config;
	/**	@var	Net_HTTP_Request_Receiver	$request	HTTP Request Object */
	private $request;
	/**	@var	Net_HTTP_Request_Response	$request	HTTP Response Object */
	private $response;
	/**	@var	Net_HTTP_Session			$session	Session Object */
	private $session;
	/**	@var	Logic						$logic		Logic Object */
	private $logic;

	public static $configFile	= "config.ini";

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@param		string		$logicClassName			Class Name of Logic Class, must be loaded before
	 *	@return		void
	 */
	public function __construct( $logicClassName )
	{
		$this->config	= new File_Configuration_Reader( self::$configFile );
		$this->request	= new Net_HTTP_Request_Receiver();
		$this->response	= new Net_HTTP_Request_Response();
		$this->session	= new Net_HTTP_Session();
		$this->logic	= new $logicClassName( $this->config );

		ob_start();
	}
	
	/**
	 *	Returns Configuration Object.
	 *	@access		public
	 *	@return		File_Configuration_Reader
	 */
	public function getConfiguration()
	{
		return $this->config;
	}
	
	/**
	 *	Returns Logic Object.
	 *	@access		public
	 *	@return		Logic
	 */
	public function getLogic()
	{
		return $this->logic;
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
	 *	Returns Configuration Object.
	 *	@access		public
	 *	@return		File_Configuration_Reader
	 */
	public function getSession()
	{
		return $this->session;
	}
}
?>