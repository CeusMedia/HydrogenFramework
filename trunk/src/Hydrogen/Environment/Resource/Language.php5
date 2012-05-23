<?php
/**
 *	Language Class of Framework Hydrogen.
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
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Language Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Language
{
	/**	@var		array								$data			Array of loaded Language File Definitions */
	protected $data;
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object */
	protected $env;
	/**	@var		string								$filePath		Path to Language Files */
	protected $filePath;
	/**	@var		string								$language		Set Language */
	protected $language;
	/**	@var		array								$languages		List of allowed Languages */
	protected $languages								= array();
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object
	 *	@param		string								$language		Language to select
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env			= $env;
		$config				= $env->getConfig();

		$this->filePath		= 'locales';															//  assume default folder name
		if( $config->get( 'path.locales' ) )														//  a locales folder has been configured
			$this->filePath	= $config->get( 'path.locales' );										//  take the configured folder name
		if( !file_exists( $this->filePath ) )														//  locales folder is not existing
			throw new RuntimeException( 'Locales folder is missing' );								//  quit with exception

		if( $config->get( 'locale.allowed' ) )														//  allowed languages have been set
			foreach( explode( ',', $config['locale.allowed'] ) as $nr => $language )				//  iterate extracted languages
				$this->languages[]	= trim( $language );											//  save language without surrounding spaces

		else																						//  otherwise scan locales folder
			foreach( Folder_Lister::getFolderList( $this->filePath ) as $folder )					//  iterate found locale folders
				$this->languages[]	= $folder->getFilename();										//  save locale folder as language
		
		$language			= $config->has( 'locale.default' ) ? $config['locale.default'] : 'en';

		if( $this->env->has( 'session' ) )
		{
			$switchTo	= $this->env->getRequest()->get( 'switchLanguageTo' );
			if( $switchTo && in_array( $switchTo, $this->languages ) )
			{
				$this->env->getSession()->set( 'language', $switchTo );
				if( !empty( $_SERVER['HTTP_REFERER'] ) )
				{
					$referer = $_SERVER['HTTP_REFERER'];
					if( !preg_match( '/switchLanguageTo/', $referer ) )
					{
						header( 'Location: '.$referer );
						exit;
					}
				}
			}
			if( $this->env->getSession()->get( 'language' ) )
				$language		= $this->env->getSession()->get( 'language' );
		}
		$this->setLanguage( $language );
	}

	/**
	 *	Returns selected Language.
	 *	@access		public
	 *	@return		string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 *	Returns list of allowed languages.
	 *	@access		public
	 *	@return		string
	 */
	public function getLanguages()
	{
		return $this->languages;
	}
	
	/**
	 *	Returns array of language sections within a language topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		bool		$strict			Flag: throw exception if language topic is not loaded
	 *	@param		bool		$force			Flag: note Failure if not loaded
	 *	@return		array
	 *	@throws		RuntimeException if language topic is not loaded/existing and strict is on
	 */
	public function getWords( $topic, $strict = TRUE, $force = TRUE )
	{
		if( isset( $this->data[$topic] ) )
			return $this->data[$topic];
		$message	= 'Invalid language topic "'.$topic.'"';
		if( $strict )
			throw new RuntimeException( $message, 221 );
		if( $force )
			$this->env->getMessenger()->noteFailure( $message );
		return array();
	}

	public function hasWords( $topic )
	{
		return isset( $this->data[$topic] );
	}

	/**
	 *	Returns map of language pairs with a language section of a language topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		string		$section		Section of Language
	 *	@param		bool		$strict			Flag: throw exception if language topic is not loaded
	 *	@param		bool		$force			Flag: note Failure if not loaded
	 *	@return		array
	 *	@throws		RuntimeException if language section is not existing and strict is on
	 */
	public function getSection( $topic, $section, $strict = TRUE, $force = TRUE )
	{
		$sections	= $this->getWords( $topic, $strict, $force );
		if( isset( $sections[$section] ) )
			return $sections[$section];
		$message	= 'Invalid language section "'.$section.'" in topic "'.$topic.'"';
		if( $strict )
			throw new RuntimeException( $message, 221 );
		if( $force )
			$this->env->getMessenger()->noteFailure( $message );
		return array();
	}

	/**
	 *	Returns File Name of Language Topic.
	 *	@access		protected
	 *	@param		string		$topic			Topic of Language
	 *	@return		void
	 */
	protected function getFilenameOfLanguage( $topic )
	{
		return $this->filePath.$this->language."/".$topic.".ini";	
	}

	/**
	 *	Loads Language File by Topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		bool		$strict			Flag: throw Exception if language file is not existing
	 *	@return		void
	 *	@throws		RuntimeException if language file is not existing (and strict is on)
	 */
	public function load( $topic, $strict = FALSE, $force = FALSE )
	{
		$fileName	= $this->getFilenameOfLanguage( $topic );
		$reader		= new File_Reader($fileName);
		if( $reader->exists() )
		{
			$data	= FALSE;
			$string	= $reader->readString();
			if( !preg_match( '/".+;"/U', $string ) ){
				$data	= @parse_ini_string( $string, TRUE, INI_SCANNER_RAW );
				if( $data !== FALSE )
					foreach( $data as $section => $pairs )
						foreach( $pairs as $key => $value )
							$data[$section][$key]	= preg_replace( '/^"(.*)"\s*$/', '\\1', $value );
			}
			if( $data === FALSE )
				$data	= File_INI_Reader::load( $fileName, TRUE );
			$this->data[$topic]	= $data;
		}
		else
		{
			$message	= 'Invalid language file "'.$topic.' ('.$fileName.')"';
			if( $strict )
				throw new RuntimeException( $message, 221 );
			if( $force )
				$this->env->getMessenger()->noteFailure( $message );
		}
	}

	/**
	 *	Sets a Language.
	 *	@access		public
	 *	@param		string		$language		Language to select
	 *	@return		void
	 */
	public function setLanguage( $language )
	{
		$language	= strtolower( $language );
		if( !in_array( $language, $this->languages ) )
			return FALSE;
		$this->data		= array();
		$this->language	= $language;

		$this->load( 'main' );
		return TRUE;
	}
}
?>