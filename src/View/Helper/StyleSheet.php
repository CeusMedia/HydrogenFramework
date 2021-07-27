<?php
/**
 *	Helper to collect and combine StyleSheets.
 *
 *	Copyright (c) 2010-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2010-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper;

use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use FS_File_RegexFilter as RegexFileFilter;
use FS_File_CSS_Combiner as CssCombiner;
use FS_File_CSS_Compressor as CssCompressor;
use FS_File_CSS_Relocator as CssRelocator;

/**
 *	Component to collect and combine StyleSheet.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class StyleSheet
{
	protected $pathBase				= "";
	protected $pathCache			= "";
	protected $prefix				= "";
	protected $suffix				= "";
	protected $revision;
	/**	@var	array				$styles		List of StyleSheet blocks */
	protected $styles				= array();
	protected $urls					= array();
	protected $useCompression		= FALSE;
	public $indent					= "    ";

	public function __construct( $basePath = NULL )
	{
		if( $basePath !== NULL )
			$this->setBasePath( $basePath );
		for( $i=0; $i<=9; $i++ ){
			$this->styles[$i]	= array();
			$this->urls[$i]		= array();
		}
	}

	/**
	 *	Collect a StyleSheet block.
	 *	@access		public
	 *	@param		string		$style		StyleSheet block
	 *	@param		integer		$level		Optional: Load level (1-9 or {top(1),mid(=5),end(9)}, default: 5)
	 *	@return		self
	 */
	public function addStyle( string $style, $level = CaptainResource::LEVEL_MID ): self
	{
		$level	= CaptainResource::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		$this->styles[$level][]		= $style;
		return $this;
	}

	/**
	 *	Add a StyleSheet URL.
	 *	@access		public
	 *	@param		string		$url		StyleSheet URL
	 *	@param		integer		$level		Optional: Load level (1-9 or {top(1),mid(=5),end(9)}, default: 5)
	 *	@param		array		$attributes	Optional: Additional style tag attributes
	 *	@return		self
	 */
	public function addUrl( string $url, $level = CaptainResource::LEVEL_MID, array $attributes = array() ): self
	{
		$level	= CaptainResource::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		$this->urls[$level][]		= (object) array( 'url' => $url, 'attributes' => $attributes );
		return $this;
	}

	/**
	 *	Removes all combined styles in file cache.
	 *	@access		public
	 *	@return		void
	 */
	public function clearCache()
	{
		$prefix = preg_replace( "/^([a-z0-9]+)/", "\\1", $this->prefix );
		$index	= new RegexFileFilter( $this->pathCache, '/^'.$prefix.'\w+\.css$/' );
		foreach( $index as $file ){
			unlink( $file->getPathname() );
		}
	}

	/**
	 *	Returns hash calculated by added URLs and revision, if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getPackageHash(): string
	{
		$copy	= array();
		foreach( $this->getUrlList() as $url )
			$copy[]	= $url->url;
		sort( $copy );
		$key	= implode( '_', $copy );
		return md5( $this->revision.$key );
	}

	/**
	 *	Returns a list of collected StyleSheet blocks.
	 *	@access		public
	 *	@return		array
	 */
	public function getStyleList(): array
	{
		$list	= array();
		foreach( $this->styles as $level => $map ){
			foreach( $map as $style ){
				$list[]	= $style;
			}
		}
		return $list;
	}

	/**
	 *	Returns a list of collected StyleSheet URLs.
	 *	@access		public
	 *	@return		array
	 */
	public function getUrlList(): array
	{
		$list	= array();
		foreach( $this->urls as $level => $map ){

			foreach( $map as $url ){
				if( !preg_match( "@^[a-z]+://@", $url->url ) )
					$url->url	= $this->pathBase.$url->url;
				$list[]	= $url;
			}
		}
		return $list;
	}

	/**
	 *	Renders an HTML scrtipt tag with all collected StyleSheet URLs and blocks.
	 *	@access		public
	 *	@param		bool		$indentEndTag	Flag: indent end tag by 2 tabs
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 */
	public function render( bool $indentEndTag = FALSE, bool $forceFresh = FALSE ): string
	{
		$urls		= $this->getUrlList();
		$styles		= $this->getStyleList();

		$linkAttributes	= [
			'type'		=> 'text/css',
			'rel'		=> 'stylesheet',
			'media'		=> 'all',
		];

		$items		= array();
		if( $urls ){
			if( $this->useCompression ){
				$attributes	= array_merge( $linkAttributes, [
					'href'		=> $this->getPackageFileName( $forceFresh )
				] );
				$items[]	= UI_HTML_Tag::create( 'link', NULL, $linkAttributes );
			}
			else{
				foreach( $urls as $url ){
					if( $this->revision )
						$url->url	.= '?r'.$this->revision;
					$attributes	= array_merge( $linkAttributes, $url->attributes, [
						'href'		=> $url->url
					] );
					$items[]	= UI_HTML_Tag::create( 'link', NULL, $attributes );
				}
			}
		}
		if( $styles ){
			$content	= PHP_EOL.implode( PHP_EOL, $styles ).PHP_EOL.$this->indent;
			$attributes	= array( 'type' => 'text/css' );
			$items[]	= UI_HTML_Tag::create( 'style', $content, $attributes );
		}
		return implode( PHP_EOL.$this->indent, $items );
	}

	public function setBasePath( string $path ): self
	{
		$this->pathBase	= $path;
		return $this;
	}

	/**
	 *	Set path to file cache.
	 *	@access		public
	 *	@param		string		$path		Path to file cache
	 *	@return		self
	 */
	public function setCachePath( string $path ): self
	{
		$this->pathCache = $path;
		return $this;
	}

	public function setCompression( bool $compression ): self
	{
		$this->useCompression	= $compression;
		return $this;
	}

	public function setPrefix( string $prefix ): self
	{
		$this->prefix	= $prefix;
		return $this;
	}

	public function setSuffix( string $suffix ): self
	{
		$this->suffix	= $suffix;
		return $this;
	}

	/**
	 *	Sets revision for versioning cache.
	 *	@access		public
	 *	@param		string		$revision	Revision number or version string
	 *	@return		self
	 */
	public function setRevision( string $revision ):self
	{
		$this->revision	= $revision;
		return $this;
	}

	//  --  PROTECTED --  //

	/**
	 *	Returns name of combined file in file cache.
	 *	@access		protected
	 *	@return		string
	 */
	protected function getPackageCacheFileName(): string
	{
		$hash	= $this->getPackageHash();
		return $this->pathCache.$this->prefix.$hash.$this->suffix.'.css';
	}

	/**
	 *	Returns name of combined StyleSheet file.
	 *	@access		protected
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 */
	protected function getPackageFileName( bool $forceFresh = FALSE ): string
	{
		$fileCss	= $this->getPackageCacheFileName();												//  calculate CSS package file name for collected CSS files
		if( file_exists( $fileCss ) && !$forceFresh )												//  CSS package file has been built before and is not to be rebuild
			return $fileCss;																		//  return CSS package file name
		$combiner	= new CssCombiner();													//  load CSS combiner for nested CSS files
		$compressor	= new CssCompressor();													//  load CSS compressor
		$relocator	= new CssRelocator();													//  load CSS relocator
		$pathRoot	= getEnv( 'DOCUMENT_ROOT' );													//  get server document root path for CSS relocator
		$pathSelf	= str_replace( $pathRoot, '', dirname( getEnv( 'SCRIPT_FILENAME' ) ) );			//  get path relative to document root for symbolic link map

		$symlinks	= array();																		//  prepare map of symbolic links for CSS relocator
		foreach( FS_Folder_Lister::getFolderList( 'themes' ) as $item )								//  iterate theme folders
			if( is_link( ( $path = $item->getPathname() ) ) )										//  if theme folder is a link
				$symlinks['/'.$pathSelf.'/themes/'.$item->getFilename()] = realpath( $path );		//  not symbolic link

		$contents	= array();																		//  prepare empty package content list
		if( $this->revision )																		//  a revision is set
			$contents[]	= "/* @revision ".$this->revision." */\n";									//  add revision header to content list

		foreach( $this->getUrlList() as $url ){														//  iterate collected URLs
			if( preg_match( "/^http/", $url->url ) ){												//  CSS resource is global (using HTTP)
				$contents[]	= Net_Reader::readUrl( $url->url );										//  read global CSS content and append to content list
				continue;																			//  skip to next without relocation etc.
			}
			$pathFile	= dirname( $url->url ).'/';													//  get path to CSS file within app
			$content	= FS_File_Reader::load( $url->url );										//  read local CSS content
			$content	= $combiner->combineString( $pathFile, $content, TRUE );					//  insert imported CSS files within CSS content
			if( preg_match( "/\/[a-z]+/", $content ) )												//  CSS content contains path notations
				$content	= $relocator->rewrite( $content, $pathFile, $pathRoot, $symlinks );		//  relocate resources paths in CSS content
			$contents[]	= $content;																	//  add CSS content after import and relocation
		}
		$content	= $compressor->compress( implode( "\n\n", $contents ) );						//  compress collected CSS contents
		FS_File_Writer::save( $fileCss, $content );													//  save CSS package file
		return $fileCss;																			//  return CSS package file name
	}
}
