<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Generic View Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2025 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Text\CamelCase as CamelCase;
use CeusMedia\Common\Alg\Time\Converter as TimeConverter;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View\Helper;
use CeusMedia\HydrogenFramework\View\Helper\Content as ContentHelper;
use CeusMedia\HydrogenFramework\View\Helper\Template as TemplateHelper;

use InvalidArgumentException;
use ReflectionException;
use RuntimeException;
use Throwable;

/**
 *	Generic View Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class View
{
	/**	@var	array					$data			Collected Data for View */
	protected array $data				= [];

	/**	@var	WebEnvironment|RemoteEnvironment			$env			Environment Object */
	protected WebEnvironment|RemoteEnvironment $env;

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

	/**
	 *	Applies hook View::onRenderContent to already created content
	 *	@param		WebEnvironment	$env
	 *	@param		object			$context
	 *	@param		string			$content		Already created content to apply hook to
	 *	@param		string			$dataType
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public static function renderContentStatic( WebEnvironment $env, object $context, string $content, string $dataType = 'HTML' ): string
	{
		return ContentHelper::applyContentProcessors( $env, $context, $content, $dataType );
	}

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment|RemoteEnvironment		$env			Framework Resource Environment Object
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __construct( WebEnvironment|RemoteEnvironment $env )
	{
		$env->getRuntime()->reach( 'View('.static::class.')::init start' );
		$this->setEnv( $env );

		$this->html		= new HtmlElements();
		$this->time		= new TimeConverter();
		$this->helpers	= new Dictionary();

		$env->getRuntime()->reach( 'View('.static::class.')::init done' );
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
	public function addData( string $key, mixed $value, ?string $topic = NULL ): self
	{
		return $this->setData( array( $key => $value ), $topic );
	}

	/**
	 *	@param		string			$name
	 *	@param		object|string	$objectOrClassName
	 *	@param		array			$parameters
	 *	@return		$this
	 *	@throws		ReflectionException
	 */
	public function addHelper( string $name, object|string $objectOrClassName, array $parameters = [] ): self
	{
		if( is_string( $objectOrClassName ) )
			return $this->registerHelper( $name, $objectOrClassName, $parameters );

		if( is_callable( [$objectOrClassName, 'setEnv'] ) )
			$objectOrClassName->setEnv( $this->env );
		$this->helpers->set( $name, $objectOrClassName );
		return $this;
	}

	/**
	 *	@param		string			$fileKey
	 *	@param		string|NULL		$path
	 *	@return		string
	 *	@deprecated
	 *	@todo		remove in 1.0
	 */
	public function getContentUri( string $fileKey, string $path = NULL ): string
	{
		Deprecation::getInstance()
			->setExceptionVersion( '1.0.0' )
			->message( 'Method View::getContentUri is deprecated' );
		return '';
	}

	/**
	 *	@param		string|NULL		$key
	 *	@param		mixed|NULL		$autoSetTo
	 *	@return		array|mixed
	 */
	public function & getData( string $key = NULL, mixed $autoSetTo = NULL )
	{
		if( NULL === $key || '' === trim( $key ) )
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
		if( !$this->helpers->has( $name ) ){
			if( !$strict )
				return NULL;
			throw new InvalidArgumentException( 'No view helper set by name "'.htmlentities( $name, ENT_QUOTES, 'UTF-8' ).'"' );
		}
		/** @var Helper $helper */
		$helper	= $this->helpers->get( $name );
		return $helper;
	}

	public function getHelpers(): Dictionary
	{
		return $this->helpers;
	}

	/**
	 *	@param		string		$controller
	 *	@param		string		$action
	 *	@param		?string		$path
	 *	@param		string		$extension
	 *	@return		bool
	 *	@deprecated	not usable and also not used in modules
	 *	@todo		remove in 1.0
	 */
	public function hasContent( string $controller, string $action, ?string $path = NULL, string $extension = '.html' ): bool
	{
		$fileKey	= $controller.'/'.$action.$extension;
		return $this->hasContentFile( $fileKey, $path );
	}

	public function hasContentFile( string $fileKey, ?string $path = NULL ): bool
	{
		$helper	= new ContentHelper( $this->env );
		try{
			$helper->setFileKey( $fileKey );
//			$helper->setPath( $path );
			return $helper->has();
		}
		catch( Throwable ){
			return FALSE;
		}
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
		return $this->hasTemplateFile( $controller.'/'.$action.'.php' );
	}

	public function hasTemplateFile( string $fileKey ): bool
	{
		$helper	= new TemplateHelper( $this->env );
		$helper->setTemplateKey( $fileKey );
		return $helper->hasTemplate();
	}

	/**
	 *	@param		string		$controller
	 *	@param		string		$action
	 *	@param		array		$data
	 *	@param		string		$extension
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@deprecated	not usable and also not used in modules
	 *	@todo		remove in 1.0
	 */
	public function loadContent( string $controller, string $action, array $data = [], string $extension = '.html' ): string
	{
		$fileKey	= 'html/'.$controller.'/'.$action.$extension;
		return $this->loadContentFile( $fileKey, $data );
	}

	/**
	 *	@param		string			$fileKey
	 *	@param		array			$data
	 *	@param		string|NULL		$path
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function loadContentFile( string $fileKey, array $data = [], ?string $path = NULL ): string
	{
		$loader	= new ContentHelper( $this->env );
		$loader->setFileKey( $fileKey );
		$loader->setData( $data );
		return $loader->render();
	}

	/**
	 *	Tries to load several content files within a path.
	 *	@access		public
	 *	@param		string		$path		Path to files within locales, like "html/controller/action/"
	 *	@param		array		$keys		List of file keys (without .html extension)
	 *	@return		array		Map of collected file contents
	 *	@throws		ReflectionException
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
	 *	@throws		ReflectionException
	 */
	public function loadTemplate( string $controller, string $action, array $data = [], bool $renderContent = TRUE ): string
	{
		$fileKey	= $controller.'/'.$action.'.php';
		return $this->loadTemplateFile( $fileKey, $data, $renderContent );
	}

	/**
	 *	@param		string		$fileName
	 *	@param		array		$data
	 *	@param		bool		$renderContent
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function loadTemplateFile( string $fileName, array $data = [], bool $renderContent = TRUE ): string
	{
		$templateHelper	= new TemplateHelper( $this->env );
		$templateHelper->setView( $this );
		$templateHelper->setTemplateKey( $fileName );
		$templateHelper->setRenderContent( $renderContent );
		$templateHelper->setData( array_merge( $this->data, $data, ['view' => $this] ) );
		/**
		 * @var string $name
		 * @var Helper $helper
		 */
		foreach( $this->getHelpers() as $name => $helper )
			$templateHelper->addHelper( $name, $helper );
		return $templateHelper->render();
	}

	/**
	 *	Tries to load several content files and maps them by prefixed IDs.
	 *	@access		public
	 *	@param		array		$keys		List of file keys (without .html extension)
	 *	@param		string		$path		Path to files within locales, like "html/controller/action/"
	 *	@return		array<string,string>	Prefixed map of collected file contents mapped by prefixed IDs
	 *	@throws		ReflectionException
	 */
	public function populateTexts( array $keys, string $path, array $data = [], string $prefix = 'text' ): array
	{
		$list	= [];																				//  prepare empty list
		$files	= $this->loadContentFiles( $path, $keys, $data );									//  try to load files
		foreach( $files as $key => $value ){														//  iterate file contents
			/** @var string $id */
			$id	= preg_replace( "/[^a-z]/i", ' ', $key );							//  replace not allowed characters
			$id	= '' !== $prefix ? $prefix.' '.$id : $id;											//  prepend prefix to ID if set
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

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string|NULL		$section	Section in locale file
	 *	@param		string|NULL		$topic		Locale file key, eg. test/my, default: current controller
	 *	@param		bool			$asObject	Return section array as object
	 *	@return		array|object
	 *	@todo		implement asObject on topic, breaks current templates
	 */
	protected function getWords( ?string $section = NULL, ?string $topic = NULL, bool $asObject = TRUE ): object|array
	{
		$topic	??= $this->controller;
		if( NULL === $topic )
			return [];
		if( NULL === $section )
			return $this->env->getLanguage()->getWords( $topic );
		$words	= $this->env->getLanguage()->getSection( $topic, $section );
		return $asObject ? (object) $words : $words;
	}

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

	/**
	 *	@param		string		$content
	 *	@param		string		$dataType
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function renderContent( string $content, string $dataType = 'HTML' ): string
	{
		return ContentHelper::applyContentProcessors( $this->env, $this, $content, $dataType );
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
		if( '' !== ( $topic ?? '' ) ){
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
	 *	@param		WebEnvironment|RemoteEnvironment		$env			Framework Resource Environment Object
	 *	@return		self
	 */
	protected function setEnv( WebEnvironment|RemoteEnvironment $env ): self
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
		/** @var array<string,string> $words */
		$words	= $this->getData( 'words', [] );
		if( isset( $words[$section][$key] ) ){
			$modes	= [-1 => 'prepend', 0 => 'set', 1 => 'append'];
			if( $this->env instanceof WebEnvironment )
				$this->env->getPage()->setTitle( $words[$section][$key], $modes[$mode] );
		}
		return $this;
	}
}
