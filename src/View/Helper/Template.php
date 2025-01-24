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
namespace CeusMedia\HydrogenFramework\View\Helper;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Exception\Deprecation;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\HydrogenFramework\View\Helper;

use Exception;
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
class Template
{
	/**	@var	array					$data			Collected Data for View */
	protected array $data				= [];

	/**	@var	Environment				$env			Environment Object */
	protected Environment $env;

	/**	@var	string|NULL				$fileKey		Key of template file, build from called controller and action */
	protected ?string $fileKey			= NULL;

	/**	@var	Dictionary				$helpers		Map of view helper classes/objects */
	protected Dictionary $helpers;

	/**	@var	string					$pathTemplates	Path to template file, can be set by config::path.templates */
	protected string $pathTemplates		= 'templates/';

	/** @var	bool					$renderContent	Flag: inject content blocks of modules */
	protected bool $renderContent		= TRUE;

	protected ?View $view				= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Framework Resource Environment Object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->helpers	= new Dictionary();
		/** @var WebEnvironment $env */
//		$this->view		= new View( $env );

		if( NULL !== $this->env->getConfig()->get( 'path.templates' ) )
			$this->pathTemplates	= $this->env->getConfig()->get( 'path.templates' );
		if( '' === trim( $this->pathTemplates ) )
			$this->pathTemplates	= './';
		if( !str_starts_with( $this->pathTemplates, '/' ) )
			$this->pathTemplates	= $this->env->uri.$this->pathTemplates;
		if( !file_exists( $this->pathTemplates ) )													//  templates folder is not existing
			throw new RuntimeException( 'Templates folder "'.$this->pathTemplates.'" is missing' );	//  quit with exception
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
		return $this->setData( [$key => $value], $topic );
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
		if( is_object( $objectOrClassName ) )
			$object	= $objectOrClassName;
		else{
			$object	= ObjectFactory::createObject( $objectOrClassName, $parameters );
			if( !$object instanceof Helper )
				throw new RuntimeException( 'Helper class "'.$name.'" is not a helper instance', 301 );
		}

