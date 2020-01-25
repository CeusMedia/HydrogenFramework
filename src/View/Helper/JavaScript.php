<?php
/**
 *	Helper to collect and combine JavaScripts.
 *
 *	Copyright (c) 2010-2019 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2019 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Component to collect and combine JavaScripts.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2019 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_View_Helper_JavaScript{

	protected static $instance;

	protected $pathCache			= "cache/";

	protected $prefix				= "";

	protected $suffix				= "";

	protected $revision;

	/**	@var	array				$scripts			List of JavaScript blocks */
	protected $scripts				= array();

	/**	@var	array				$scriptsOnReady		List if JavaScripts to run on load if browser is ready */
	protected $scriptsOnReady		= array();

	protected $urls					= array();

	protected $useCompression		= FALSE;

	public $indent					= "\t\t";

	/**
	 *	Constructor is disabled from public context.
	 *	Use static call 'getInstance()' instead of 'new'.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@return		void
	 */
	protected function __construct( CMF_Hydrogen_Environment $env ){
		$this->env	= $env;
	}

	/**
	 *	Cloning this object is not allowed.
	 *	@access		private
	 *	@return		void
	 */
	private function __clone(){}

	/**
	 *	Adds a module JavaScript by path name within configured local JavaScript folder.
	 *	Uses addUrl with configured  local JavaScript folder.
	 *	@access		public
	 *	@param		string		$filePath	Path of file within JavaScript folder
	 *	@param		integer		$level		Optional: Load level (1-9 or {top(1),mid(5),end(9)}, default: 5)
	 *	@return		void
	 */
	public function addModuleFile( $filePath, $level = CMF_Hydrogen_Environment_Resource_Captain::LEVEL_MID, $key = NULL ){
		$path	= $this->env->getConfig()->get( 'path.scripts' );
		$level	= CMF_Hydrogen_Environment_Resource_Captain::interpretLoadLevel( $level );
		$this->addUrl( $path.$filePath, $level );
	}

	/**
	 *	Adds a module JavaScript by path name within configured local JavaScript folder.
	 *	Uses addUrl with configured  local JavaScript folder.
	 *	@access		public
	 *	@param		string		$script		JavaScript block
	 *	@param		integer		$level		Optional: Load level (1-9 or {top(1),mid(5),end(9)}, default: 5)
	 *	@return		void
	 *	@deprecated	use addModuleFile instead
	 *	@todo		remove in v0.8.8
	 */
	public function addModuleScript( $script, $level = CMF_Hydrogen_Environment_Resource_Captain::LEVEL_MID, $key = NULL ){
		CMF_Hydrogen_Deprecation::getInstance()
			->setErrorVersion( '0.8.6.3' )
			->setExceptionVersion( '0.8.7' )
			->message( 'Please use addModuleFile instead' );

		$path	= $this->env->getConfig()->get( 'path.scripts' );
		$level	= CMF_Hydrogen_Environment_Resource_Captain::interpretLoadLevel( $level );
		$this->addUrl( $path.$script, $level );
	}

	/**
	 *	Collect a JavaScript block.
	 *	@access		public
	 *	@param		string		$script		JavaScript block
	 *	@param		integer		$level		Optional: Load level (1-9 or {top(1),mid(=5),end(9)}, default: 5)
	 *	@param		string		$key		Optional: script key in case of later removal
	 *	@return		void
	 *	@todo		remove support for level "ready", see below
	 */
	public function addScript( $script, $level = CMF_Hydrogen_Environment_Resource_Captain::LEVEL_MID, $key = NULL ){
		/* @todo		remove after all ->addScript( '...', 'ready' ) are replaced by ->addScriptOnReady( '...' ) */
		if( $level === "ready" )
			return $this->addScriptOnReady( $script, $level );

		$level	= CMF_Hydrogen_Environment_Resource_Captain::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		if( !array_key_exists( $level, $this->scripts ) )										//  level is not yet defined in scripts list
			$this->scripts[$level]	= array();													//  create empty scripts list for level
		$key	= strlen( $key ) ? md5( $key ) : 'default';
		if( !array_key_exists( $key, $this->scripts[$level] ) )
			$this->scripts[$level][$key]	= array();
		$this->scripts[$level][$key][]	= $script;
	}

	/**
	 *	Appends JavaScript code to be run after Browser finished rendering (document.ready).
	 *	@access		public
	 *	@param		string		$script		JavaScript code to execute on ready
	 *	@param		integer		$level		Optional: Load level (1-9 or {top(1),mid(=5),end(9)}, default: 5)
	 *	@param		string		$key		Optional: script key in case of later removal
	 *	@return		void
	 */
	public function addScriptOnReady( $script, $level = CMF_Hydrogen_Environment_Resource_Captain::LEVEL_MID, $key = NULL ){
		$level	= CMF_Hydrogen_Environment_Resource_Captain::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		if( !array_key_exists( $level, $this->scriptsOnReady ) )								//  level is not yet defined in scripts list
			$this->scriptsOnReady[$level]	= array();											//  create empty scripts list for level
		$key	= strlen( $key ) ? md5( $key ) : 'default';
		if( !array_key_exists( $key, $this->scriptsOnReady[$level] ) )
			$this->scriptsOnReady[$level][$key]	= array();
		$this->scriptsOnReady[$level][$key][]	= $script;										//  note JavaScript code on runlevel
	}

	/**
	 *	Add a JavaScript URL.
	 *	@access		public
	 *	@param		string		$url		JavaScript URL
	 *	@param		integer		$level		Optional: Load level (1-9 or {top(1),mid(=5),end(9)}, default: 5)
	 *	@param		string		$key		Optional: script key in case of later removal
	 *	@return		void
	 */
	public function addUrl( $url, $level = CMF_Hydrogen_Environment_Resource_Captain::LEVEL_MID, $key = NULL ){
		$level	= CMF_Hydrogen_Environment_Resource_Captain::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		if( !array_key_exists( $level, $this->urls ) )											//  level is not yet defined in scripts list
			$this->urls[$level]	= array();														//  create empty scripts list for level
		$key	= strlen( $key ) ? md5( $key ) : 'default';
		if( !array_key_exists( $key, $this->urls[$level] ) )
			$this->urls[$level][$key]	= array();
		$this->urls[$level][$key][]	= $url;														//  note JavaScript code on runlevel
	}

	/**
	 *	Removes all combined scripts in file cache.
	 *	@access		public
	 *	@return		void
	 */
	public function clearCache(){
		$index			= new FS_File_Iterator( $this->pathCache );
		$lengthPrefix	= strlen( $this->prefix );
		$lengthSuffix	= strlen( $suffix = $this->suffix.'.js' );
		foreach( $index as $item ){
			$fileName	= $item->getFilename();
			if( $this->prefix && substr( $fileName, 0, $lengthPrefix ) != $this->prefix )
				continue;
			if( substr( $fileName, -1 * $lengthSuffix ) != $suffix )
				continue;
			unlink( $item->getPathname() );
		}
	}

	protected function compress( $script ){
//		$script	= preg_replace( "@^\s*//.+\n?@", "", $script );
//		$script	= preg_replace( "@/\*.+\*/\n?@sU", "", $script );
		if( class_exists( 'JSMin' ) ){
			try{
				return JSMin::minify( $script );
			}
			catch( Exception $e ){}
		}
		if( class_exists( 'Net_API_Google_ClosureCompiler' ) ){
			return Net_API_Google_ClosureCompiler::minify( $script );
		}
		return $script;
	}

	/**
	 *	Returns a single instance of this Singleton class.
	 *	@static
	 *	@access		public
	 *	@return		ADT_Singleton						Single instance of this Singleton class
	 */
	public static function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	/**
	 *	Returns hash calculated by added URLs and revision, if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getPackageHash() {
		$copy	= $this->getPlainUrlList();
		sort( $copy );
		$key	= implode( '_', $copy );
		return md5( $this->revision.$key );
	}

	/**
	 *	Returns name of combined file in file cache.
	 *	@access		protected
	 *	@return		string
	 */
	protected function getPackageCacheFileName(){
		$hash	= $this->getPackageHash();
		return $this->pathCache.$this->prefix.$hash.$this->suffix.'.js';
	}

	/**
	 *	Returns name of combined JavaScript file.
	 *	@access		protected
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 */
	protected function getPackageFileName( $forceFresh = FALSE ){
		$fileJs	= $this->getPackageCacheFileName();
		if( !file_exists( $fileJs ) || $forceFresh ) {
			$contents	= array();
			if( $this->revision )
				$content	= "/* @revision ".$this->revision." */\n";
			foreach( $this->getPlainUrlList() as $url ){
				if( preg_match( "/^http/", $url ) )
					$content	= Net_Reader::readUrl( $url );
				else
					$content	= FS_File_Reader::load( $url );
				if( $content === FALSE )
					throw new RuntimeException( 'Script file "'.$url.'" not existing' );
				if( preg_match( "/\.min\.js$/", $url ) )
					array_unshift( $contents, preg_replace( "@/\*.+\*/\n?@sU", "", $content ) );
				else
					$contents[]	= $this->compress( $content );
			}
			$content	= implode( "\n\n", $contents );
			FS_File_Writer::save( $fileJs, $content );
		}
		return $fileJs;
	}

	/**
	 *	Returns a list of collected JavaScripts URLs.
	 *	@access		public
	 *	@return		array
	 *	@deprecated	use getPlainUrlList instead
	 *	@todo		remove in v0.8.7
	 */
	public function getUrlList(){
		CMF_Hydrogen_Deprecation::getInstance()
			->setErrorVersion( '0.8.6.3' )
			->setExceptionVersion( '0.8.7' )
			->message( 'Please use getPlainUrlList or getStructuredUrlList instead' );
		return $this->getPlainUrlList();
	}

	/**
	 *	Returns a flat list of collected JavaScripts URLs ordered by run level.
	 *	@access		public
	 *	@return		array		List of registered script URLS ordered by run level and script key
	 */
	public function getPlainUrlList(){
		$list	= array();
		foreach( $this->urls as $level => $levelUrls )
			foreach( $levelUrls as $key => $keyedUrls )
				foreach( $keyedUrls as $url )
					$list[]	= $url;
		return $list;
	}

	/**
	 *	Returns a list of collected JavaScripts URLs structured by run level and script key.
	 *	@access		public
	 *	@return		array		List of registered script URLS structured by run level and script key
	 */
	public function getStructuredUrlList(){
		return $this->urls;
	}

	/**
	 *	Renders an HTML script tag with all collected JavaScript URLs and blocks.
	 *	@access		public
	 *	@param		bool		$indentEndTag	Flag: indent end tag by 2 tabs
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 */
	public function render( $indentEndTag = FALSE, $forceFresh = FALSE ){
		$links			= $this->renderUrls( $this->useCompression, TRUE, $forceFresh );
		$scripts		= $this->renderScripts( $this->useCompression, TRUE );
		$scriptsOnReady	= $this->renderScriptsOnReady( $this->useCompression, TRUE );
		return $links.PHP_EOL.$scriptsOnReady.PHP_EOL.$scripts;
	}

	/**
	 *	Renders block of collected JavaScript code with directive to run if Browser finished loading (using jQuery event document.ready).
	 *	@access		protected
	 *	@param		boolean		$compress		Flag: compress code
	 *	@param		boolean		$wrapInTag		Flag: wrap code in HTML script tag
	 *	@return		string		Combinded JavaScript code to run if Browser is ready
	 */
	protected function renderScripts( $compress = FALSE, $wrapInTag = FALSE ){
		$list	= array();
		ksort( $this->scripts );
		foreach( $this->scripts as $level => $map )
			foreach( $map as $key => $scripts )
				foreach( $scripts as $script )
					$list[]	= preg_replace( "/;+$/", ";", trim( $script ) );
		if( !count( $list ) )
			return '';
		$content		= join( "\n", $list );
		if( $compress )
			$content	= $this->compress( $content );
		if( !$wrapInTag )
			return $content;
		return UI_HTML_Tag::create( 'script', $content, array( 'type' => 'text/javascript' ) );
	}

	/**
	 *	Renders block of collected JavaScript code with directive to run if Browser finished loading (using jQuery event document.ready).
	 *	@access		protected
	 *	@param		boolean		$wrapInTag		Flag: wrap code in HTML script tag
	 *	@return		string		Combined JavaScript code to run if Browser is ready
	 */
	protected function renderScriptsOnReady( $compress = FALSE, $wrapInTag = FALSE ){
		$list	= array();
		ksort( $this->scriptsOnReady );
		foreach( $this->scriptsOnReady as $level => $map ){
			foreach( $map as $key => $scripts ){
				foreach( $scripts as $script ){
					if( !$compress ){
						$script		= preg_replace( "/;+$/", ";", trim( $script ) );
						if( preg_match( "/\r?\n/", $script ) ){
							$lines	= preg_split( "/\r?\n/", PHP_EOL.$script.PHP_EOL );
							$script	= join( PHP_EOL."\t", $lines );
						}
					}
					$list[]		= "jQuery(document).ready(function(){".$script."});";
				}
			}
		}
		if( !count( $list ) )
			return '';
		$content	= PHP_EOL.trim( join( PHP_EOL, $list ) ).PHP_EOL;
		if( $compress )
			$content	= $this->compress( $content );
		if( !$wrapInTag )
			return $content;
		return UI_HTML_Tag::create( 'script', $content, array( 'type' => 'text/javascript' ) );
	}

	protected function renderUrls( $compress, $wrapInTag = FALSE, $forceFresh = FALSE ){
		if( !count( $this->getPlainUrlList() ) )
			return '';
		if( $this->useCompression ){
			$fileJs	= $this->getPackageFileName( $forceFresh );
			if( $this->revision )
				$fileJs	.= '?'.$this->revision;
			$attributes	= array(
				'type'		=> 'text/javascript',
	//			'language'	=> 'JavaScript',
				'src'		=> $fileJs
			);
			$links	= UI_HTML_Tag::create( 'script', NULL, $attributes );
		}
		else{
			$list	= array();
			foreach( $this->getPlainUrlList() as $url ){
				if( $this->revision )
					$url	.= ( preg_match( '/\?/', $url ) ? '&amp;' : '?' ).$this->revision;
				$attributes	= array(
					'type'		=> 'text/javascript',
		//			'language'	=> 'JavaScript',
					'src'		=> $url
				);
				$list[]	= UI_HTML_Tag::create( 'script', NULL, $attributes );
			}
			$links	= implode( "\n".$this->indent, $list  );
		}
		return $links;
	}

	/**
	 *	Sets revision for versioning cache.
	 *	@access		public
	 *	@param		mixed		$revision	Revision number or version string
	 *	@return		void
	 */
	public function setRevision( $revision ) {
		$this->revision	= $revision;
	}

	public function setCompression( $compression ) {
		$this->useCompression	= (bool) $compression;
	}

	public function setPrefix( $prefix ) {
		$this->prefix	= $prefix;
	}

	public function setSuffix( $suffix ) {
		$this->suffix	= $suffix;
	}

	/**
	 *	Set path to file cache.
	 *	@access		public
	 *	@param		string		$path		Path to file cache
	 *	@return		void
	 */
	public function setCachePath( $path ) {
		$this->pathCache = $path;
	}
}
