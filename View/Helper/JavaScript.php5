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
 *	Component to collect and combine JavaScripts.
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class Framework_Hydrogen_View_Helper_JavaScript
{
	protected static $instance;
	protected $pathCache			= "contents/cache/";
	protected $revision;
	/**	@var	array				$scripts		List of JavaScript blocks */
	protected $scripts				= array();
	protected $urls					= array();

	/**
	 *	Constructor is disabled from public context.
	 *	Use static call 'getInstance()' instead of 'new'.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __construct(){
	}

	/**
	 *	Cloning this object is not allowed.
	 *	@access		private
	 *	@return		void
	 */
	private function __clone(){}

	/**
	 *	Collect a JavaScript block.
	 *	@access		public
	 *	@param		string		$script		JavaScript block
	 *	@param		bool		$onTop		Put this block on top of all others
	 *	@return		void
	 */
	public function addScript( $script, $onTop = FALSE ){
		$onTop ? array_unshift( $this->scripts, $script ) : array_push( $this->scripts, $script );
	}

	/**
	 *	Add a JavaScript URL.
	 *	@access		public
	 *	@param		string		$url		JavaScript URL
	 *	@param		bool		$onTop		Flag: add this URL on top of all others
	 *	@return		void
	 */
	public function addUrl( $url, $onTop = FALSE ){
		$onTop ? array_unshift( $this->urls, $url ) : array_push( $this->urls, $url );
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
			self::$instance	= new self();
		return self::$instance;
	}

	/**
	 *	Returns hash calculated by added URLs and revision, if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getPackageHash() {
		$copy	= $this->urls;
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
		return $this->pathCache.$hash.'.js';
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
			foreach( $this->urls as $url ){
				$content	= @file_get_contents( $url );
				if( $content === FALSE )
					throw new RuntimeException( 'Script file "'.$url.'" not existing' );
				$contents[]	= $content;
			}
			$content	= implode( "\n\n", $contents );
			$attributes	= array(
				'type'		=> 'text/javascript',
				'language'	=> 'JavaScript'
			);
			File_Writer::save( $fileJs, $content );
		}
		return $fileJs;
	}

	/**
	 *	Renders an HTML scrtipt tag with all collected JavaScript URLs and blocks.
	 *	@access		public
	 *	@param		bool		$indentEndTag	Flag: indent end tag by 2 tabs
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 */
	public function render( $enablePackage = TRUE, $indentEndTag = FALSE, $forceFresh = FALSE ){
		$links		= '';
		$scripts	= '';
		if( $this->urls )
		{
			if( $enablePackage )
			{
				$fileJs	= $this->getPackageFileName( $forceFresh );
				$attributes	= array(
					'type'		=> 'text/javascript',
		//			'language'	=> 'JavaScript',
					'src'		=> $fileJs
				);
				$links	= UI_HTML_Tag::create( 'script', NULL, $attributes );
			}
			else
			{
				$list	= array();
				foreach( $this->urls as $url )
				{
					$attributes	= array(
						'type'		=> 'text/javascript',
			//			'language'	=> 'JavaScript',
						'src'		=> $url
					);
					$list[]	= UI_HTML_Tag::create( 'script', NULL, $attributes );
				}
				$links	= implode( "\n", $list  );
			}
		}

		if( $this->scripts )
		{
			array_unshift( $this->scripts, '' );
			array_push( $this->scripts, $indentEndTag ? "\t\t" : '' );
			$content	= implode( "\n", $this->scripts );
			$attributes	= array(
				'type'		=> 'text/javascript',
	//			'language'	=> 'JavaScript',
			);
			$scripts	= UI_HTML_Tag::create( 'script', $content, $attributes );
		}
		return $links.$scripts;
	}

	/**
	 *	Returns a list of collected JavaScripts URLs.
	 *	@access		public
	 *	@return		array
	 */
	public function getUrlList(){
		return $this->urls;
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