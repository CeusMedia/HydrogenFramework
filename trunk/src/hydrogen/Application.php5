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

	protected $_components;
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
		$this->clock	= new Alg_Time_Clock();
		if( !$env )
			$env		= Alg_Object_Factory::createObject( self::$classEnvironment );
		$this->env		= $env;
		$this->env->getLanguage()->load( 'main' );
		$result			= $this->main();
		$this->env->getResponse()->write( $result );
		$this->env->getResponse()->send();
		$this->env->close();
	}

	
	/**
	 *	Main Method of Framework calling Controller (and View) and Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function main()
	{
		ob_start();
		$this->control();
		$config		= $this->env->getConfig();
		$this->setViewComponents(
			array(
				'config'		=> $config,
				'request'		=> $this->env->getRequest(),
				'messages'		=> $this->env->getMessenger()->buildMessages( $config['layout.format_timestamp'] ),
				'language'		=> $config['languages.default'],
				'words'			=> $this->env->getLanguage()->getWords( 'main' ),
				'content'		=> $this->content,
				'clock'			=> $this->clock,
				'dbQueries'		=> (int) $this->env->getDatabase()->countQueries,
				'dev'			=> ob_get_clean(),
			)
		);
		return $this->view();
	}

	/**
	 *	Executes called Controller and stores generated View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function control( $defaultController = 'index', $defaultAction = 'index' )
	{
		$dispatched	= array();
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
				return $this->env->getMessenger()->noteFailure( "Controller '".ucfirst( $controller )."' not defined yet." );
			$object	= Alg_Object_Factory::createObject( $class, array( &$this->env ) );
			if( !method_exists( $object, $action ) )
				return $this->env->getMessenger()->noteFailure( "Action '".ucfirst( $class )."::".$action."' not defined yet." );
			Alg_Object_MethodFactory::callObjectMethod( $object, $action );
		}
		while( $object->redirect );
		$this->content = $object->getView();
	}

	/**
	 *	Sets collacted View Components for Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function setViewComponents( $components = array() )
	{
		$this->_components	= $components;
	}

	/**
	 *	Collates View Components and puts out Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function view()
	{
		extract( $this->_components );
		return require_once( $config['paths.templates']."master.php" );
	}
}
?>