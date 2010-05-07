<?php
/**
 *	Generic Controller Class of Framework Hydrogen.
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
 *	Generic Controller Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class Framework_Hydrogen_Controller
{
	/**	@var		Framework_Hydrogen_Environment	$env			Application Environment Object */
	protected $env;
	/**	@var		array							$_data			Collected Data for View */
	var $_data					= array();
	/**	@var		string							$controller		Name of called Controller */
	protected $controller		= "";
	/**	@var		string							$action			Name of called Action */
	protected $action			= "";
	/**	@var		bool							$redirect		Flag for Redirection */
	var $redirect				= FALSE;

	protected $prefixModel		= "Model_";
	protected $prefixView		= "View_";

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment	$env			Application Environment Object
	 *	@return		void
	 */
	public function __construct( Framework_Hydrogen_Environment &$env )
	{
		$this->setEnv( $env );
	}
	
	//  --  SETTERS & GETTERS  --  //
	public function addData( $key, $value, $topic = NULL )
	{
		return $this->setData( array( $key => $value ), $topic );
	}
	
	/**
	 *	Returns Data for View.
	 *	@access		public
	 *	@return		array
	 */
	public function getData()
	{
		return $this->_data;
	}
	
	//  --  PUBLIC METHODS  --  //
	/**
	 *	Returns View Content of called Action.
	 *	@access		public
	 *	@return		string
	 */
	public function getView()
	{
		$view	= $this->getViewObject();
		if( !method_exists( $view, $this->action ) )
			throw new RuntimeException( 'View Action "'.$this->action.'" not defined yet', 302 );
		$data			= $this->getData();
		$data['words']	= $this->env->getLanguage()->getWords( $this->controller );
		$view->setData( $data );
		$result			= Alg_Object_MethodFactory::callObjectMethod( $view, $this->action );
		if( is_string( $result ) )
			return $result;
		return $view->load();
	}
	
	/**
	 *	Redirects by calling different Controller and Action.
	 *	@access		public
	 *	@param		string		$controller		Controller to be called, default: index
	 *	@param		string		$action			Action to be called, default: index
	 *	@param		array		$parameters		Map of additional parameters to set in request
	 *	@return		void
	 */
	public function redirect( $controller = 'index', $action = "index", $parameters = array() )
	{
		$this->env->getRequest()->set( 'controller', $controller );
		$this->env->getRequest()->set( 'action', $action );
		foreach( $parameters as $key => $value )
			if( !empty( $key ) )
				$this->env->getRequest()->set( $key, $value );
		$this->redirect = TRUE;
	}
	
	/**
	 *	Redirects by requesting a URI.
	 *	@access		public
	 *	@param		string		$uri				URI to request
	 *	@return		void
	 */
	public function restart( $uri )
	{
		$base	= dirname( getEnv( 'SCRIPT_NAME' ) )."/";
	#	$this->dbc->close();
	#	$this->session->close();
		header( "Location: ".$base.$uri );
		die;
	}

	/**
	 *
	 *	Sets Data for View.
	 *	@access		public
	 *	@param		array		$data			Array of Data for View
	 *	@param		string		[$topic]			Topic Name of Data
	 *	@return		void
	 */
	public function setData( $data, $topic = "" )
	{
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
	 *	@param		Framework_Hydrogen_Environment	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	protected function setEnv( Framework_Hydrogen_Environment &$env )
	{
		$this->env			= $env;
		$this->controller	= $env->getRequest()->get( 'controller' );
		$this->action		= $env->getRequest()->get( 'action' );
		$this->env->getLanguage()->load( $this->controller );
	}
}
?>