		if( is_callable( [$object, 'setEnv'] ) )
			$object->setEnv( $this->env );
		$this->helpers->set( $name, $object );
		return $this;
	}

	/**
	 *	@param		string|NULL		$key
	 *	@param		mixed|NULL		$autoSetTo
	 *	@return		array|mixed
	 */
	public function & getData( string $key = NULL, mixed $autoSetTo = NULL ): mixed
	{
		if(!$key )
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

	public function hasData( string $key ): bool
	{
		return isset( $this->data[$key] );
	}

	public function hasHelper( string $name ): bool
	{
		return isset( $this->helpers[$name] );
	}

	public function hasTemplate(): bool
	{
		return file_exists( $this->pathTemplates.$this->fileKey );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		$filePath	= $this->pathTemplates.$this->fileKey;

		if( !file_exists( $filePath ) )
			throw new RuntimeException( 'Template "'.$filePath.'" is not existing', 311 );

		/*  --  PREPARE DATA BY ASSIGNED AND ADDITIONAL  --  */
//		$content	= '';

		//  new solution
//		$payload	= (object) ['filePath' => $uri, 'data' => $this->data, 'content' => ''];
//		$result	= $this->env->getCaptain()->callHook( 'View', 'renderContent', $this, $payload );
//		if( $result )
//			return $this->renderContent( $payload->content );										//  return loaded and rendered content

		//  old solution, extract to module UI_TemplateAbstraction
		/*  --  LOAD TEMPLATE AND APPLY DATA  --  */

		$content	= $this->realizeTemplate( $filePath, $this->data );								//
		if( $this->renderContent )
			$content	= Content::applyContentProcessors( $this->env, $this, $content );
		return $content;																			//  return loaded and rendered content
	}

	/**
	 *	Sets Data of View.
	 *	@access		public
	 *	@param		array			$data			Array of Data for View
	 *	@param		string|NULL		$topic			Optional: Topic Name of Data
	 *	@return		static
	 */
	public function setData( array $data, ?string $topic = NULL ): static
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

	public function setRenderContent( bool $switch ): static
	{
		$this->renderContent	= $switch;
		return $this;
	}

	public function setTemplateKey( string $fileKey ): static
	{
		$this->fileKey		= $fileKey;
		return $this;
	}

	public function setView( View $view ): static
	{
		$this->view	= $view;
		return $this;
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
	 *	...
	 *	Check if template file is existing MUST be done beforehand.
	 *	@param		string		$filePath		Template file path name with templates folder
	 *	@param		array		$data			Additional template data, appended to assigned view data
	 *	@return		string		Template content with applied data
	 *	@throws		ReflectionException
	 */
	protected function realizeTemplate( string $filePath, array $data = [] ): string
	{
		$payload	= [
			'filePath'	=> $filePath,
			'data'			=> [
				'view'		=> $this->view,
				'env'		=> $this->env,
				'config'	=> $this->env->getConfig(),
				'helpers'	=> $this->helpers,
			],
			'content'		=> '',
		];
		$payload['data']	= array_merge( $data, $payload['data'] );


		ob_start();
		$this->env->getCaptain()->callHook( 'View', 'realizeTemplate', $this, $payload );
		$content	= $payload['content'];

		if( '' === $content )																	//  no hook realized template
			$content	= $this->realizeTemplateWithInclude( $filePath, $data );			//  realize with own strategy

		//  handle output buffer of template realization
		$buffer	= (string) ob_get_clean();														//  get standard output buffer
		/** @var string $buffer */
		$buffer	= preg_replace( '/^(<br( ?\/)?>)+/s', '', $buffer );
		$buffer	= trim( $buffer );
		if( strlen( $buffer ) ){																//  there is something in buffer
			if( !is_string( $content ) )														//  the view did not return content
				$content	= $buffer;															//  use buffer as content
			else if( $this->env->getMessenger() )												//  otherwise use messenger
				$this->env->getMessenger()->noteFailure( nl2br( $buffer ) );					//  note as failure
			else if( !$this->env->getLog()?->log( 'error', $buffer, $this ) )					//  logging failed
				throw new RuntimeException( $buffer );											//  throw exception
		}
		return (string) $content;
	}

	/**
	 *	ATTENTION: This is for legay only.
	 *  Needed to support templates which, load sub templates using $this->loadTemplateFile()
	 *
	 *	Tries to load several content files and maps them by prefixed IDs.
	 *	@access		public
	 *	@param		array		$keys		List of file keys (without .html extension)
	 *	@param		string		$path		Path to files within locales, like "html/controller/action/"
	 *	@return		array<string,string>	Prefixed map of collected file contents mapped by prefixed IDs
	 *	@throws		ReflectionException
	 */
	public function populateTexts( array $keys, string $path, array $data = [], string $prefix = "text" ): array
	{
		$this->env->getLog()?->logException( Deprecation::create()
			->setMessage( 'Calling this->populateTexts in templates is deprecated')
			->setDescription( 'A template file still has old syntax to populate texts.' )
			->setSuggestion( 'Replace by $view->populateTexts()' )
		);
		if( NULL === $this->view )
			return [];
		return $this->view->populateTexts( $keys, $path, $data, $prefix );
	}

	protected function loadContentFile( string $fileKey, array $data = [], string $path = NULL ): string
	{
		$this->env->getLog()?->logException( Deprecation::create()
			->setMessage( 'Calling this->loadContentFile in templates is deprecated')
			->setDescription( 'A template file still has old syntax to load nested templates.' )
			->setSuggestion( 'Replace by $view->loadContentFile()' )
		);
		if( NULL === $this->view )
			return '';
		try{
			return $this->view->loadContentFile( $fileKey, $data, $path );
		}
		catch( Throwable $e ){
			$this->env->getLog()?->logException( $e );
		}
		return '';
	}


	//  --  PRIVATE  --  //

	private function realizeTemplateWithInclude( string $filePath, array $data = [] ): string
	{
		$___templateUri	= $filePath;
		extract( $data );																		//
		$view		= $___view		= $this->view;													//
		$env		= $___env		= $this->env;												//
		$config		= $___config	= $this->env->getConfig();									//
		$helpers	= $___helpers	= $this->helpers;											//

		try{
			$content	= include( $___templateUri );											//  render template by include
			if( $content === FALSE )
				throw new RuntimeException( 'Template file "'.$___templateUri.'" is not existing' );
		}
		catch( Exception $e ){
			$message	= 'Rendering template file "%1$s" failed: %2$s';
			$message	= sprintf( $message, $filePath, $e->getMessage() );
			$this->env->getLog()?->log( 'error', $message, $this );
			if( !$this->env->getLog()?->logException( $e, $this ) )
				throw new RuntimeException( $message, 0, $e  );
			$this->env->getMessenger()?->noteFailure( $message );
			return '';
		}

		return (string) $content;
	}
}
