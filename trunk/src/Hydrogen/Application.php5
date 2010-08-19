<?php
/**
 *	Generic Main Class of Framework Hydrogen
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
 *	Generic Main Class of Framework Hydrogen
 *	@category		cmFrameworks
 *	@package		Hydrogen
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
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			Code Documentation
 */
class Framework_Hydrogen_Application
{
	/**	@var		string							$classEnvironment		Class Name of Application Environment to build */
	public static $classEnvironment					= 'Framework_Hydrogen_Environment';
	public static $checkClassActionArguments		= TRUE;

	/**	@var		string							$content				Collected Content to respond */
	protected $content								= '';
	/**	@var		Framework_Hydrogen_Environment	$env					Application Environment Object */
	protected $env;

	protected $components			= array();
	protected $_dev;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment	$env					Framework Environment
	 *	@return		void
	 */
	public function __construct( $env = NULL )
	{
		error_reporting( E_ALL );
		try
		{
			if( !$env )
				$env		= Alg_Object_Factory::createObject( self::$classEnvironment );
			$this->env		= $env;

			/*	@todo		Hack for moved clock, please remove later */
			$this->clock	= $this->env->getClock();

			$this->respond( $this->main() );
			$this->logOnComplete();
			$this->env->close();
			exit( 0 );
		}
		catch( Exception $e )
		{
			new UI_HTML_Exception_TraceViewer( $e );
		}
	}

	protected function logOnComplete()
	{
		$responseLength	= $this->env->getResponse()->getLength();
		$responseTime	= $this->env->getClock()->stop( 6, 0 );
		// ...
	}
	
	/**
	 *	Main Method of Framework calling Controller (and View) and Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function main()
	{
		ob_start();
		$content	= $this->control();														// dispatch and run request

		if( $this->env->getRequest()->isAjax() )								// this is an AJAX request
			return $content;													// deliver content only

		$config		= $this->env->getConfig();									// shortcut to configation object
		$language	= $this->env->getLanguage();								// shortcut to language object
		$database	= $this->env->getDatabase();								// shortcut to database connection object
		$this->setViewComponents(												// set up information sources for main template
			array(
				'config'		=> $config,										// configuration object
				'request'		=> $this->env->getRequest(),					// request object
				'messenger'		=> $this->env->getMessenger(),					// UI messages for user
				'language'		=> $config['languages.default'],				// document language
				'words'			=> $language->getWords( 'main', FALSE, FALSE ),	// main UI word pairs
				'content'		=> $content,									// rendered response page view content
				'clock'			=> $this->clock,								// system clock for performance messure
				'dbQueries'		=> (int) $database->numberExecutes,				// number of SQL queries executed
				'dbStatements'	=> (int) $database->numberStatements,			// number of SQL statements sent
				'dev'			=> ob_get_clean(),								// error or development messages
			)
		);
		return $this->view();													// render and return main template to constructor
	}

	/**
	 *	Executes called Controller and stores generated View.
	 *	@access		protected
	 *	@param		string		$defaultController	Controller if none is set and not 'index'
	 *	@param		string		$defaultAction		Action if none is set and not 'index'
	 *	@return		string		Content generated by view triggered by controller
	 *	@throws		Exception	if a exception is caught and neither error view not messenger is available
	 */
	protected function control( $defaultController = NULL, $defaultAction = NULL )
	{
		$request		= $this->env->getRequest();
		try
		{
			$dispatcher	= new Framework_Hydrogen_Dispatcher( $this->env );
			$dispatcher->checkClassActionArguments	= self::$checkClassActionArguments;
			if( $defaultController )
				$dispatcher->defaultController	= $defaultController;
			if( $defaultAction )
				$dispatcher->defaultAction		= $defaultAction;
			return $dispatcher->dispatch();
		}
		catch( Exception $e )
		{
			if( class_exists( 'View_Error' ) )
			{
				$view	= new View_Error( $this->env );
				$result	= $view->handleException( $e );
				if( $result )
					return $result;
			}
			else if( !$this->env->getMessenger() )
				throw $e;
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	/**
	 *	Simple implementation of content response. Can be overridden for special moves.
	 *	@access		public
	 *	@param		string		$body		Response content body
	 *	@return		int			Number of sent bytes
	 */
	protected function respond( $body, $headers = array() )
	{
		$response	= $this->env->getResponse();

		if( $body )
			$response->setBody( $body );

		foreach( $headers as $key => $value )
			if( $value instanceof Net_HTTP_Header )
				$response->addHeader( $header );
			else
				$response->addHeaderPair( $key, $value );
		
		return $response->send();
	}

	/**
	 *	Sets collacted View Components for Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function setViewComponents( $components = array() )
	{
		foreach( $components as $key => $component )
		{
			if( !array_key_exists( $key, $this->components ) )
				$this->components[$key]	= $component;
			
		}
	}

	/**
	 *	Collates View Components and puts out Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function view( $templateFile = "master.php" )
	{
		$view	= new Framework_Hydrogen_View( $this->env );
		$path	= $this->env->getConfig()->get( 'path.templates' );
		return $view->loadTemplateFile( $path.$templateFile, $this->components );
	}
}
?>