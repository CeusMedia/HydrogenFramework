<?php /** @noinspection PhpUnused */

/**
 *	Helper to collect and combine JavaScripts.
 *	This is a singleton.
 *
 *	Copyright (c) 2010-2022 Christian Würker (ceusmedia.de)
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
 *	@copyright		2010-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper;

use CeusMedia\Common\Alg\JS\Minifier as JsMinifier;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\FS\File\Iterator as FileIterator;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\API\Google\ClosureCompiler as NetClosureCompiler;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;

use Throwable;

/**
 *	Component to collect and combine JavaScripts.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class JavaScript
{
	public string $indent				= "\t\t";

	protected static ?self $instance	= NULL;

	protected Environment $env;

	protected string $pathCache			= "cache/";

	protected string $prefix			= '';

	protected string $suffix			= '';

	protected ?string $revision			= NULL;

	/**	@var	array					$scripts			List of JavaScript blocks */
	protected array $scripts			= [];

	/**	@var	array					$scriptsOnReady		List if JavaScripts to run on load if browser is ready */
	protected array $scriptsOnReady		= [];

	protected array $urls				= [];

	protected bool $useCompression		= FALSE;

	/**
	 *	Adds a module JavaScript by path name within configured local JavaScript folder.
	 *	Uses addUrl with configured  local JavaScript folder.
	 *	@access		public
	 *	@param		string			$filePath	Path of file within JavaScript folder
	 *	@param		integer|NULL	$level		Optional: Load level (1-9, default: 5)
	 *	@param		string|NULL		$key		Optional: script key in case of later removal
	 *	@return		self
	 */
	public function addModuleFile(string $filePath, ?int $level = CaptainResource::LEVEL_MID, string $key = NULL ): self
	{
		$path	= $this->env->getConfig()->get( 'path.scripts' );
		$level	??= CaptainResource::LEVEL_MID;
		return $this->addUrl( $path.$filePath, $level, $key );
	}

	/**
	 *	Collect a JavaScript block.
	 *	@access		public
	 *	@param		string			$script		JavaScript block
	 *	@param		integer|NULL	$level		Optional: Load level (1-9, default: 5)
	 *	@param		string|NULL		$key		Optional: script key in case of later removal
	 *	@return		self
	 */
	public function addScript( string $script, ?int $level = CaptainResource::LEVEL_MID, string $key = NULL ): self
	{
		$level	??= CaptainResource::LEVEL_MID;
		if( !array_key_exists( $level, $this->scripts ) )										//  level is not yet defined in scripts list
			$this->scripts[$level]	= [];													//  create empty scripts list for level
		$key	= strlen( $key ) ? md5( $key ) : 'default';
		if( !array_key_exists( $key, $this->scripts[$level] ) )
			$this->scripts[$level][$key]	= [];
		$this->scripts[$level][$key][]	= $script;
		return $this;
	}

	/**
	 *	Appends JavaScript code to be run after Browser finished rendering (document.ready).
	 *	@access		public
	 *	@param		string			$script		JavaScript code to execute on ready
	 *	@param		integer|NULL	$level		Optional: Load level (1-9, default: 5)
	 *	@param		string|NULL		$key		Optional: script key in case of later removal
	 *	@return		self
	 *	@todo		implement support of a script key (3rd argument)
	 */
	public function addScriptOnReady( string $script, ?int $level = CaptainResource::LEVEL_MID, string $key = NULL ): self
	{
		$level	??= CaptainResource::LEVEL_MID;
		if( !array_key_exists( $level, $this->scriptsOnReady ) )								//  level is not yet defined in scripts list
			$this->scriptsOnReady[$level]	= [];											//  create empty scripts list for level
		$key	= strlen( $key ) ? md5( $key ) : 'default';
		if( !array_key_exists( $key, $this->scriptsOnReady[$level] ) )
			$this->scriptsOnReady[$level][$key]	= [];
		$this->scriptsOnReady[$level][$key][]	= $script;										//  note JavaScript code on runlevel
		return $this;
	}

	/**
	 *	Add a JavaScript URL.
	 *	@access		public
	 *	@param		string			$url		JavaScript URL
	 *	@param		integer|NULL	$level		Optional: Load level (1-9, default: 5)
	 *	@param		string|NULL		$key		Optional: script key in case of later removal
	 *	@return		self
	 *	@todo		implement support of a script key (3rd argument)
	 */
	public function addUrl( string $url, ?int $level = CaptainResource::LEVEL_MID, string $key = NULL ): self
	{
		$level	??= CaptainResource::LEVEL_MID;
		if( !array_key_exists( $level, $this->urls ) )											//  level is not yet defined in scripts list
			$this->urls[$level]	= [];														//  create empty scripts list for level
		$key	= strlen( $key ) ? md5( $key ) : 'default';
		if( !array_key_exists( $key, $this->urls[$level] ) )
			$this->urls[$level][$key]	= [];
		$this->urls[$level][$key][]	= $url;														//  note JavaScript code on runlevel
		return $this;
	}

	/**
	 *	Removes all combined scripts in file cache.
	 *	@access		public
	 *	@return		self
	 */
	public function clearCache(): self
	{
		$index			= new FileIterator( $this->pathCache );
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
		return $this;
	}

	/**
	 *	Returns a single instance of this Singleton class.
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env
	 *	@return		self						Single instance of this Singleton class
	 */
	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	/**
	 *	Returns hash calculated by added URLs and revision, if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getPackageHash(): string
	{
		$copy	= $this->getPlainUrlList();
		sort( $copy );
		$key	= implode( '_', $copy );
		return md5( $this->revision.$key );
	}

	/**
	 *	Returns a flat list of collected JavaScripts URLs ordered by run level.
	 *	@access		public
	 *	@return		array		List of registered script URLS ordered by run level and script key
	 */
	public function getPlainUrlList(): array
	{
		$list	= [];
		foreach( $this->urls as $levelUrls )
			foreach( $levelUrls as $keyedUrls )
				foreach( $keyedUrls as $url )
					$list[]	= $url;
		return $list;
	}

	/**
	 *	Returns a list of collected JavaScripts URLs structured by run level and script key.
	 *	@access		public
	 *	@return		array		List of registered script URLS structured by run level and script key
	 */
	public function getStructuredUrlList(): array
	{
		return $this->urls;
	}

	/**
	 *	Renders an HTML script tag with all collected JavaScript URLs and blocks.
	 *	@access		public
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache, default: no
	 *	@return		string
	 *	@throws		IoException
	 */
	public function render( bool $forceFresh = FALSE ): string
	{
		$links			= $this->renderUrls( $forceFresh );
		$scripts		= $this->renderScripts( TRUE );
		$scriptsOnReady	= $this->renderScriptsOnReady( TRUE );
		return $links.PHP_EOL.$scriptsOnReady.PHP_EOL.$scripts;
	}

	/**
	 *	Sets revision for versioning cache.
	 *	@access		public
	 *	@param		string		$revision	Revision number or version string
	 *	@return		self
	 */
	public function setRevision( string $revision ): self
	{
		$this->revision	= $revision;
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


	//  --  PROTECTED  --  //

	/**
	 *	Constructor is disabled from public context.
	 *	Use static call 'getInstance()' instead of 'new'.
	 *	@access		protected
	 *	@param		Environment	$env		Environment object
	 *	@return		void
	 */
	protected function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	protected function compress( string $script ): string
	{
//		$script	= preg_replace( "@^\s*//.+\n?@", "", $script );
//		$script	= preg_replace( "@/\*.+\*/\n?@sU", "", $script );
		try{
			return JsMinifier::minify( $script );
		}
		catch( Throwable $t ){
		}
		try{
			return NetClosureCompiler::minify( $script );
		}
		catch( Throwable $t ){}
		return $script;
	}

	/**
	 *	Returns name of combined file in file cache.
	 *	@access		protected
	 *	@return		string
	 */
	protected function getPackageCacheFileName(): string
	{
		$hash	= $this->getPackageHash();
		return $this->pathCache.$this->prefix.$hash.$this->suffix.'.js';
	}

	/**
	 *	Returns name of combined JavaScript file.
	 *	@access		protected
	 *	@param		bool		$forceFresh		Flag: force fresh creation instead of using cache
	 *	@return		string
	 *	@throws        IoException
	 */
	protected function getPackageFileName( bool $forceFresh = FALSE ): string
	{
		$fileJs	= $this->getPackageCacheFileName();
		if( !file_exists( $fileJs ) || $forceFresh ) {
			$contents	= [];
			if( $this->revision )
				$contents[]	= "/* @revision ".$this->revision." */";
			foreach( $this->getPlainUrlList() as $url ){
				if( preg_match( "/^http/", $url ) )
					$content	= NetReader::readUrl( $url );
				else
					$content	= FileReader::load( $url );
				if( preg_match( "/\.min\.js$/", $url ) )
					array_unshift( $contents, preg_replace( "@/\*.+\*/\n?@sU", "", $content ) );
				else
					$contents[]	= $this->compress( $content );
			}
			$content	= implode( PHP_EOL.PHP_EOL, $contents );
			FileWriter::save( $fileJs, $content );
		}
		return $fileJs;
	}

	/**
	 *	Renders block of collected JavaScript code with directive to run if Browser finished loading (using jQuery event document.ready).
	 *	@access		protected
	 *	@param		boolean		$wrapInTag		Flag: wrap code in HTML script tag, default: no
	 *	@return		string		Combined JavaScript code to run if Browser is ready
	 */
	protected function renderScripts( bool $wrapInTag = FALSE ): string
	{
		$list	= [];
		ksort( $this->scripts );
		foreach( $this->scripts as $levelScripts )
			foreach( $levelScripts as $scripts )
				foreach( $scripts as $script )
					$list[]	= preg_replace( "/;+$/", ";", trim( $script ) );
		if( !count( $list ) )
			return '';
		$content		= join( "\n", $list );
		if( $this->useCompression )
			$content	= $this->compress( $content );
		if( $wrapInTag )
			$content	= HtmlTag::create( 'script', $content, array( 'type' => 'text/javascript' ) );
		return $content;
	}

	/**
	 *	Renders block of collected JavaScript code with directive to run if Browser finished loading (using jQuery event document.ready).
	 *	@access		protected
	 *	@param		boolean		$wrapInTag		Flag: wrap code in HTML script tag, default: no
	 *	@return		string		Combined JavaScript code to run if Browser is ready
	 */
	protected function renderScriptsOnReady( bool $wrapInTag = FALSE ): string
	{
		$list	= [];
		ksort( $this->scriptsOnReady );
		foreach( $this->scriptsOnReady as $levelScripts ){
			foreach( $levelScripts as $scripts ){
				foreach( $scripts as $script ){
					if( !$this->useCompression ){
						$script		= preg_replace( "/;+$/", ";", trim( $script ) );
						if( preg_match( "/\r?\n/", $script ) ){
							$lines	= (array) preg_split( "/\r?\n/", PHP_EOL.$script.PHP_EOL );
							$script	= join( PHP_EOL."\t", $lines );
						}
					}
					$list[]	= "jQuery(function(){".rtrim( $script, "\t;" )."});";
				}
			}
		}
		if( !count( $list ) )
			return '';
		$content	= PHP_EOL.trim( join( PHP_EOL, $list ) ).PHP_EOL;
		if( $this->useCompression )
			$content	= $this->compress( $content );
		if( $wrapInTag )
			$content	= HtmlTag::create( 'script', $content, array( 'type' => 'text/javascript' ) );
		return $content;
	}

	/**
	 *	@param		bool		$forceFresh
	 *	@return		string
	 *	@throws		IoException
	 */
	protected function renderUrls(bool $forceFresh = FALSE ): string
	{
		if( !count( $this->getPlainUrlList() ) )
			return '';
		if( $this->useCompression ){
			$fileJs	= $this->getPackageFileName( $forceFresh );
			if( $this->revision )
				$fileJs	.= '?'.$this->revision;
			$attributes	= [
				'type'		=> 'text/javascript',
	//			'language'	=> 'JavaScript',
				'src'		=> $fileJs
			];
			$links	= HtmlTag::create( 'script', NULL, $attributes );
		}
		else{
			$list	= [];
			foreach( $this->getPlainUrlList() as $url ){
				if( $this->revision )
					$url	.= ( preg_match( '/\?/', $url ) ? '&amp;' : '?' ).$this->revision;
				$attributes	= [
					'type'		=> 'text/javascript',
		//			'language'	=> 'JavaScript',
					'src'		=> $url
				];
				$list[]	= HtmlTag::create( 'script', NULL, $attributes );
			}
			$links	= implode( "\n".$this->indent, $list  );
		}
		return $links;
	}

	//  --  PRIVATE  --  //

	/**
	 *	Cloning this object is not allowed.
	 *	@access		private
	 *	@return		void
	 */
	private function __clone()
	{
	}
}
