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

use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\Common\FS\File;
use CeusMedia\Common\FS\File\Reader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\TemplateEngine\Template as TemplateEngine;

use InvalidArgumentException;
use ReflectionException;
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
class Content
{
	/**	@var	array						$data			Collected Data for View */
	protected array $data					= [];

	/**	@var	Environment					$env			Environment Object */
	protected Environment $env;

	/**	@var	string						$fileKey		Key of content file */
	protected string $fileKey				= '';

	protected string $content				= '';
	protected string $dataType				= 'HTML';

	protected bool $useContentProcessors	= TRUE;
	protected bool $useTemplateEngines		= TRUE;

	/**
	 *	Applies hook View::onRenderContent to already created content
	 *	@param		Environment		$env
	 *	@param		object			$context
	 *	@param		string			$content		Already created content to apply hook to
	 *	@param		string			$dataType
	 *	@return		string
	 */
	public static function applyContentProcessors( Environment $env, object $context, string $content, string $dataType = 'HTML' ): string
	{
		$payload	= [
			'content'	=> $content,
			'type'		=> $dataType
		];
		try{
			$env->getCaptain()->callHook( 'View', 'onRenderContent', $context, $payload );
		}
		catch( Throwable $e ){
			$env->getLog()?->logException( $e );
		}
		return $payload['content'];
	}

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Framework Resource Environment Object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->__onInit();
	}

	/**
	 *	Sets Data of View.
	 *	@access		public
	 *	@param		string			$key
	 *	@param		mixed			$value
	 *	@param		string|NULL		$topic			Optional: Topic Name of Data
	 *	@return		static
	 */
	public function addData( string $key, mixed $value, ?string $topic = NULL ): static
	{
		return $this->setData( [$key => $value], $topic );
	}

	/**
	 *	@param		string|NULL		$key
	 *	@param		mixed|NULL		$autoSetTo
	 *	@return		array|mixed
	 */
	public function & getData( string $key = NULL, mixed $autoSetTo = NULL ): mixed
	{
		if( NULL === $key )
			return $this->data;
		if( !isset( $this->data[$key] ) && !is_null( $autoSetTo ) )
			$this->addData( $key, $autoSetTo );
		if( isset( $this->data[$key] ) )
			return $this->data[$key];
		throw new InvalidArgumentException( 'No view data by key "'.htmlentities( $key, ENT_QUOTES, 'UTF-8' ).'"' );
	}

	/**
	 *	@param		?string		$path
	 *	@return		bool
	 */
	public function has( ?string $path = NULL ): bool
	{
		$uri	= $this->getContentUri( $this->fileKey, $path );
		return file_exists( $uri );
	}

	/**
	 *	@param		string		$key
	 *	@return		bool
	 */
	public function hasData( string $key ): bool
	{
		return isset( $this->data[$key] );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		$content	= $this->content;
		if( $this->useTemplateEngines )															//  apply data to content
			$content	= TemplateEngine::renderString( $content, $this->data );
		if( $this->useContentProcessors )														//  apply modules to content
			$content	= static::applyContentProcessors( $this->env, $this, $content, $this->dataType );
		return $content;
	}

	/**
	 *	Sets content to work on.
	 *	Will be done on setting a file key.
	 *	This method can override the latest loaded file.
	 *
	 *	@param		string		$content
	 *	@return		static
	 */
	public function setContent( string $content ): static
	{
		$this->content	= $content;
		return $this;
	}

	/**
	 *	Enable or disable to apply content processors.
	 *	@param		bool		$switch		Flag, default: yes
	 *	@return		static
	 */
	public function setRenderContent( bool $switch = TRUE ): static
	{
		$this->useContentProcessors	= $switch;
		return $this;
	}

	/**
	 *	Sets Data of View.
	 *	@access		public
	 *	@param		array		$data		Array of Data for View
	 *	@param		?string		$topic		Optional: Topic Name of Data
	 *	@return		static
	 */
	public function setData( array $data, ?string $topic = NULL ): static
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
	 *	@param		string		$type
	 *	@return		static
	 */
	public function setDataType( string $type = 'HTML' ): static
	{
		$this->dataType	= $type;
		return $this;
	}

	/**
	 *	@param		string		$fileKey
	 *	@return		static
	 *	@throws		FileNotExistingException	if strict & file is not existing
	 *	@throws		FileNotExistingException	if strict & given path is not a file
	 */
	public function setFileKey( string $fileKey ): static
	{
		if( '' !== $fileKey ){
			$uri	= $this->getContentUri( $fileKey, '' );									//  calculate file pathname
			$file	= new File( $uri );
			$file->exists( TRUE );
			$this->content	= Reader::load( $uri ) ?? '';
		}
		$this->fileKey	= $fileKey;
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
	 *	@param		string		$fileKey
	 *	@param		?string		$path
	 *	@return		string
	 */
	protected function getContentUri( string $fileKey, string $path = NULL ): string
	{
		$path		= preg_replace( '/^(.+)(\/)*$/U', '\\1/', $path ?? '' );
		$pathLocale	= $this->env->getLanguage()->getLanguagePath();
		return $pathLocale.$path.$fileKey;
	}
}
