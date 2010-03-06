<?php
/**
 *	Language Support with sniffing of Browser Language and Language Validation.
 *	Loads Language Files direct or from Cache if enabled.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@uses			Framework_Xenon_Core_Registry
 *	@uses			File_Reader
 *	@uses			File_Writer
 *	@uses			File_INI_Reader
 *	@uses			Net_HTTP_LanguageSniffer
 *	@uses			Alg_Validation_LanguageValidator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			05.12.2006
 *	@version		0.6
 */
import( 'de.ceus-media.framework.xenon.core.Registry' );
/**
 *	Language Support with sniffing of Browser Language and Language Validation.
 *	Loads Language Files direct or from Cache if enabled.
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@uses			Framework_Xenon_Core_Registry
 *	@uses			File_Reader
 *	@uses			File_Writer
 *	@uses			File_INI_Reader
 *	@uses			Net_HTTP_LanguageSniffer
 *	@uses			Alg_Validation_LanguageValidator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			05.12.2006
 *	@version		0.6
 *	@todo			Code Doc
 */
class Framework_Xenon_Core_Language
{
	protected $hovers		= array();
	protected $words		= array();
	
	protected $default;
	protected $allowed;
	
	protected $pathFiles;
	protected $pathCache;
	protected $loadedFiles	= array();

	public function getAllowedLanguages()
	{
		return $this->allowed;
	}
	
	public function getDefaultLanguage()
	{
		return $this->default;
	}
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $identify = TRUE )
	{
		$this->registry	= Framework_Xenon_Core_Registry::getInstance();
		$config		= $this->registry->get( 'config' );

		$this->default	= $config['languages.default'];
		$this->allowed	= explode( ",", $config['languages.allowed'] );

		if( $identify )
			$this->identifyLanguage();
		if( !$this->registry->has( 'words' ) )
			$this->registry->set( 'words', $this->getWords(), TRUE );
	}
	
	/**
	 *	Creates nested Folder recursive.
	 *	@access		protected
	 *	@param		string		$path		Folder to create
	 *	@return		void
	 */
	protected function createFolder( $path )
	{
		if( !file_exists( $path ) )
		{
			$parts	= explode( "/", $path );
			$folder	= array_pop( $parts );
			$path	= implode( "/", $parts );
			$this->createFolder( $path );
			mkDir( $path."/".$folder );
		}
	}
	
	/**
	 *	Returns ISO-Code of current Language.
	 *	@access		public
	 *	@return		string
	 */
	public function getLanguage()
	{
		if( $this->registry->has( 'session' ) )
		{
			$session	= $this->registry->get( 'session' );
			if( $session->get( 'language' ) )
				return $session->get( 'language' );
		}
		return $this->getDefaultLanguage();
	}
	
	public function getLoadedFiles()
	{
		return $this->loadedFiles;	
	}
	
	public function & getWord( $fileName, $section, $key )
	{
		if( isset( $this->words[$fileName][$section][$key] ) )
			return $this->words[$fileName][$section][$key];
		throw new Exception( 'Word "'.$key.'" is not available in File "'.$fileName.'" in Section "'.$section.'".' );
	}

	/**
	 *	Returns Array of Words, either of Section of File, of File or all.
	 *	@access		public
	 *	@param		string		$fileName		File Name of Language File
	 *	@param		string		$section		Section in Language File
	 *	@return		array
	 */
	public function & getWords( $fileName = FALSE, $section = FALSE )
	{
		if( $fileName )
		{
			if( $section )
			{
				if( isset( $this->words[$fileName][$section] ) )
					return $this->words[$fileName][$section];
				throw new Exception( 'Section "'.$section.'" is not available in Language File "'.$fileName.'"' );
			}
			if( isset( $this->words[$fileName] ) )
				return $this->words[$fileName];
			else
				throw new Exception( 'Language File "'.$fileName.'" is not available.' );
		}
		return $this->words;
	}
	
	/**
	 *	Identifies Language from User Browser or User Request.
	 *	@access		public
	 *	@return		void
	 */
	public function identifyLanguage()
	{
		$request	= $this->registry->get( 'request' );
		$session	= $this->registry->get( 'session' );

		//  --  LANGUAGE SWITCH  --  //
		if( $request->has( 'switchLanguageTo' ) )
		{
			import( 'de.ceus-media.alg.validation.LanguageValidator' );
			$language	= $request->get( 'switchLanguageTo' );
			$language	= Alg_Validation_LanguageValidator::validate( $language, $this->allowed, $this->default );
			$this->setLanguage( $language );
			if( getEnv( 'HTTP_REFERER' ) && getEnv( "HTTP_USER_AGENT" ) != "Motrada Office" )
				die( header( "Location: ".getEnv( 'HTTP_REFERER' ) ) );
		}

		//  --  SESSION PRESET  --  //
		if( $session->has( 'language' ) )
			return;

		//  --  LANGUAGE SNIFF  --  //
		if( getEnv( 'HTTP_ACCEPT_LANGUAGE' ) )
		{
			import( 'de.ceus-media.net.http.LanguageSniffer' );
			$language	= Net_HTTP_LanguageSniffer::getLanguage( $this->allowed, $this->default );
			$this->setLanguage( $language );
		}
	}

	/**
	 *	Constructor.
	 *	@access		private
	 *	@param		string		$url		URL of Language Cache File
	 *	@return		string
	 */
	private function loadCache( $url )
	{
		return file_get_contents( $url );
/*		import( 'de.ceus-media.file.Reader' );
		$file	= new File_Reader( $url );
		return $file->readString();
			return implode( "", file( $url ) );
*/	}
	
	/**
	 *	Loads Hover Texts.
	 *	@access		private
	 *	@return		void
	 */
