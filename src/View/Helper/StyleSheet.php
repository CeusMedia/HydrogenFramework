<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Helper to collect and combine StyleSheets.
 *
 *	Copyright (c) 2010-2024 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper;

use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\FS\File\CSS\Combiner as CssCombiner;
use CeusMedia\Common\FS\File\CSS\Compressor as CssCompressor;
use CeusMedia\Common\FS\File\CSS\Relocator as CssRelocator;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RegexFilter as RegexFileFilter;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\Folder\Lister as FolderLister;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use SplFileInfo;

/**
 *	Component to collect and combine StyleSheet.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class StyleSheet
{
	protected string $pathBase			= '';
	protected string $pathCache			= '';
	protected string $prefix			= '';
	protected string $suffix			= '';
	protected ?string $revision			= NULL;
	/**	@var	array					$styles		List of StyleSheet blocks */
	protected array $styles				= [];
	protected array $urls				= [];
	protected bool $useCompression		= FALSE;
	public string $indent				= '    ';

	public function __construct( ?string $basePath = NULL )
	{
		if( $basePath !== NULL )
			$this->setBasePath( $basePath );
		for( $i=0; $i<=9; $i++ ){
			$this->styles[$i]	= [];
			$this->urls[$i]		= [];
		}
	}

	/**
	 *	Collect a StyleSheet block.
	 *	@access		public
	 *	@param		string			$style		StyleSheet block
	 *	@param		integer|NULL	$level		Optional: Load level (1-9 or {top(1),mid(=5),end(9)}, default: 5)
	 *	@return		self
	 */
	public function addStyle( string $style, ?int $level = CaptainResource::LEVEL_MID ): self
	{
		$level	??= CaptainResource::LEVEL_MID;
		$this->styles[$level][]		= $style;
		return $this;
	}

	/**
	 *	Add a StyleSheet URL.
	 *	@access		public
	 *	@param		string			$url		StyleSheet URL
	 *	@param		integer|NULL	$level		Optional: Load level (1-9 or {top(1),mid(=5),end(9)}, default: 5)
	 *	@param		array			$attributes	Optional: Additional style tag attributes
	 *	@return		self
	 */
	public function addUrl( string $url, ?int $level = CaptainResource::LEVEL_MID, array $attributes = [] ): self
	{
		$level	??= CaptainResource::LEVEL_MID;
		$this->urls[$level][]	= (object) ['url' => $url, 'attributes' => $attributes];
		return $this;
	}

	/**
	 *	Removes all combined styles in file cache.
	 *	@access		public
	 *	@return		self
	 */
	public function clearCache(): self
	{
		$prefix = preg_replace( "/^([a-z0-9]+)/", "\\1", $this->prefix );
		$index	= new RegexFileFilter( $this->pathCache, '/^'.$prefix.'\w+\.css$/' );
		/** @var SplFileInfo $file */
		foreach( $index as $file )
			unlink( $file->getPathname() );
		return $this;
	}

	/**
	 *	Returns hash calculated by added URLs and revision, if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getPackageHash(): string
	{
		$copy	= [];
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
		$list	= [];
		foreach( $this->styles as $map )
			foreach( $map as $style )
				$list[]	= $style;
		return $list;
	}

	/**
	 *	Returns a list of collected StyleSheet URLs.
	 *	@access		public
	 *	@return		array
	 */
	public function getUrlList(): array
	{
		$list	= [];
		foreach( $this->urls as $map ){
			foreach( $map as $url ){
				if( !preg_match( "@^[a-z]+://@", $url->url ) )
					$url->url	= $this->pathBase.$url->url;
				$list[]	= $url;
			}
		}
		return $list;
	}

	/**
	 *	Renders an HTML script tag with all collected StyleSheet URLs and blocks.
	 *	@access		public
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 *	@throws		IoException
	 */
	public function render( bool $forceFresh = FALSE ): string
	{
		$urls		= $this->getUrlList();
		$styles		= $this->getStyleList();

		$linkAttributes	= [
			'type'		=> 'text/css',
			'rel'		=> 'stylesheet',
			'media'		=> 'all',
		];

		$items		= [];
		if( $urls ){
			if( $this->useCompression ){
				$items[]	= HtmlTag::create( 'link', NULL, array_merge( $linkAttributes, [
					'href'		=> $this->getPackageFileName( $forceFresh )
				] ) );
			}
			else{
				foreach( $urls as $url ){
					if( $this->revision )
						$url->url	.= '?r'.$this->revision;
					$attributes	= array_merge( $linkAttributes, $url->attributes, [
						'href'		=> $url->url
					] );
					$items[]	= HtmlTag::create( 'link', NULL, $attributes );
				}
			}
		}
		if( $styles ){
			$content	= PHP_EOL.implode( PHP_EOL, $styles ).PHP_EOL.$this->indent;
			$attributes	= array( 'type' => 'text/css' );
			$items[]	= HtmlTag::create( 'style', $content, $attributes );
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
	 *	@throws		IoException
	 */
	protected function getPackageFileName( bool $forceFresh = FALSE ): string
	{
		$fileCss	= $this->getPackageCacheFileName();												//  calculate CSS package file name for collected CSS files
		if( file_exists( $fileCss ) && !$forceFresh )												//  CSS package file has been built before and is not to be rebuilt
			return $fileCss;																		//  return CSS package file name
		$combiner	= new CssCombiner();															//  load CSS combiner for nested CSS files
		$compressor	= new CssCompressor();															//  load CSS compressor
		$relocator	= new CssRelocator();															//  load CSS relocator
		$pathRoot	= (string) getEnv( 'DOCUMENT_ROOT' );										//  get server document root path for CSS relocator
		$pathSelf	= dirname( (string) getEnv( 'SCRIPT_FILENAME' ) );
		$pathSelf	= str_replace( $pathRoot, '', $pathSelf );								//  get path relative to document root for symbolic link map

		$symlinks	= [];																			//  prepare map of symbolic links for CSS relocator
		/** @var SplFileInfo $item */
		foreach( FolderLister::getFolderList( 'themes' ) as $item )									//  iterate theme folders
			if( is_link( ( $path = $item->getPathname() ) ) )										//  if theme folder is a link
				$symlinks['/'.$pathSelf.'/themes/'.$item->getFilename()] = realpath( $path );		//  not symbolic link

		$contents	= [];																		//  prepare empty package content list
		if( $this->revision )																		//  a revision is set
			$contents[]	= "/* @revision ".$this->revision." */\n";									//  add revision header to content list

		foreach( $this->getUrlList() as $url ){														//  iterate collected URLs
			if( str_starts_with( $url->url, 'http' ) ){												//  CSS resource is global (using HTTP)
				$contents[]	= NetReader::readUrl( $url->url );										//  read global CSS content and append to content list
				continue;																			//  skip to next without relocation etc.
			}
			$pathFile	= dirname( $url->url ).'/';													//  get path to CSS file within app
			$content	= FileReader::load( $url->url ) ?? '';										//  read local CSS content
			$content	= $combiner->combineString( $pathFile, $content, TRUE );					//  insert imported CSS files within CSS content
			if( preg_match( "/\/[a-z]+/", $content ) )												//  CSS content contains path notations
				$content	= $relocator->rewrite( $content, $pathFile, $pathRoot, $symlinks );		//  relocate resources paths in CSS content
			$contents[]	= $content;																	//  add CSS content after import and relocation
		}
		$content	= $compressor->compress( implode( "\n\n", $contents ) );						//  compress collected CSS contents
		FileWriter::save( $fileCss, $content );														//  save CSS package file
		return $fileCss;																			//  return CSS package file name
	}
}
