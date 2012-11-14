<?php
/**
 *	Generic View Class of Framework Hydrogen.
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
 *	Generic View Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@uses			UI_HTML_Elements
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_View
{
	/**	@var		array						$data			Collected Data for View */
	protected $data			= array();
	/**	@var		CMF_Hydrogen_Environment_Web	$env			Environment Object */
	protected $env;
	/**	@var		string						$controller		Name of called Controller */
	protected $controller	= NULL;
	/**	@var		string						$action			Name of called Action */
	protected $action		= NULL;
	/**	@var		array						$helpers		Map of view helper classes/objects */
	protected $helpers;
	/**	@var		string						$time			Instance of time converter */
	protected $time;
	/**	@var		string						$html			Instance of HTML library class */
	protected $html;
	/**	@var		CMM_TEA_Factory				$tea			Instance of TEA (Template Engine Abstraction) Factory (from cmModules) OR empty if TEA is not available */
	protected $tea			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Web $env )
	{
		$this->setEnv( $env );
		$this->html		= new UI_HTML_Elements;
		$this->time		= new Alg_Time_Converter();
		$this->helpers	= new ADT_List_Dictionary;

/*		if( class_exists( 'CMM_TEA_Factory' ) ){
			$config	= 'config/TEA.ini';
			if( !file_exists( 'config/TEA.ini' ) )
				$config	= array( 'CMC' => array( 'active' => TRUE ) );
			$this->tea		= new CMM_TEA_Factory( $config );
			$this->tea->setDefaultType( 'PHP' );
#			$this->tea->hasEngine( 'STE' ){
				$this->tea->setDefaultType( 'STE' );
				CMM_STE_Template::addPlugin( new CMM_STE_Plugin_Comments() );
#			}
			$this->tea->setTemplatePath( '' );
			$this->tea->setCachePath( 'tmp/cache/templates/' );
			$this->tea->setCompilePath( 'tmp/cache/templates_c/' );
		}*/
		$this->__onInit();
	}

	/**
	 *	Empty method which is called after construction and can be customised.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(){}

	public function addData( $key, $value, $topic = NULL )
	{
		return $this->setData( array( $key => $value ), $topic );
	}

	protected function addHelper( $name, $object, $parameters = array() )
	{
		if( is_object( $object ) )
		{
			$object->setEnv( $this->env );
			$this->helpers->set( $name, $object );
		}
		else
			$this->registerHelper($name, $object, $parameters);
	}

	public function & getData( $key = NULL )
	{
		if( !$key )
			return $this->data;
		if( isset( $this->data[$key] ) )
			return $this->data[$key];
		throw new InvalidArgumentException( 'Data for key "'.$key."' is not set" );
	}

	public function getContentUri( $fileKey, $path = NULL )
	{
		$path		= preg_replace( '/^(.+)(\/)*$/U', '\\1/', $path );
		$pathLocale	= $this->env->getConfig()->get( 'path.locales' );
 		$language	= $this->env->getLanguage()->getLanguage();
		$uri		= $pathLocale.$language.'/'.$path.$fileKey;
		return $uri;
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
		return (object) $this->env->getLanguage()->getSection( $topic, $section );
	}

	protected function getTemplateUri( $controller, $action )
	{
		$fileKey	= $controller.'/'.$action.'.php';
		return $this->getTemplateUriFromFile( $fileKey );
	}

	/**
	 *	Returns File Name of Template.
	 *	@access		protected
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		string
	 */
	protected function getTemplateUriFromFile( $fileKey )
	{
		$path		= $this->env->getConfig()->get( 'path.templates' );
		return $path.$fileKey;
	}

	public function hasContent( $controller, $action, $path = NULL )
	{
		$fileKey	= $controller.'/'.$action.'.html';
		return $this->hasContentFile( $fileKey, $path );
	}

	public function hasContentFile( $fileKey, $path = NULL )
	{
		$uri	= $this->getContentUri( $fileKey, $path );
		return file_exists( $uri );
	}


	public function hasTemplate( $controller, $action )
	{
		$uri		= $this->getTemplateUri( $controller, $action );
		return file_exists( $uri );
	}

	public function hasTemplateFile( $fileKey )
	{
		$file	= $controller.'/'.$action.'.php';
		$uri	= $this->getTemplateUriFromFile( $file );
		return file_exists( $uri );
	}

	public function loadContent( $controller, $action, $data = array() )
	{
		$fileKey	= 'html/'.$controller.'/'.$action.'.html';
		return $this->loadContentFile( $fileKey, $data );
	}

	/**
	 *	@todo	remove use of UI_Template
	 */
	public function loadContentFile( $fileKey, $data = array(), $path = NULL )
	{
		if( !is_array( $data ) )																	//  no data given
			$data	= array();																		//  ensure empty array
		$uri	= $this->getContentUri( $fileKey, $path );											//  calculate file pathname
		if( !file_exists( $uri ) )																	//  content file is not existing
			throw new RuntimeException( 'Locale content file "'.$fileKey.'" is missing.', 321 );	//  throw exception
//		$data	= array_merge( $this->data, $data );
		if( $this->env->getPage()->tea ){															//  template engine abstraction is enabled
			$this->env->getPage()->tea->setDefaultType( 'STE' );									//  
			$template	= $this->env->getPage()->tea->getTemplate( $uri );							//  create template object for content file
			$template->setData( $data );															//  set given data
			return $template->render();																//  render template and return content
		}
		return UI_Template::render( $uri, $data );													//  render template with simple integrated but soon deprecated template engine
	}

	/**
	 *	Returns rendered Content of Template for a Controller Action.
	 *	@access		public
	 *	@param		string		$controller			Name of Controller
	 *	@param		string		$action				Name of Action
	 *	@param		array		$data				Additional Array of View Data
	 *	@return		string
	 */
	public function loadTemplate( $controller, $action, $data = array() )
	{
		$fileKey	= $controller.'/'.$action.'.php';
		$uri		= $this->getTemplateUri( $controller, $action );
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'Template "'.$controller.'/'.$action.'" is not existing', 311 );
		return $this->loadTemplateFile( $fileKey, $data );
	}

	public function loadTemplateFile( $fileName, $data = array() )
	{
		$___templateUri	= $this->getTemplateUriFromFile( $fileName );
		if( !file_exists( $___templateUri ) )
			throw new RuntimeException( 'Template "'.$fileName.'" is not existing', 311 );

		if( $this->env->getPage()->tea ){
			$data['this']		= $this;															//  
			$data['view']		= $this;															//  
			$data['env']		= $this->env;														//  
			$data['config']		= $this->env->getConfig();											//  
			$data['request']	= $this->env->getRequest();											//  
			$data['session']	= $this->env->getSession();											//  
			$data['helpers']	= $this->helpers;													//  
			$data	+= $this->data;																	//  
			$this->env->getPage()->tea->setDefaultType( 'PHP' );									//  
			$template	= $this->env->getPage()->tea->getTemplate( $___templateUri );				//  
			$template->setData( $data );															//  
			return $template->render();																//  
		}
		else{
			$content	= '';
			ob_start();
			$view		= $___view		= $this;													//  
			$env		= $___env		= $this->env;												//  
			$config		= $___config	= $this->env->getConfig();									//  
			$request	= $___request	= $this->env->getRequest();									//  
			$session	= $___session	= $this->env->getSession();									//  
			$___data	= $data;																	//  
			extract( $this->data );																	//  
			extract( $___data );																	//  
			$helpers	= $this->helpers;															//  
			$result		= require( $___templateUri );												//  
			$buffer		= ob_get_clean();															//  
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
		}
		return $content;
	}

	public function populateTexts( $keys, $path ){
		$list	= array();
		foreach( $keys as $key ){
			$uri	= $path.$key.'.html';
			$list[$key]	= "";
			if( $this->hasContentFile( $uri ) )
				$list[$key]	= $this->loadContentFile( $uri );
		}
		return $list;
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
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	protected function setEnv( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env			= $env;
		$this->controller	= $this->env->getRequest()->get( 'controller' );
		$this->action		= $this->env->getRequest()->get( 'action' );
	}
}
?>
