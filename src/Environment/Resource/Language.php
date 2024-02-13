<?php
/**
 *	Language Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2024 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\FS\File\INI\Reader as IniFileReader;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\Folder\Lister as FolderLister;
use CeusMedia\HydrogenFramework\Environment;

use RuntimeException;
use InvalidArgumentException;
use DomainException;

/**
 *	Language Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Language
{
	/**	@var		string					$fileExtension	File extension of language files (default: ini) */
	static public string $fileExtension		= 'ini';

	/**	@var		array					$data			Array of loaded Language File Definitions */
	protected array $data;

	/**	@var		Environment				$env			Application Environment Object */
	protected Environment $env;

	/**	@var		string					$filePath		Path to Language Files */
	protected string $filePath;

	/**	@var		string					$language		Set Language */
	protected string $language				= '';

	/**	@var		array					$languages		List of allowed Languages */
	protected array $languages				= [];

	/**
	 *	Constructor.
	 *	Uses config::path.locales and defaults to 'locales/'.
	 *	@access		public
	 *	@param		Environment			$env			Application Environment Object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$config				= $env->getConfig();

		$this->filePath		= $env->uri.'locales/';													//  assume default folder name
		if( $config->has( 'path.locales' ) )														//  a locales folder has been configured
			$this->filePath	= $env->uri.$config->get( 'path.locales' );								//  take the configured folder name
		if( !file_exists( $this->filePath ) ){														//  locales folder is not existing
			$message	= sprintf( 'Locales folder "%s" is missing', $this->filePath );
			throw new RuntimeException( $message );													//  quit with exception
		}
		if( $config->get( 'locale.allowed' ) )														//  allowed languages have been set
			foreach( explode( ',', $config['locale.allowed'] ) as $language )					//  iterate extracted languages
				$this->languages[]	= trim( $language );											//  save language without surrounding spaces
		else																						//  otherwise scan locales folder
			foreach( FolderLister::getFolderList( $this->filePath ) as $folder )				//  iterate found locale folders
				$this->languages[]	= $folder->getFilename();										//  save locale folder as language
		$language			= $config->has( 'locale.default' ) ? $config['locale.default'] : 'en';

		if( $this->env->has( 'session' ) ){
			$session	= $this->env->getSession();
			$switchTo	= $this->env->getRequest()->get( 'switchLanguageTo' );
			if( $switchTo && in_array( $switchTo, $this->languages ) ){
				$session->set( 'language', $switchTo );
				if( !empty( $_SERVER['HTTP_REFERER'] ) ){
					$referer = $_SERVER['HTTP_REFERER'];
					if( !preg_match( '/switchLanguageTo/', $referer ) ){
						header( 'Location: '.$referer );
						exit;
					}
				}
			}
			if( $session->get( 'language' ) )
				$language	= $session->get( 'language' );
		}
		$this->setLanguage( $language ?? '' );
//		@todo remove: title is not longer existing in environment
//		$words	= $this->getWords( 'main', FALSE );
//		if( !empty( $words['main']['title'] ) )
//			$env->title	= $words['main']['title'];
	}

	/**
	 *	Returns selected Language.
	 *	@access		public
	 *	@return		string
	 */
	public function getLanguage(): string
	{
		return $this->language;
	}

	/**
	 *	Returns selected Language.
	 *	@access		public
	 *	@return		string
	 */
	public function getLanguagePath(): string
	{
		return $this->filePath.$this->language.'/';
	}

	/**
	 *	Returns list of allowed languages.
	 *	@access		public
	 *	@return		array
	 */
	public function getLanguages(): array
	{
		return $this->languages;
	}

	/**
	 *	Returns array of language sections within a language topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		bool		$strict			Flag: throw exceptions on error, default: yes
	 *	@param		bool		$force			Flag: show error messages on error and strict mode is off, default: yes
	 *	@return		array
	 *	@throws		RuntimeException if language topic is not loaded/existing and strict is on
	 */
	public function getWords( string $topic, bool $strict = TRUE, bool $force = TRUE ): array
	{
		if( !strlen( trim( $topic ) ) )
			throw new InvalidArgumentException( "getWords: Topic cannot be empty" );

		if( !isset( $this->data[$topic] ) )
			$this->load( $topic, $strict, $force );
		if( isset( $this->data[$topic] ) )
			return $this->data[$topic];

		$message	= 'Invalid language topic "'.$topic.'"';
		if( $strict )
			throw new RuntimeException( $message, 221 );
		if( $force && $this->env->has( 'messenger' ) )
			$this->env->getMessenger()->noteFailure( $message );
		return [];
	}

	public function hasWords( string $topic ): bool
	{
		return isset( $this->data[$topic] );
	}

	/**
	 *	Returns map of language pairs with a language section of a language topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		string		$section		Section of Language
	 *	@param		bool		$strict			Flag: throw exceptions on error, default: yes
	 *	@param		bool		$force			Flag: show error messages on error and strict mode is off, default: yes
	 *	@return		array
	 *	@throws		RuntimeException if language section is not existing and strict is on
	 */
	public function getSection( string $topic, string $section, bool $strict = TRUE, bool $force = TRUE ): array
	{
		$sections	= $this->getWords( $topic, $strict, $force );
		if( isset( $sections[$section] ) )
			return $sections[$section];
		$message	= 'Invalid language section "'.$section.'" in topic "'.$topic.'"';
		if( $strict )
			throw new RuntimeException( $message, 221 );
		if( $force && $this->env->has( 'messenger' ) )
			$this->env->getMessenger()->noteFailure( $message );
		return [];
	}

	/**
	 *	Returns File Name of Language Topic.
	 *	@access		protected
	 *	@param		string		$topic			Topic of Language
	 *	@return		string
	 */
	protected function getFilenameOfLanguage( string $topic ): string
	{
		$ext	= strlen( trim( static::$fileExtension ) ) ? '.'.trim( static::$fileExtension ) : '';
		return $this->filePath.$this->language.'/'.$topic.$ext;
	}

	/**
	 *	Loads Language File by Topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		bool		$strict			Flag: throw Exception if language file is not existing, default: no
	 *	@param		bool		$force			Flag: show error if language file is not existing and strict mode is off, default: no
	 *	@return		array		Map of loaded words
	 *	@throws		RuntimeException if language file is not existing (and strict is on)
	 *	@todo		improve error handling
	 */
	public function load( string $topic, bool $strict = FALSE, bool $force = FALSE )
	{
		if( !strlen( trim( $topic ) ) )
			throw new InvalidArgumentException( "Topic cannot be empty" );
		$this->env->getRuntime()->reach( 'Resource_Language::load('.$topic.')' );
		$fileName	= $this->getFilenameOfLanguage( $topic );
		$reader		= new FileReader( $fileName, FALSE );
		$data		= [];
		if( $reader->exists() )	{
			$string	= $reader->readString();
			$this->env->getRuntime()->reach( 'Resource_Language::load('.$topic.'): loaded file' );
			$string	= preg_replace( "/\s;[^\n]+\n+/", "\n", $string );
			$string	= preg_replace( "/\n;[^\n]+\n/Us", "\n", $string );
#			$plain	= preg_replace( '/".+"/U', "", $string );
			if( !preg_match( '/".*;.*"/U', $string ) ){
				$dataFromString	= @parse_ini_string( $string, TRUE, INI_SCANNER_RAW );
				if( $dataFromString !== FALSE )
					foreach( $dataFromString as $section => $pairs )
						foreach( $pairs as $key => $value )
							$data[$section][$key]	= preg_replace( '/^"(.*)"\s*$/', '\\1', $value );
				$this->env->getRuntime()->reach( 'Resource_Language::load: '.$topic.' @mode1' );
			}
			if( empty( $data ) ){
				$data	= IniFileReader::loadArray( $fileName, TRUE );
				$this->env->getRuntime()->reach( 'Resource_Language::load: '.$topic.' @mode2' );
			}
			$this->data[$topic]	= $data;
		}
		else{
			$message	= 'Invalid language file "'.$topic.'" ('.$fileName.')';
			if( $strict )
				throw new RuntimeException( $message, 221 );
			if( $force && $this->env->has( 'messenger' ) )
				$this->env->getMessenger()->noteFailure( $message );
		}
		$this->env->getRuntime()->reach( 'Resource_Language: end' );
		return $data;
	}

	/**
	 *	Sets a Language.
	 *	@access		public
	 *	@param		string		$language		Language to select
	 *	@return		self
	 *	@throws		DomainException				if language is not supported
	 */
	public function setLanguage( string $language ): self
	{
		if( 0 !== count( $this->languages ) ){
			$language	= strtolower( $language );
			if( !in_array( $language, $this->languages ) )
				throw new DomainException( 'Language "'.$language.'" is not supported' );
			$this->data		= [];
			$this->language	= $language;
			$this->load( 'main', FALSE );
		}
		return $this;
	}
}
