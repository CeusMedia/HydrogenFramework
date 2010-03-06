<?php
/**
 *	Generic Main Class of Framework Hydrogen
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
 *	@package		framework.hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.1
 */
/**
 *	Generic Main Class of Framework Hydrogen
 *	@category		cmClasses
 *	@package		framework.hydrogen
 *	@uses			Database_MySQL_Connection
 *	@uses			File_INI_Reader
 *	@uses			Net_HTTP_PartitionSession
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Alg_Time_Clock
 *	@uses			Framework_Hydrogen_FieldDefinition
 *	@uses			Framework_Hydrogen_Messenger
 *	@uses			Framework_Hydrogen_Model
 *	@uses			Framework_Hydrogen_View
 *	@uses			Framework_Hydrogen_Controller
 *	@uses			Language
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.1
 *	@deprecated		use Framework_Hydrogen_Application instead
 *	@todo			Code Documentation
 */
class Framework_Hydrogen_Base
{
	public $config;
	var $dbc;
	var $session;
	var $request;
	var $language;
	var $messenger;
	var $definition;

	var $controller	= "";
	var $action	= "";
	var $content	= "";
	
	var $_components;
	var $_dev;
	var $clock;
	
	public $fileConfig			= "config/config.ini";
	public $fileDatabase		= "config/db_access.ini";
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $fileConfig = NULL, $fileDatabase = NULL )
	{
		if( !empty( $fileConfig ) )
			$this->fileConfig	= $fileConfig;
		if( !empty( $fileDatabase ) )
			$this->fileDatabase	= $fileDatabase;
		$this->clock	= new Alg_Time_Clock();
		$this->init();
		$this->openBuffer();
	}
	
	/**
	 *	Main Method of Framework calling Controller (and View) and Master View.
	 *	@access		public
	 *	@return		void
	 */
	public function main()
	{
		$this->control();
		$this->closeBuffer();
		$this->setViewComponents( array(
				'config'		=> $this->config,
				'content'		=> $this->content,
				'messages'		=> $this->messenger->buildMessages( $this->config['layout']['format_timestamp'] ),
				'language'		=> $this->config['languages']['default'],
				'words'			=> $this->language->getWords( 'main' ),
				'stopwatch'		=> $this->clock->stop(),
				'dev'			=> $this->_dev,
			)
		);
		$this->view();
	}

	/**
	 *	Executes called Controller and stores generated View.
	 *	@access		public
	 *	@return		void
	 */
	public function control( $defaultController = 'index', $defaultAction = 'index' )
	{
		if( !$this->request->get( 'controller' ) )
			$this->request->set( 'controller', $defaultController );
		if( !$this->request->get( 'action' ) )
			$this->request->set( 'action', $defaultAction );
		$this->controller	= $this->request->get( 'controller' );
		$this->action		= $this->request->get( 'action' );
		
#		remark( "controller: ".$this->controller );
#		remark( "action: ".$this->action );
		
		$class		= "Controller_".ucfirst( $this->controller );
		if( !class_exists( $class ) )
			return $this->messenger->noteFailure( "Controller '".ucfirst( $this->controller )."' not defined yet." );
		$controller	= new $class( $this );
		if( !method_exists( $controller, $this->action ) )
			return $this->messenger->noteFailure( "Action '".ucfirst( $this->controller )."::".$this->action."' not defined yet." );
		$controller->{$this->action}();
		if( $controller->redirect )
			return $this->control();
		$this->content = $controller->getView();
	}

	/**
	 *	Sets collacted View Components for Master View.
	 *	@access		public
	 *	@return		void
	 */
	public function setViewComponents( $components = array() )
	{
		$this->_components	= $components;
	}

	/**
	 *	Collates View Components and puts out Master View.
	 *	@access		public
	 *	@return		void
	 */
	public function view()
	{
		extract( $this->_components );
		$words	= $this->language->getWords( 'main' );
		require_once( $this->config['paths']['templates']."master.php" );
		unset( $this->session );			//		->close();
//		$this->dbc->close();
		die( $content );
	}

	//  --  PRIVATE METHODS  --  //
	/**
	 *	Closes Output Buffer.
	 *	@access		protected
	 *	@return		void
	 */
	protected function closeBuffer()
	{
		$this->_dev	= ob_get_contents();
		ob_end_clean();
	}

	/**
	 *	Returns File Name of selected Controller.
	 *	@access		protected
	 *	@param		string		$controller		Name of called Controller
	 *	@return		string
	 */
	protected function getFilenameOfController( $controller )
	{
		$filename	= $this->config['paths']['controllers'].ucfirst( $controller).".php5";
		return $filename;
	}

	protected function initConfiguration()
	{
		$ir_conf		= new File_INI_Reader( $this->fileConfig, TRUE );
		$this->config	= $ir_conf->toArray();
		error_reporting( $this->config['config']['error_reporting'] );
	}

	protected function initDatabase()
	{
		$data		= parse_ini_file( $this->fileDatabase );
		$this->dbc	= new Database_MySQL_Connection( $data['type'], $data['logfile'] );
		$this->dbc->connect( $data['hostname'], $data['username'], $data['password'], $data['database'], (bool) $data['lazy'] );
		$this->config['config']['table_prefix']	= $data['prefix'];
	}


	protected function initFieldDefinition()
	{
		$this->definition	= new Framework_Hydrogen_FieldDefinition( "config/", $this->config['config']['use_cache'], $this->config['config']['cache_path'] );
		$this->definition->setChannel( "html" );
	}

	protected function initLanguage()
	{
		$this->language		= new Framework_Hydrogen_Language( $this, $this->config['languages']['default'] );
		$this->language->load( 'main' );
	}

	protected function initMessenger()
	{
		$this->messenger	= new Framework_Hydrogen_Messenger( $this->session );
	}

	protected function initRequest()
	{
		$this->request		= new Net_HTTP_Request_Receiver();
		if( $this->request->get( 'param' ) && !$this->request->get( 'controller' ) )
		{
			$parts	= explode( ".", $this->request->get( 'param' ) );
			$this->request->set( 'controller', $parts[0] );
			$this->request->set( 'action', isset( $parts[1] ) ? $parts[1] : "index" );
			$this->request->set( 'id', isset( $parts[2] ) ? $parts[2] : "0" );
		}
	}

	protected function initSession()
	{
		$this->session	= new Net_HTTP_PartitionSession( $this->config['application']['name'], $this->config['config']['session_name'] );
	}

	/**
	 *	Initialisation of Framework Environment.
	 *	@access		protected
	 *	@return		void
	 */
	protected function init()
	{
		$this->initConfiguration();																	//  --  CONFIGURATION  --  //
		$this->initSession();																		//  --  SESSION HANDLING  --  //
		$this->initMessenger();																		//  --  UI MESSENGER  --  //
		$this->initDatabase();																		//  --  DATABASE CONNECTION  --  //
		$this->initLanguage();																		//  --  LANGUAGE SUPPORT  --  //
		$this->initRequest();																		//  --  REQUEST HANDLER  --  //
		$this->initFieldDefinition();																//  --  FIELD DEFINITION SUPPORT  --  //
	}
	
	/**
	 *	Opens Output Buffer.
	 *	@access		protected
	 *	@return		void
	 */
	protected function openBuffer()
	{
		ob_start();
	}
}
?>