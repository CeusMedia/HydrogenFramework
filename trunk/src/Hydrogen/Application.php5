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
 *	@todo			Code Documentation
 */
class Framework_Hydrogen_Application
{
	/**	@var		string							$classEnvironment		Class Name of Application Environment to build */
	public static $classEnvironment					= 'Framework_Hydrogen_Environment';
	/**	@var		string							$content				Collected Content to respond */
	protected $content								= '';
	/**	@var		Framework_Hydrogen_Environment	$env					Application Environment Object */
	protected $env;

	protected $components			= array();
	protected $_dev;
	protected $clock;

	public $prefixController		= "Controller_";
	public $prefixModel				= "Model_";
	public $prefixView				= "View_";

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment	$env					Framework Environment
	 *	@return		void
	 */
	public function __construct( $env = NULL )
	{
		try
		{
			$this->clock	= new Alg_Time_Clock();
			if( !$env )
				$env		= Alg_Object_Factory::createObject( self::$classEnvironment );
			$this->env		= $env;
			$result			= $this->main();
			$this->env->getResponse()->write( $result );
			$this->env->getResponse()->send();
			$this->env->close();
		}
		catch( Exception $e )
		{
			new UI_HTML_Exception_TraceViewer( $e );
		}
	}

	
	/**
	 *	Main Method of Framework calling Controller (and View) and Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function main()
	{
		ob_start();
		$this->control();														// dispatch and run request

		if( $this->env->getRequest()->isAjax() )								// this is an AJAX request
			return $this->content;												// deliver content only

		$config		= $this->env->getConfig();									// shortcut to configation object
		$language	= $this->env->getLanguage();								// shortcut to language object
		$database	= $this->env->getDatabase();								// shortcut to database connection object
		$this->setViewComponents(												// set up information sources for main template
			array(
				'config'		=> $config,										// configuration object
				'request'		=> $this->env->getRequest(),					// request object
				'messenger'		=> $this->env->getMessenger(),					// UI messages for user
				'language'		=> $config['languages.default'],				// document language
				'words'			=> $language->getWords( 'main' ),				// main UI word pairs
				'content'		=> $this->content,								// calculates page content
				'clock'			=> $this->clock,								// system clock for performance messure
				'dbQueries'		=> (int) $database->countQueries,				// number of SQL queries sent
				'dev'			=> ob_get_clean(),								// error or development messages
			)
		);
		return $this->view();													// render and return main template to constructor
	}

	/**
	 *	Executes called Controller and stores generated View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function control( $defaultController = 'index', $defaultAction = 'index' )
	{
		$dispatched	= array();
		try
		{
			do
			{
				$request	= $this->env->getRequest();
				if( !$request->get( 'controller' ) )
					$request->set( 'controller', $defaultController );
				if( !$request->get( 'action' ) )
					$request->set( 'action', $defaultAction );
				$controller	= $request->get( 'controller' );
				$action		= $request->get( 'action' );

		#		remark( "controller: ".$controller );
		#		remark( "action: ".$action );
				if( empty( $dispatched[$controller][$action] ) )
					$dispatched[$controller][$action]	= 0;
				if( $dispatched[$controller][$action] > 2 )
				{
					$this->messenger->noteFailure( 'Too many redirects.' );
					break;
				}
				$dispatched[$controller][$action]++;

				$class		= $this->prefixController.ucfirst( $controller );
				if( !class_exists( $class ) )
					throw new RuntimeException( 'Controller "'.ucfirst( $controller ).'" not defined yet', 201 );
				$object	= Alg_Object_Factory::createObject( $class, array( &$this->env ) );
				if( !method_exists( $object, $action ) )
					return $this->env->getMessenger()->noteFailure( "Action '".ucfirst( $class )."::".$action."' not defined yet." );
				Alg_Object_MethodFactory::callObjectMethod( $object, $action );
				if( strtoupper( getEnv( 'REQUEST_METHOD' ) ) == 'GET' )
				{
					if( !$object->redirect )
					{
						$this->env->getSession()->set( 'lastController', $controller );
						$this->env->getSession()->set( 'lastAction', $action );
					}
				}
			}
			while( $object->redirect );
			return $this->content = $object->getView();
		}
		catch( Exception $e )
		{
			if( class_exists( 'View_Error' ) )
			{
				$view	= new View_Error( $this->env );
				$result	= $view->handleException( $e );
				if( $result )
				{
					return $this->content = $result;
				}
			}
			$this->env->getMessenger()->noteFailure( $e->getMessage().'.' );
		}
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