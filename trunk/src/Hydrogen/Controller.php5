<?php
/**
 *	Generic Controller Class of Framework Hydrogen.
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
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Generic Controller Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Controller
{
	public static $prefixModel		= "Model_";
	public static $prefixView		= "View_";
	public $alias					= "";

	/**	@var		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object */
	protected $env;
	/**	@var		array								$_data			Collected Data for View */
	var $_data					= array();
	/**	@var		string								$controller		Name of called Controller */
	protected $controller		= "";
	/**	@var		string								$action			Name of called Action */
	protected $action			= "";
	/**	@var		bool								$redirect		Flag for Redirection */
	var $redirect				= FALSE;
	/**	@var		CMF_Hydrogen_View					$view			View instance for controller */
	protected $view;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$env->clock->profiler->tick( 'CMF_Controller('.get_class( $this ).')' );
		$this->setEnv( $env );
		$env->clock->profiler->tick( 'CMF_Controller('.get_class( $this ).'): env set' );
		$this->view	= $this->getViewObject( $this->controller );
//		$arguments		= array_slice( func_get_args(), 1 );										//  collect additional arguments for extended logic classes
//		Alg_Object_MethodFactory::callObjectMethod( $this, '__onInit', $arguments, TRUE, TRUE );	//  invoke possibly extended init method
		$this->__onInit();
		$env->clock->profiler->tick( 'CMF_Controller('.get_class( $this ).'): done' );
	}

	/**
	 *	Empty method which is called after construction and can be customised.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(){}
	
	
	protected function addData( $key, $value, $topic = NULL )
	{
		return $this->view->setData( array( $key => $value ), $topic );
	}

	protected function compactFilterInput( $input ){
		if( is_object( $input ) || is_resource( $input ) || is_null( $input ) )						//  input is of invalid type
			return NULL;																			//  break with empty result
		if( is_array( $input ) ){																	//  input is an array
			foreach( $input as $nr => $chunk ){														//  iterate map pairs
				$chunk	= $this->compactFilterInput( $chunk );										//  compact map pair data chunk
				if( !( is_array( $chunk ) && count( $chunk ) ) && !strlen( $chunk ) )				//  chunk is empty
					unset( $input[$nr] );															//  remove chunk pair from map
			}
		}
		return $input;
	}
	
	/**
	 *	Returns Data for View.
	 *	@access		protected
	 *	@return		array
	 */
	protected function getData( $key = NULL )
	{
		return $this->view->getData( $key );
	}
	
	//  --  PUBLIC METHODS  --  //
	/**
	 *	Returns View Content of called Action.
	 *	@access		public
	 *	@return		string
	 */
	public function getView()
	{
		$this->env->clock->profiler->tick( 'Controller::getView: start' );
		if( !$this->view )
			throw new RuntimeException( 'No view object created in Constructor' );
		if( !method_exists( $this->view, $this->action ) )
			throw new RuntimeException( 'View Action "'.$this->action.'" not defined yet', 302 );
		$language		= $this->env->getLanguage();
		$this->env->clock->profiler->tick( 'Controller::getView: got language' );
		if( $language->hasWords( $this->controller ) )
			$this->view->setData( $language->getWords( $this->controller ), 'words' );
		$this->env->clock->profiler->tick( 'Controller::getView: set words' );
		$result			= Alg_Object_MethodFactory::callObjectMethod( $this->view, $this->action );
		if( is_string( $result ) ){
			$this->env->clock->profiler->tick( 'Controller::getView: Action called' );
		}
		else if( $this->view->hasTemplate( $this->controller, $this->action ) ){
			$result	= $this->view->loadTemplate( $this->controller, $this->action );
			$this->env->clock->profiler->tick( 'Controller::getView: loadTemplate' );
		}
		else if( $this->view->hasContent( $this->controller, $this->action, 'html/' ) ){
			$result	= $this->view->loadContent( $this->controller, $this->action, NULL, 'html/' );
			$this->env->clock->profiler->tick( 'Controller::getView: loadContent' );
		}
		else
			throw new Exception( 'Neither view template nor content file defined' );
		return $result;
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@return		void
	 */
	protected function getViewObject( $controller )
	{
		$name	= str_replace( ' ', '_', ucwords( str_replace( '/', ' ', $controller ) ) );
		$class	= self::$prefixView.$name;
		if( !class_exists( $class, TRUE ) )
			throw new RuntimeException( 'View "'.$name.'" is missing', 301 );
		return Alg_Object_Factory::createObject( $class, array( &$this->env ) );
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string		$section	Section in locale file
	 *	@param		string		$topic		Locale file key, eg. test/my, default: current controller
	 *	@return		void
	 */
	protected function getWords( $section = NULL, $topic = NULL ){
		if( empty( $topic ) && $this->env->getLanguage()->hasWords( $this->controller ) )
			$topic = $this->controller;
		if( empty( $section ) )
			return $this->env->getLanguage()->getWords( $topic );
		return $this->env->getLanguage()->getSection( $topic, $section );
	}

	/**
	 *	Redirects by calling different Controller and Action.
	 *	@access		protected
	 *	@param		string		$controller		Controller to be called, default: index
	 *	@param		string		$action			Action to be called, default: index
	 *	@param		array		$parameters		Map of additional parameters to set in request
	 *	@return		void
	 */
	protected function redirect( $controller = 'index', $action = "index", $arguments = array(), $parameters = array() )
	{
		$request	= $this->env->getRequest();
		$request->set( 'controller', $controller );
		$request->set( 'action', $action );
		$request->set( 'arguments', $arguments );
		foreach( $parameters as $key => $value )
			if( !empty( $key ) )
				$request->set( $key, $value );
		$this->redirect = TRUE;
	}
	
	/**
	 *	Redirects by requesting a URI.
	 *	@access		protected
	 *	@param		string		$uri				URI to request
	 *	@return		void
	 *	@todo		concept and implement anti-loop
	 *	@see		http://dev.(ceusmedia.com)/cmKB/?MTI
	 */
	protected function restart( $uri, $withinModule = FALSE )
	{
		$base   = preg_replace( "@^(.*)\/*$@U", "\\1/", dirname( getEnv( 'SCRIPT_NAME' ) ) );
		if( $withinModule ){
			$controller	= $this->env->getRequest()->get( 'controller' );
			$base	.= $this->alias ? $this->alias : $controller;
			$base	.= strlen( $uri ) ? '/' : '';
		}
	#	$this->dbc->close();
	#	$this->session->close();
		header( "Location: ".$base.$uri );
		die;
	}

	/**
	 *
	 *	Sets Data for View.
	 *	@access		protected
	 *	@param		array		$data			Array of Data for View
	 *	@param		string		[$topic]			Topic Name of Data
	 *	@return		void
	 */
	protected function setData( $data, $topic = "" )
	{
		if( $this->view )
			$this->view->setData( $data, $topic );
		return;
		if( !is_array( $data ) )
			throw new InvalidArgumentException( 'Must be array' );
		if( is_string( $topic) && !empty( $topic ) )
		{
			if( !isset( $this->_data[$topic] ) )
				$this->_data[$topic]	= array();
			foreach( $data as $key => $value )
				$this->_data[$topic][$key]	= $value;
		}
		else
		{
			foreach( $data as $key => $value )
				$this->_data[$key]	= $value;
		}
	}

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	protected function setEnv( CMF_Hydrogen_Environment_Abstract &$env )
	{
		$this->env			= $env;
		$this->controller	= $env->getRequest()->get( 'controller' );
		$this->action		= $env->getRequest()->get( 'action' );
		if( $this->env->has( 'language' ) )
		{
			$language	= $this->env->getLanguage();
			$language->load( $this->controller, FALSE, FALSE );
		}
	}
}
?>
