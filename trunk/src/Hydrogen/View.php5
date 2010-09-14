<?php
/**
 *	Generic View Class of Framework Hydrogen.
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
 *	Generic View Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@uses			UI_HTML_Elements
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class Framework_Hydrogen_View
{
	/**	@var		array							$data			Collected Data for View */
	protected $data	= array();
	/**	@var		Framework_Hydrogen_Environment_Abstract	$env	Environment Object */
	protected $env;
	/**	@var		array							$envKeys		Keys of Environment */
	protected $envKeys	= array(
		'dbc',
		'config',
		'session',
		'request',
		'language',
		'messenger',
		'model',
		'controller',
		'action',
		);
	/**	@var		Database_MySQL_Connection		$dbc			Database Connection */
	protected $dbc;
	/**	@var		array							$config			Configuration Settings */
	protected $config;
	/**	@var		Net_HTTP_PartitionSession		$session		Partition Session */
	protected $session;
	/**	@var		Net_HTTP_Request_Receiver		$request		Receiver of Request Parameters */
	protected $request;
	/**	@var		Framework_Hydrogen_Language		$language		Language Support */
	protected $language;
	/**	@var		Framework_Hydrogen_Messenger	$messenger		UI Messenger */
	protected $messenger;
	/**	@var		string							$controller		Name of called Controller */
	protected $controller	= "";
	/**	@var		string							$action			Name of called Action */
	protected $action	= "";

	protected $helpers;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment_Abstract	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	public function __construct( Framework_Hydrogen_Environment_Abstract $env )
	{
		$this->setEnv( $env );
		$this->html	= new UI_HTML_Elements;
		$this->time	= new Alg_Time_Converter();
		$this->helpers	= new ADT_List_Dictionary;
	}

	protected function addHelper( $name, $object, $parameters = array() )
	{
		if( is_object( $object ) )
		{
			$object->setEnv( $this->env );
			$this->helpers->$name	= $object;
		}
		else
			$this->registerHelper($name, $object, $parameters);
	}
	
	public function & getData()
	{
		return $this->data;
	}

	protected function getContentFileUri( $fileName )
	{
		$path		= $this->env->getConfig()->get( 'path.locales' );
 		$language	= $this->env->getLanguage()->getLanguage();
		$uri		= $path.$language.'/'.$fileName;
		return $uri;
	}
	
	
	/**
	 *	Returns File Name of Template.
	 *	@access		protected
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		string
	 */
	protected function getFilenameOfTemplate( $controller, $action )
	{
		$pathname	= $this->env->getConfig()->get( 'path.templates' );
		$filename	= $controller."/".$action.".php";
		return $pathname.$filename;
	}

	public function isContentFile( $fileName )
	{
		$uri	= $this->getContentFileUri( $fileName );
		return file_exists( $uri );
	}

	public function load()
	{
		$request	= $this->env->getRequest();
		$controller	= $request->get( 'controller' );
		$action		= $request->get( 'action' );
		return $this->loadTemplate( $controller, $action );
	}

	public function loadContent( $fileName, $data = array() )
	{
		if( !$this->isContentFile( $fileName ) )
			throw new RuntimeException( 'File "'.$fileName.'" is missing.', 321 );
		$uri	= $this->getContentFileUri( $fileName );
		return UI_Template::render( $uri, $data);
	}

	/**
	 *	Loads Template of View and returns Content.
	 *	@access		public
	 *	@param		string		$controller			Name of Controller
	 *	@param		string		$action				Name of Action
	 *	@param		array		$data				Additional Array of View Data
	 *	@return		string
	 */
	protected function loadTemplate( $controller, $action, $data = array() )
	{
#		throw new Exception("test");
		$fileName	= $this->getFilenameOfTemplate( $controller, $action );
		if( !file_exists( $fileName ) )
			throw new RuntimeException( 'Template "'.$this->controller.'/'.$this->action.'" is not existing', 311 );
		return $this->loadTemplateFile( $fileName, $data );
	}

	public function loadTemplateFile( $fileName, $data = array() )
	{
		if( !file_exists( $fileName ) )
			throw new RuntimeException( 'Template "'.$fileName.'" is not existing', 311 );

		$content	= '';
		ob_start();
		extract( $this->data );
		extract( $data );
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$helpers	= $this->helpers;
		$result		= require( $fileName );
		$buffer		= ob_get_clean();
		$content	= $result;
		if( trim( $buffer ) )
		{
			if( !is_string( $content ) )
				$content	= $buffer;
			else if( $this->env->getMessenger() )
				$this->env->getMessenger()->noteFailure( nl2br( $buffer ) );
			else
				throw new RuntimeException( $buffer );
		}
		return $content;
	}

	protected function registerHelper( $name, $class, $parameters = array() )
	{
		$object	= Alg_Object_Factory::createObject( $class, $parameters );
		$this->addHelper( $name, $object );
	}

	/**
	 *	Sets Data of View.
	 *	@access		public
	 *	@param		array		$data			Array of Data for View
	 *	@param		string		$topic			Optional: Topic Name of Data
	 *	@return		void
	 */
	public function setData( $data, $topic = NULL )
	{
		if( $topic )
		{
			if( !isset( $this->data[$topic] ) )
				$this->data[$topic]	= array();
			foreach( $data as $key => $value )
				$this->data[$topic][$key]	= $value;
		}
		else
		{
			foreach( $data as $key => $value )
				$this->data[$key]	= $value;
		}
	}
	
	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		Framework_Hydrogen_Environment_Abstract	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	protected function setEnv( Framework_Hydrogen_Environment_Abstract $env )
	{
		$this->env			= $env;
		$this->controller	= $this->env->getRequest()->get( 'controller' );
		$this->action		= $this->env->getRequest()->get( 'action' );
	}
}
?>