/*	protected function loadHovers()
	{
		$session	= $this->registry->get( 'session' );
		import( 'de.ceus-media.file.block.Reader' );
		$uri	= $this->pathFiles.$session->get( 'language' )."/hovers.blocks";
		if( file_exists( $uri ) )
		{
			$bfr	= new File_Block_Reader( $uri );
			$this->hovers	= $bfr->getBlocks();
		}
	}
*/	

	public function loadModuleLanguage( $module, $fileName, $section = FALSE, $verbose = FALSE )
	{
		if( $verbose )
	 	   remark( "<b>Load Language: </b> File: ".$fileName." -> Section: ".$section );
		$config		= $this->registry->get( 'config' );
		$language	= $this->getLanguage();
		if( !$section )
			$section	= $fileName;

		if( in_array( $fileName, array_keys( $this->loadedFiles ) ) )
			return FALSE;

		if( in_array( $section, array_values( $this->words ) ) )
			throw new Exception( 'Language File with Key "'.$section.'" is already loaded.' );

		//  --  BASICS  --  //
		$basePath	= "module/".$module."/languages/".$language."/";
		$baseName	= str_replace( ".", "/", $fileName ).".lan";
		$lanFile	= $basePath.$baseName;		//  fallback: base path

		//  --  CACHE CHECK  --  //
		$cache	= FALSE;
		if( isset( $config['paths.cache'] ) && $config['paths.cache'] )
		{
			$cachePath	= $config['paths.cache'].basename( $config['paths.languages'] ).'/';
			$cacheFile	= $cachePath.$language.".".$fileName.".ser";
			if( file_exists( $cacheFile ) && filemtime( $lanFile ) <= filemtime( $cacheFile ) )
			{
				$this->words[$section]	= unserialize( $this->loadCache( $cacheFile ) );
				$this->loadedFiles[$fileName]	= $section;
				return TRUE;
			}
			$cache	= TRUE;
		}

		if( !file_exists( $lanFile ) )
			throw new Exception_IO( 'Language File "'.$fileName.'" is not existing.', $lanFile );	

		import( 'de.ceus-media.file.ini.Reader' );
		$ir	= new File_INI_Reader( $lanFile, TRUE, FALSE );							//  load File with Sections and without reserved Words
		$this->words[$section]	= $ir->toArray( TRUE );								//  load File Sections into Language Array
		foreach( $this->words[$section] as $area => $pairs )
			foreach( array_keys( $pairs ) as $key )
				if( isset( $this->hovers[$baseName."/".$area."/".$key] ) )
					$this->words[$section][$area][$key."_hover"] = $this->hovers[$fileName."/".$area."/".$key];
		if( $cache )
			$this->saveCache( $cacheFile, serialize( $this->words[$section] ) );
		$this->loadedFiles[$fileName]	= $section;
		return TRUE;
	}

	/**
	 *	Loads Language File.
	 *	@access		public
	 *	@param		string		$fileName		Name of Language File without Extension
	 *	@param		string		$section		Section Name in Words
	 *	@param		bool		$verbose		Flag: Note missing Language Files.
	 *	@return		bool
	 */
	public function loadLanguage( $fileName, $section = FALSE, $verbose = FALSE )
	{
		if( $verbose )
	 	   remark( "<b>Load Language: </b> File: ".$fileName." -> Section: ".$section );
		$config		= $this->registry->get( 'config' );
		$language	= $this->getLanguage();
		if( !$section )
			$section	= $fileName;

		if( in_array( $fileName, array_keys( $this->loadedFiles ) ) )
			return FALSE;

		if( in_array( $section, array_values( $this->words ) ) )
			throw new Exception( 'Language File with Key "'.$section.'" is already loaded.' );

		//  --  BASICS  --  //
		$basePath	= $config['paths.languages'].$language."/";
		$baseName	= str_replace( ".", "/", $fileName ).".lan";
		$lanFile	= $basePath.$baseName;		//  fallback: base path

		//  --  CACHE CHECK  --  //
		$cache	= FALSE;
		if( isset( $config['paths.cache'] ) && $config['paths.cache'] )
		{
			$cachePath	= $config['paths.cache'].basename( $config['paths.languages'] ).'/';
			$cacheFile	= $cachePath.$language.".".$fileName.".ser";
			if( file_exists( $cacheFile ) && filemtime( $lanFile ) <= filemtime( $cacheFile ) )
			{
				$this->words[$section]	= unserialize( $this->loadCache( $cacheFile ) );
				$this->loadedFiles[$fileName]	= $section;
				return TRUE;
			}
			$cache	= TRUE;
		}

		if( !file_exists( $lanFile ) )
			throw new Exception_IO( 'Language File "'.$fileName.'" is not existing.', $lanFile );	

		import( 'de.ceus-media.file.ini.Reader' );
		$ir	= new File_INI_Reader( $lanFile, TRUE, FALSE );							//  load File with Sections and without reserved Words
		$this->words[$section]	= $ir->toArray( TRUE );								//  load File Sections into Language Array
		foreach( $this->words[$section] as $area => $pairs )
			foreach( array_keys( $pairs ) as $key )
				if( isset( $this->hovers[$baseName."/".$area."/".$key] ) )
					$this->words[$section][$area][$key."_hover"] = $this->hovers[$fileName."/".$area."/".$key];
		if( $cache )
			$this->saveCache( $cacheFile, serialize( $this->words[$section] ) );
		$this->loadedFiles[$fileName]	= $section;
		return TRUE;
	}

	/**
	 *	Saves Language File Cache.
	 *	@access		private
	 *	@param		string		$url		URL of Language Cache File
	 *	@param		string		$content	Content of Language Cache File
	 *	@return		void
	 */
	private function saveCache( $url, $content )
	{
		import( 'de.ceus-media.file.Writer' );
		$this->createFolder( dirname( $url ) );		
		$file	= new File_Writer( $url, 0750 );
		$file->writeString( $content );
	}
	
	/**
	 *	Sets current Language, sets internal Paths and loads Hover Texts.
	 *	@access		public
	 *	@param		string		$language		ISO-Code of Language
	 *	@return		void
	 */
	public function setLanguage( $language )
	{
		$config		= $this->registry->get( 'config' );
		$session	= $this->registry->get( 'session' );
		if( !in_array( $language, $this->allowed ) )
			throw new Exception( 'Language "'.$language.'" is not allowed.' );
		$session->set( 'language', $language );

		$this->pathFiles	= $config['paths.languages'];
		$this->pathCache	= $config['paths.cache'].basename( $config['paths.languages'] ).$language."/";
		$this->loadedFiles	= array();
		$this->registry->set( 'words', $this->words, TRUE );
//		$this->loadHovers();
	}
}
?>