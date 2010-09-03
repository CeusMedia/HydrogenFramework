<?php
/**
 *	Helper to collect and combine JavaScripts.
 *
 *	Copyright (c) 2010 Christian Würker (ceus-media.de)
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
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Helper to collect and combine JavaScripts.
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class Framework_Hydrogen_View_Helper_JsLoader
{
	/**	@var		ADT_Singleton		$instance		Instance of Singleton */
	protected static $instance;
	protected $pathCache	= "cache/";
	protected $revision;
	protected $scripts		= array();

	/**
	 *	Constructor is disabled from public context.
	 *	Use static call 'getInstance()' instead of 'new'.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __construct(){}

	/**
	 *	Cloning this object is not allowed.
	 *	@access		private
	 *	@return		void
	 */
	private function __clone(){}

	/**
	 *	Add a JavaScript URL.
	 *	@access		public
	 *	@param		string		$url		JavaScript URL
	 *	@param		bool		$onTop		Flag: add this URL on top of all others
	 *	@return		void
	 */
	public function addUrl( $url, $onTop = FALSE ){
		$onTop ? array_unshift( $this->scripts, $url ) : array_push( $this->scripts, $url );
	}

	/**
	 *	Removes all combined scripts in file cache.
	 *	@access		public
	 *	@return		void
	 */
	public function clearCache(){
		$index	= new File_RegexFilter( $this->pathCache, '/\.js$/' );
		foreach( $index as $file )
			unlink( $file->getPathname() );
	}

	/**
	 *	Returns a single instance of this Singleton class.
	 *	@static
	 *	@access		public
	 *	@return		ADT_Singleton	Single instance of this Singleton class
	 */
	public static function getInstance(){
		if( !self::$instance )
			self::$instance	= new Framework_Hydrogen_View_Helper_JsLoader();
		return self::$instance;
	}

	/**
	 *	Returns hash calculated by added URLs and revision, if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getHash() {
		$copy	= $this->scripts;
		sort( $copy );
		$key	= implode( '_', $copy );
		return md5( $this->revision.$key );
	}

	/**
	 *	Returns name of combined file in file cache.
	 *	@access		protected
	 *	@return		string
	 */
	protected function getCacheFileName(){
		$hash	= $this->getHash();
		return $this->pathCache.$hash.'.js';
	}

	/**
	 *	Returns combined content of collected JavaScripts.
	 *	@access		public
	 *	@return		string
	 */
	public function getContent(){
		$fileJs	= $this->getFileName();
		return File_Reader::load( $fileJs );
	}

	/**
	 *	Returns name of combined JavaScript file.
	 *	@access		public
	 *	@return		string
	 */
	public function getFileName(){
		$fileJs	= $this->getCacheFileName();
		if( !file_exists( $fileJs ) ) {
			$contents	= array();
			if( $this->revision )
				$content	= "/* @revision ".$this->revision." */\n";
			foreach( $this->scripts as $url ){
				$content	= file_get_contents( $url );
				$contents[]	= $content;
			}
			$content	= implode( "\n\n", $contents );
			File_Writer::save( $fileJs, $content );
		}
		return $fileJs;
	}

	/**
	 *	Returns rendered HTML script tag containing content of all collected JavaScripts.
	 *	@access		public
	 *	@return		string
	 */
	public function getScriptTag(){
		$fileJs	= $this->getFileName();
		$attributes	= array(
			'type'		=> 'text/javascript',
//			'language'	=> 'JavaScript',
			'src'		=> $fileJs
		);
		return UI_HTML_Tag::create( 'script', NULL, $attributes );
	}

	/**
	 *	Returns a list of collected JavaScripts URLs.
	 *	@access		public
	 *	@return		array
	 */
	public function getUrlList(){
		return $this->scripts;
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
?>