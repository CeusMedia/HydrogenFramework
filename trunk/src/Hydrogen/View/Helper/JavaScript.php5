<?php
/**
 *	Helper to collect and combine JavaScripts.
 *
 *	Copyright (c) 2010 Christian Würker (ceusmedia.com)
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
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Component to collect and combine JavaScripts.
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_View_Helper_JavaScript
{
	protected static $instance;
	protected $pathCache			= "cache/";
	protected $prefix				= "";
	protected $suffix				= "";
	protected $revision;
	/**	@var	array				$scripts		List of JavaScript blocks */
	protected $scripts				= array(
		'top'	=> array(),
		'mid'	=> array(),
		'end'	=> array(),
		'ready'	=> array()
	);
	protected $urls					= array();
	protected $useCompression		= FALSE;
	public $indent					= "\t\t";

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
	 *	Collect a JavaScript block.
	 *	@access		public
	 *	@param		string		$script		JavaScript block
	 *	@param		integer		$level		Run level, values: (top, mid, end), default: mid
	 *	@return		void
	 */
	public function addScript( $script, $level = 'mid', $key = NULL ){
		$level	= is_bool( $level ) ? ( $level ? 'top' : 'mid' ) : $level;				//  hack: map older boolean values to levels @todo remove if modules are adjusted
		$level	= in_array( $level, array( 'top', 'mid', 'end', 'ready' ) ) ? $level : 'ready';
		$key	= strlen( $key ) ? md5( $key ) : 'default';
		if( !array_key_exists( $key, $this->scripts[$level] ) )
			$this->scripts[$level][$key]	= array();
		$this->scripts[$level][$key][]	= $script;
	}

    /**
     *  Collect a StyleSheet block.
     *  @access     public
     *  @param      string      $style      StyleSheet block
     *  @return     void
     */
    public function addStyle( $style, $level = 'mid', $key = NULL ){
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
		$index			= new File_Iterator( $this->pathCache );
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

	/**
	 *	Returns a single instance of this Singleton class.
	 *	@static
	 *	@access		public
	 *	@return		ADT_Singleton						Single instance of this Singleton class
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
			foreach( $this->urls as $url ){
				if( preg_match( "/^http/", $url ) )
					$content	= Net_Reader::readUrl( $url );
				else
					$content	= File_Reader::load( $url );
				if( $content === FALSE )
					throw new RuntimeException( 'Script file "'.$url.'" not existing' );
				if( !preg_match( "/\.min\.js$/", $url ) )
					if( class_exists( 'JSMin' ) )
						$content	= JSMin::minify( $content );
				$contents[]	= $content;
			}
			$content	= implode( "\n\n", $contents );
			File_Writer::save( $fileJs, $content );
		}
		return $fileJs;
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
	 *	Renders an HTML scrtipt tag with all collected JavaScript URLs and blocks.
	 *	@access		public
	 *	@param		bool		$indentEndTag	Flag: indent end tag by 2 tabs
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 */
	public function render( $indentEndTag = FALSE, $forceFresh = FALSE ){
		$links		= '';
		$scripts	= '';
		if( $this->urls ){
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
				foreach( $this->urls as $url ){
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
		}

        $list   = array( 'top' => array(), 'mid' => array(), 'end' => array(), 'ready' => array() );
        foreach( $this->scripts as $level => $map ){
            foreach( $map as $key => $scripts ){
                foreach( $scripts as $script ){
                    $list[$level][] = $script;
                }
            }
        }
        foreach( $list as $level => $map ){
			$list[$level]	= implode( "\n", $map );
			if( $level === "ready" )
				$list[$level]	= 'jQuery(document).ready(function(){'.$list[$level].'});';
		}
		$scripts	= trim( $list['top'].$list['mid'].$list['end'] );
		if( strlen( $scripts ) ){
			if( $this->useCompression ){
				if( class_exists( 'JSMin' ) )
					$scripts	= JSMin::minify( $scripts );
				else if( class_exists( 'Net_API_Google_ClosureCompiler' ) )
					$scripts	= Net_API_Google_ClosureCompiler::minify( $scripts );
			}
			$attributes	= array(
				'type'		=> 'text/javascript',
	//			'language'	=> 'JavaScript',
			);
			$scripts	= "\n".$this->indent.UI_HTML_Tag::create( 'script', $scripts, $attributes );
		}

		$scriptsOnReady	= trim( $list['ready'] );
		if( strlen( $scriptsOnReady ) ){
			if( $this->useCompression ){
				if( class_exists( 'JSMin' ) )
					$scriptsOnReady	= JSMin::minify( $scriptsOnReady );
				else if( class_exists( 'Net_API_Google_ClosureCompiler' ) )
					$scriptsOnReady	= Net_API_Google_ClosureCompiler::minify( $scriptsOnReady );
			}
			$attributes	= array(
				'type'		=> 'text/javascript',
	//			'language'	=> 'JavaScript',
			);
			$scriptsOnReady	= "\n".$this->indent.UI_HTML_Tag::create( 'script', $scriptsOnReady, $attributes );
		}
		return $links.$scriptsOnReady.$scripts;
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
?>
