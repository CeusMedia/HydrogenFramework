<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Generic View Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2023 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Text\CamelCase as CamelCase;
use CeusMedia\Common\Alg\Time\Converter as TimeConverter;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View\Helper;
use CeusMedia\TemplateEngine\Template as TemplateEngine;

use Exception;
use InvalidArgumentException;
use ReflectionException;
use RuntimeException;

/**
 *	Generic View Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class View
{
	/**	@var	array					$data			Collected Data for View */
	protected array $data				= [];

	/**	@var	WebEnvironment			$env			Environment Object */
	protected WebEnvironment $env;

	/**	@var	string|NULL				$controller		Name of called Controller */
	protected ?string $controller		= NULL;

	/**	@var	string|NULL				$action			Name of called Action */
	protected ?string $action			= NULL;

	/**	@var	Dictionary				$helpers		Map of view helper classes/objects */
	protected Dictionary $helpers;

	/**	@var	TimeConverter			$time			Instance of time converter */
	protected TimeConverter $time;

	/**	@var	HtmlElements			$html			Instance of HTML library class */
	protected HtmlElements $html;

//	/**	@var	CMM_TEA_Factory			$tea			Instance of TEA (Template Engine Abstraction) Factory (from cmModules) OR empty if TEA is not available */
//	protected $tea						= NULL;

	/**	@var	string					$pathTemplates	Path to template file, can be set by config::path.templates */
	protected string $pathTemplates		= 'templates/';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment		$env			Framework Resource Environment Object
	 *	@return		void
	 */
	public function __construct( WebEnvironment $env )
	{
		$env->getRuntime()->reach( 'CMF_View('.get_class( $this ).')::init start' );
		$this->setEnv( $env );
		$this->html		= new HtmlElements();
		$this->time		= new TimeConverter();
		$this->helpers	= new Dictionary();

		if( NULL !== $this->env->getConfig()->get( 'path.templates' ) )
			$this->pathTemplates	= $this->env->getConfig()->get( 'path.templates' );
		if( 0 === strlen( trim( $this->pathTemplates ) ) )
			$this->pathTemplates	= './';
		if( !file_exists( $this->pathTemplates ) )													//  templates folder is not existing
			throw new RuntimeException( 'Templates folder "'.$this->pathTemplates.'" is missing' );	//  quit with exception
		$env->getRuntime()->reach( 'CMF_Controller('.get_class( $this ).')::init done' );
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
//		$this->env->getMessenger()->noteNotice( "View::Construct: ".get_class( $this ) );
		$this->env->getCaptain()->callHook( 'View', 'onConstruct', $this );
		$this->__onInit();
	}

	/**
	 *	Sets Data of View.
	 *	@access		public
	 *	@param		string			$key
	 *	@param		mixed			$value
	 *	@param		string|NULL		$topic			Optional: Topic Name of Data
	 *	@return		self
	 */
	public function addData( string $key, $value, ?string $topic = NULL ): self
	{
		return $this->setData( array( $key => $value ), $topic );
	}

	/**
	 *	@param		string			$name
	 *	@param		object|string	$object
	 *	@param		array			$parameters
	 *	@return		$this
	 *	@throws		ReflectionException
	 */
	public function addHelper( string $name, $object, array $parameters = [] ): self
	{
		if( is_object( $object ) ){
			if( is_callable( [$object, 'setEnv'] ) )
				$object->setEnv( $this->env );
			$this->helpers->set( $name, $object );
		}
		else
			$this->registerHelper( $name, $object, $parameters );
		return $this;
	}

	public function getContentUri( string $fileKey, string $path = NULL ): string
	{
		$path		= preg_replace( '/^(.+)(\/)*$/U', '\\1/', $path ?? '' );
		$pathLocale	= $this->env->getLanguage()->getLanguagePath();
		return $pathLocale.$path.$fileKey;
	}

	/**
	 *	@param		string|NULL		$key
	 *	@param		mixed|NULL		$autoSetTo
	 *	@return		array|mixed
	 */
	public function & getData( string $key = NULL, $autoSetTo = NULL )
	{
		if( !$key )
			return $this->data;
		if( !isset( $this->data[$key] ) && !is_null( $autoSetTo ) )
			$this->addData( $key, $autoSetTo );
		if( isset( $this->data[$key] ) )
			return $this->data[$key];
		throw new InvalidArgumentException( 'No view data by key "'.htmlentities( $key, ENT_QUOTES, 'UTF-8' ).'"' );
	}

	/**
	 *	@param		string $name
	 *	@param		bool $strict
	 *	@return		Helper|NULL
	 */
	public function getHelper( string $name, bool $strict = TRUE ): ?Helper
	{
		if( isset( $this->helpers[$name] ) )
			return $this->helpers[$name];
		if( !$strict )
			return NULL;
		throw new InvalidArgumentException( 'No view helper set by name "'.htmlentities( $name, ENT_QUOTES, 'UTF-8' ).'"' );
	}

	public function getHelpers(): Dictionary
	{
		return $this->helpers;
	}

	public function hasContent( string $controller, string $action, ?string $path = NULL, string $extension = '.html' ): bool
	{
		$fileKey	= $controller.'/'.$action.$extension;
		return $this->hasContentFile( $fileKey, $path );
	}

	public function hasContentFile( string $fileKey, ?string $path = NULL ): bool
	{
		$uri	= $this->getContentUri( $fileKey, $path );
		return file_exists( $uri );
	}

	public function hasData( string $key ): bool
	{
		return isset( $this->data[$key] );
	}

	public function hasHelper( string $name ): bool
	{
		return isset( $this->helpers[$name] );
	}

	public function hasTemplate( string $controller, string $action ): bool
	{
		$uri		= $this->getTemplateUri( $controller, $action );
		return file_exists( $uri );
	}

	public function hasTemplateFile( string $fileKey ): bool
	{
		$uri	= $this->getTemplateUriFromFile( $fileKey );
		return file_exists( $uri );
	}

	public function loadContent( string $controller, string $action, array $data = [], string $extension = '.html' ): string
	{
		$fileKey	= 'html/'.$controller.'/'.$action.$extension;
		return $this->loadContentFile( $fileKey, $data );
	}

	/**
	 *	@todo	remove use of TemplateEngine (aka. UI_Template)
	 */
	public function loadContentFile( string $fileKey, array $data = [], ?string $path = NULL ): string
	{
		if( !is_array( $data ) )																	//  no data given
			$data	= [];																		//  ensure empty array
		$uri	= $this->getContentUri( $fileKey, $path );											//  calculate file pathname
		if( !file_exists( $uri ) )																	//  content file is not existing
			throw new RuntimeException( 'Locale content file "'.$fileKey.'" is missing.', 321 );	//  throw exception
//		$data	= array_merge( $this->data, $data );

		//  new solution
//		$payload	= (object) ['filePath' => $uri, 'data' => $data, 'content' => ''];
//		$result	= $this->env->getCaptain()->callHook( 'View', 'renderContent', $this, $payload );
//		if( $result )
//			return $this->renderContent( $payload->content );										//  return loaded and rendered content

		//  old solution, extract to module UI_TemplateAbstraction
//		if( 0 && $this->env->getPage()->tea ){														//  template engine abstraction is enabled
//			$this->env->getPage()->tea->setDefaultType( 'STE' );									//
//			$template	= $this->env->getPage()->tea->getTemplate( $uri );							//  create template object for content file
//			$template->setData( $data );															//  set given data
//			$content	= $template->render();														//  render template
//		}
//		else
			$content	= TemplateEngine::renderFile( $uri, $data );									//  render template with integrated template engine

		return $this->renderContent( $content );												//  apply modules to content
	}

	/**
	 *	Tries to load several content files within a path.
	 *	@access		public
	 *	@param		string		$path		Path to files within locales, like "html/controller/action/"
	 *	@param		array		$keys		List of file keys (without .html extension)
	 *	@return		array		Map of collected file contents
	 */
	public function loadContentFiles( string $path, array $keys, array $data = [] ): array
	{
		$list	= [];
		$path	= preg_replace( "/\/+$/", "", $path ).'/';											//  correct path
		foreach( $keys as $key ){																	//  iterate keys
			$url		= $path.$key.'.html';														//  build filename
			$list[$key]	= '';																		//  default: empty block in map
			if( $this->hasContentFile( $url ) ){													//  content file is available
				$content	= $this->loadContentFile( $url, $data );								//  load file content
				$list[$key]	= $content;																//  append content to map
			}
		}
		return $list;																				//  return collected content map
	}

	/**
	 *	Returns rendered Content of Template for a Controller Action.
	 *	@access		public
	 *	@param		string		$controller			Name of Controller
	 *	@param		string		$action				Name of Action
	 *	@param		array		$data				Additional Array of View Data
	 *	@param		boolean		$renderContent		Flag: inject content blocks of modules
	 *	@return		string
	 */
	public function loadTemplate( string $controller, string $action, array $data = [], bool $renderContent = TRUE ): string
	{
		$fileKey	= $controller.'/'.$action.'.php';
		$uri		= $this->getTemplateUri( $controller, $action );
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'Template "'.$controller.'/'.$action.'" is not existing', 311 );
		return $this->loadTemplateFile( $fileKey, $data, $renderContent );
	}

	public function loadTemplateFile( string $fileName, array $data = [], bool $renderContent = TRUE ): string
	{
		$filePath	= $this->getTemplateUriFromFile( $fileName );
		if( !file_exists( $filePath ) )
			throw new RuntimeException( 'Template "'.$filePath.'" is not existing', 311 );

		/*  --  PREPARE DATA BY ASSIGNED AND ADDITIONAL  --  */
		if( isset( $this->data['this' ] ) )
			unset( $this->data['this'] );
		if( isset( $data['this' ] ) )
			unset( $data['this'] );
		$data		= array_merge( $this->data, $data );											//
//		$content	= '';

		//  new solution
//		$payload	= (object) ['filePath' => $uri, 'data' => $data, 'content' => ''];
//		$result	= $this->env->getCaptain()->callHook( 'View', 'renderContent', $this, $payload );
//		if( $result )
//			return $this->renderContent( $payload->content );										//  return loaded and rendered content

		//  old solution, extract to module UI_TemplateAbstraction
		/*  --  LOAD TEMPLATE AND APPLY DATA  --  */
//		if( $this->env->getPage()->tea )
//			$content	= $this->realizeTemplateWithTEA( $filePath, $data );
//		else
			$content	= $this->realizeTemplate( $filePath, $data );								//
		if( $renderContent )
			$content	= $this->renderContent( $content );											//  apply modules to content
		return $content;																			//  return loaded and rendered content
	}

	/**
	 *	Tries to load several content files and maps them by prefixed IDs.
	 *	@access		public
	 *	@param		array		$keys		List of file keys (without .html extension)
	 *	@param		string		$path		Path to files within locales, like "html/controller/action/"
	 *	@return		array<string,string>	Prefixed map of collected file contents mapped by prefixed IDs
	 */
	public function populateTexts( array $keys, string $path, array $data = [], string $prefix = "text" ): array
	{
		$list	= [];																				//  prepare empty list
		$files	= $this->loadContentFiles( $path, $keys, $data );									//  try to load files
		foreach( $files as $key => $value ){														//  iterate file contents
			$id	= preg_replace( "/[^a-z]/i", " ", $key );						//  replace not allowed characters
			$id	= $prefix ? $prefix." ".$id : $id;													//  prepend prefix to ID if set
			$id	= CamelCase::convert( $id, FALSE );									//  build camel-cased ID
			$list[$id]	= $value;																	//  append content to map
		}
		return $list;																				//  return map of collected files
	}

	//  --  PROTECTED  --  //

	/**
	 *	Empty method which is called after construction and can be customised.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(): void
	{
	}

	protected function getTemplateUri( string $controller, string $action ): string
	{
		$fileKey	= $controller.'/'.$action.'.php';
		return $this->getTemplateUriFromFile( $fileKey );
	}

	/**
	 *	Returns File Name of Template.
	 *	Uses config::path.templates and defaults to 'templates/'.
	 *	@access		protected
	 *	@param		string		$fileKey		File key, like: controller/action.php
	 *	@return		string
	 */
	protected function getTemplateUriFromFile( string $fileKey ): string
	{
		return $this->pathTemplates.$fileKey;
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string|NULL		$section	Section in locale file
	 *	@param		string|NULL		$topic		Locale file key, eg. test/my, default: current controller
	 *	@return		array|object
	 */
	protected function getWords( ?string $section = NULL, ?string $topic = NULL )
	{
		if( empty( $topic ) /*&& $this->env->getLanguage()->hasWords( $this->controller ) */)
			$topic = $this->controller;
		if( empty( $section ) )
			return $this->env->getLanguage()->getWords( $topic );
		return (object) $this->env->getLanguage()->getSection( $topic, $section );
	}

	/**
	 *	...
	 *	Check if template file is existing MUST be done beforehand.
	 *	@param		string		$filePath		Template file path name with templates folder
	 *	@param		array		$data			Additional template data, appended to assigned view data
	 *	@return		string		Template content with applied data
	 */
	protected function realizeTemplate( string $filePath, array $data = [] ): string
	{
		$___content	= '';
		$___templateUri	= $filePath;
		ob_start();
		extract( $data );																		//
		$view		= $___view		= $this;													//
		$env		= $___env		= $this->env;												//
		$config		= $___config	= $this->env->getConfig();									//
		$request	= $___request	= $this->env->getRequest();									//
		$session	= $___session	= $this->env->getSession();									//
		$helpers	= $___helpers	= $this->helpers;											//

		try{
			$content	= include( $___templateUri );											//  render template by include
			if( $content === FALSE )
				throw new RuntimeException( 'Template file "'.$___templateUri.'" is not existing' );
		}
		catch( Exception $e ){
			$message	= 'Rendering template file "%1$s" failed: %2$s';
			$message	= sprintf( $message, $filePath, $e->getMessage() );
			$this->env->getLog()->log( 'error', $message, $this );
			if( !$this->env->getLog()->logException( $e, $this ) )
				throw new RuntimeException( $message, 0, $e  );
			else if( $this->env->getMessenger() )
				$this->env->getMessenger()->noteFailure( $message );
		}
		$buffer	= (string) ob_get_clean();
		$buffer	= trim( preg_replace( '/^(<br( ?\/)?>)+/s', '', $buffer ) );			//  get standard output buffer
		if( strlen( $buffer ) ){																//  there is something in buffer
			if( !is_string( $content ) )														//  the view did not return content
				$content	= $buffer;															//  use buffer as content
			else if( $this->env->getMessenger() )												//  otherwise use messenger
				$this->env->getMessenger()->noteFailure( nl2br( $buffer ) );					//  note as failure
			else if( !$this->env->getLog()->log( 'error', $buffer, $this ) )					//  logging failed
				throw new RuntimeException( $buffer );											//  throw exception
		}
		return (string) $content;
	}

//	/**
//	 *	...
//	 *	Check if template file is existing MUST be done beforehand.
//	 *	@param		string		$filePath		Template file path name with templates folder
//	 *	@param		array		$data			Additional template data, appended to assigned view data
//	 *	@return		string		Template content with applied data
//	 */
/*	protected function realizeTemplateWithTEA( string $filePath, array $data = [] ): string
	{
		$data['view']		= $this;															//
		$data['env']		= $this->env;														//
		$data['config']		= $this->env->getConfig();											//
		$data['request']	= $this->env->getRequest();											//
		$data['session']	= $this->env->getSession();											//
		$data['helpers']	= $this->helpers;													//
		$this->env->getPage()->tea->setDefaultType( 'PHP' );									//
		$template	= $this->env->getPage()->tea->getTemplate( $filePath );						//
		$template->setData( $data );															//
		return $template->render();																//  render content with template engine
	}*/

	/**
	 *	@param		string		$name
	 *	@param		string		$class
	 *	@param		array		$parameters
	 *	@return		$this
	 *	@throws		ReflectionException
	 */
	protected function registerHelper( string $name, string $class, array $parameters = [] ): self
	{
		$object	= ObjectFactory::createObject( $class, $parameters );
		if( !$object instanceof Helper )
			throw new RuntimeException( 'View class "'.$name.'" is not a Hydrogen view', 301 );
		$this->addHelper( $name, $object );
		return $this;
	}

	public function renderContent( string $content, string $dataType = "HTML" ): string
	{
		return static::renderContentStatic( $this->env, $this, $content, $dataType );
	}

	/**
	 *	Applies hook View::onRenderContent to already created content
	 *	@param		WebEnvironment	$env
	 *	@param		object			$context
	 *	@param		string			$content		Already created content to apply hook to
	 *	@param		string			$dataType
	 *	@return		string
	 */
	public static function renderContentStatic( WebEnvironment $env, object $context, string $content, string $dataType = "HTML" ): string
	{
		$payload	= [
			'content'	=> $content,
			'type'		=> $dataType
		];
		$env->getCaptain()->callHook( 'View', 'onRenderContent', $context, $payload );
		return $payload['content'];
	}

	/**
	 *	Sets Data of View.
	 *	@access		public
	 *	@param		array			$data			Array of Data for View
	 *	@param		string|NULL		$topic			Optional: Topic Name of Data
	 *	@return		self
	 */
	public function setData( array $data, ?string $topic = NULL ): self
	{
		if( $topic ){
			if( !isset( $this->data[$topic] ) )
				$this->data[$topic]	= [];
			foreach( $data as $key => $value )
				$this->data[$topic][$key]	= $value;
		}
		else{
			foreach( $data as $key => $value )
				$this->data[$key]	= $value;
		}
		return $this;
	}

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		WebEnvironment		$env			Framework Resource Environment Object
	 *	@return		self
	 */
	protected function setEnv( WebEnvironment $env ): self
	{
		$this->env			= $env;
		$this->controller	= $this->env->getRequest()->get( '__controller' );
		$this->action		= $this->env->getRequest()->get( '__action' );
		return $this;
	}

	/**
	 *	Sets HTML page title from language file assigned by controller.
	 *	Lets you select a language section and key and inserts given data.
	 *	Can set a new page title or append or prepend to currently set title.
	 *	Usage: Call this method in your view methods!
	 *	@access		protected
	 *	@param		string		$section		Section in language file of current controller
	 *	@param		string		$key			Pair key in this section
	 *	@param		array		$data			List of arguments to insert using sprintf
	 *	@param		integer		$mode			Concat mode: 0 - set | 1 - append, -1 - prepend
	 *	@return		self
	 */
	protected function setPageTitle( string $section = 'index', string $key = 'title', array $data = [], int $mode = 1 ): self
	{
		$data	= $this->getData();
		if( isset( $data['words'][$section][$key] ) ){
			$modes	= [-1 => 'prepend', 0 => 'set', 1 => 'append'];
			$this->env->getPage()->setTitle( $data['words'][$section][$key], $modes[$mode] );
		}
		return $this;
	}
}
