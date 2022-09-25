<?php
/**
 *	XHTML Page Resource of Framework Hydrogen.
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Component\Config as ConfigComponent;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Component\File as FileComponent;
use CeusMedia\HydrogenFramework\View\Helper\StyleSheet as CssHelper;
use CeusMedia\HydrogenFramework\View\Helper\JavaScript as JsHelper;

use InvalidArgumentException;
use RuntimeException;
use stdClass;

/**
 *	XHTML Page Resource of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Page extends HtmlPage
{
	/**	@var	Environment		$env				Environment object */
	public $env;

	protected $bodyClasses		= [];

	protected $packJavaScripts	= FALSE;

	protected $packStyleSheets	= FALSE;

	protected $pathPrimer;

	protected $pathCommon;

	protected $pathTheme;

	/**	@var	JsHelper		$js					JavaScript Collector Helper */
	public $js;

	/**	@var	stdClass								$css				CSS containers (primer, theme) */
	public $css;

//	/**	@var	CMM_TEA_Factory							$tea				Instance of TEA (Template Engine Abstraction) Factory (from cmModules) OR empty if TEA is not available */
//	public $tea					= NULL;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$language	= 'en';
		if( $this->env->has( 'language' ) )
			$language	= $this->env->getLanguage()->getLanguage();

		parent::__construct( 'XHTML_10_STRICT', $language );
		$this->js		= JsHelper::getInstance( $env );

		$config			= $env->getConfig();
		$pathThemes		= rtrim( $config->get( 'path.themes' ), '/' ).'/';
		$this->pathPrimer	= $pathThemes;
		if( $config->get( 'layout.primer' ) )
			$this->pathPrimer	= $pathThemes.$config->get( 'layout.primer' ).'/';
		$this->pathCommon	= $pathThemes.'common/';
		$this->pathTheme	= $pathThemes.$config->get( 'layout.theme' ).'/';
		$this->css			= new stdClass;
		$this->css->primer	= new CssHelper( $this->pathPrimer.'css/' );
		$this->css->common	= new CssHelper( $this->pathCommon.'css/' );
		$this->css->theme	= new CssHelper( $this->pathTheme.'css/' );
		$this->css->lib		= new CssHelper();
		if( $config->get( 'app.revision' ) ){
			$this->css->primer->setRevision( $config->get( 'app.revision' ) );
			$this->css->theme->setRevision( $config->get( 'app.revision' ) );
			$this->js->setRevision( $config->get( 'app.revision' ) );
		}

		if( strlen( $title	= $config->get( 'app.name' ) ) )
			$this->setTitle( $title );
		$this->env->getModules()->callHook( 'Page', 'init', $this );								//  call related module event hooks
	}

	/**
	 *	Note to class to be set on body tag.
	 *	@access		public
	 *	@param		string		$class			Class to be set on body tag
	 *	@return		self						Instance for method chaining
	 */
	public function addBodyClass( string $class ): self
	{
		if( strlen( trim( $class ) ) ){
			$classFixed	= trim( htmlentities( $class, ENT_QUOTES, 'UTF-8' ) );
			if( !in_array( $classFixed, $this->bodyClasses ) )
				$this->bodyClasses[]	= $classFixed;
		}
		return $this;
	}

	/**
	 *	Note to load style file from common style folder.
	 *	@access		public
	 *	@param		string		$fileName		Style file to load from common style folder
	 *	@param		integer		$level			Load level, default: 5 (mid), less: earlier, more: later
	 *	@param		array		$attributes		Map of style tag attributes
	 *	@return		self						Instance for method chaining
	 */
	public function addCommonStyle( string $fileName, int $level = Captain::LEVEL_MID, array $attributes = [] ): self
	{
		$this->css->common->addUrl( $fileName, $this->interpretLoadLevel( $level ), $attributes );
		return $this;
	}

	/**
	 *	Note to load style file from primer style folder.
	 *	@access		public
	 *	@param		string		$fileName		Style file to load from primer style folder
	 *	@param		integer		$level			Load level, default: 5 (mid), less: earlier, more: later
	 *	@param		array		$attributes		Map of style tag attributes
	 *	@return		self						Instance for method chaining
	 */
	public function addPrimerStyle( string $fileName, int $level = Captain::LEVEL_MID, array $attributes = [] ): self
	{
		$this->css->primer->addUrl( $fileName, $this->interpretLoadLevel( $level ), $attributes );
		return $this;
	}

	/**
	 *	Note to load style file from theme style folder.
	 *	@access		public
	 *	@param		string		$fileName		Style file to load from theme style folder
	 *	@param		integer		$level			Load level, default: 5 (mid), less: earlier, more: later
	 *	@param		array		$attributes		Map of style tag attributes
	 *	@return		self						Instance for method chaining
	 */
	public function addThemeStyle( string $fileName, int $level = Captain::LEVEL_MID, array $attributes = [] ): self
	{
		$this->css->theme->addUrl( $fileName, $this->interpretLoadLevel( $level ), $attributes );
		return $this;
	}

	/**
	 *	@todo		apply JS levels after JsHelper is supporting it
	 */
	public function applyModules()
	{
		$config		= $this->env->getConfig()->getAll( 'module.', TRUE );			//  dictionary of (user modified) module settings
		$modules	= $this->env->getModules();														//  get module handler resource
		if( 0 === $modules->count() )																//  no active modules found
			return;

		$pathScripts	= $this->env->getConfig()->get( 'path.scripts' );
		$pathScriptsLib	= $this->env->getConfig()->get( 'path.scripts.lib' );
		$pathStylesLib	= $this->env->getConfig()->get( 'path.styles.lib' );
		$settings		= [];

		foreach( $modules->getAll() as $module ){													//  iterate installed modules
			$settings[$module->id]	= [
//				'_id'		=> $module->id,
//				'_title'	=> $module->title,
//				'_version'	=> $module->version,
			];
			/** @var FileComponent $style */
			foreach( $module->files->styles as $style ){											//  iterate module style files
				if( !empty( $style->load ) && $style->load == "auto" ){								//  style file is to be loaded always
					$source	= !empty( $style->source ) ? $style->source : NULL;						//  get source attribute if possible
					$level	= $this->interpretLoadLevel( $style->level ?? Captain::LEVEL_MID );		//  get load level (top, mid, end), default: mid
					if( preg_match( "/^[a-z]+:\/\/.+$/", $style->file ) )					//  style file is absolute URL
						$this->css->theme->addUrl( $style->file, $level );							//  add style file URL
					else if( $source == 'primer' )													//  style file is in primer theme
						$this->addPrimerStyle( $style->file, $level );								//  load style file from primer theme folder
					else if( $source == 'common' )													//  style file is in common theme
						$this->addCommonStyle( $style->file, $level );								//  load style file from common theme folder
					else if( $source == 'lib' ){													//  style file is in styles library, which is enabled by configured path
						if( !strlen( trim( $pathStylesLib ) ) )
							throw new RuntimeException( 'Path to style library "path.styles.lib" is not configured' );
						$this->css->lib->addUrl( $pathStylesLib.$style->file, $level );				//  load style file from styles library
					}
					else if( $source == 'scripts-lib' && $pathScriptsLib ){							//  style file is in scripts library, which is enabled by configured path
						if( !strlen( trim( $pathScriptsLib ) ) )
							throw new RuntimeException( 'Path to script library "path.scripts.lib" is not configured' );
						$this->css->primer->addUrl( $pathScriptsLib.$style->file, $level );			//  load style file from scripts library
					}
					else if( $source == 'theme' || !$source )										//  style file is in custom theme
						$this->addThemeStyle( $style->file, $level );								//  load style file from custom theme folder
					else																			//  style file is in an individual source folder within themes folder
						$this->css->primer->addUrl( /*$path.$source.'/'.*/$style->file );				//  load style file /*from source folder within themes folder*/
				}
			}
			/** @var FileComponent $script */
			foreach( $module->files->scripts as $script ){											//  iterate module script files
				if( !empty( $script->load ) && $script->load == "auto" ){							//  script file is to be loaded always
					$source	= empty( $script->source ) ? 'local' : $script->source;
					$level	= $this->interpretLoadLevel( $script->level ?? Captain::LEVEL_MID );		//  get load level (top, mid, end, ready), default: mid
					$top	= !empty( $script->top ) || $level === Captain::LEVEL_TOP;				//  get flag attribute for appending on top
					if( $source == 'lib' ){															//  script file is in script library
						if( $top )																	//
							$this->addJavaScript( $pathScriptsLib.$script->file );					//
						else																		//
							$this->js->addUrl( $pathScriptsLib.$script->file/*, $level*/ );			//  load script file from script library
					}
					else if( $source == 'local' ){													//  script file is in app scripts folder
						if( $top )																	//
							$this->addJavaScript( $pathScripts.$script->file );						//
						else																		//
							$this->js->addUrl( $pathScripts.$script->file/*, $level*/ );			//  load script file from app scripts folder
					}
					else if( $source == 'url' ){													//  script file is absolute URL
						if( !preg_match( "/^[a-z]+:\/\/.+$/", $script->file ) ){
							$msg	= 'Invalid script URL: '.$script->file;
							throw new InvalidArgumentException( $msg );
						}
						$this->js->addUrl( $script->file, $source );								//  add script file URL
					}
				}
			}
			/** @var ConfigComponent $pair */
			foreach( $module->config as $pair ){													//  iterate module configuration pairs
				if( !empty( $pair->protected ) && $pair->protected !== 'yes' ){
					$value	= $config->get( strtolower( $module->id ).'.'.$pair->key );				//  get (user modified) module setting value
					$settings[$module->id][str_replace( '.', '_', $pair->key )]	= $value;			//  note module setting
				}
			}
			if( !$settings[$module->id] )
				unset( $settings[$module->id] );
		}
		$modules->callHook( 'Page', 'applyModules', $this );										//  call related module event hooks

		if( $this->env instanceof WebEnvironment ){
			$settings['Env']	= [
				'host'		=> $this->env->host,
				'port'		=> $this->env->port,
				'protocol'	=> $this->env->scheme,
				'domain'	=> $this->env->host.( $this->env->port ? ':'.$this->env->port : '' ),
				'path'		=> $this->env->path,
	//			'title'		=> $this->env->title,
				'secure'	=> getEnv( 'HTTPS' ),
			];
			$script		= 'var settings = '.json_encode( $settings ).';';
			$script		= HtmlTag::create( 'script', "<!--\n".$script."\n-->", ['type' => "text/javascript"] );
			$this->addHead( $script );
		}
	}

	/**
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public function build( $bodyAttributes = [], $htmlAttributes = [] ): string
	{
		$controller			= $this->env->getRequest()->get( '__controller' );
		$action				= $this->env->getRequest()->get( '__action' );
		$controllerKey		= str_replace( '/', '-', $controller );
		$actionKey			= str_replace( '/', '-', $action );										//  @todo to be removed
		$moduleKeyOld		= join( explode( ' ', ucwords( str_replace( '/', ' ', $controller ) ) ) );		//  @todo to be removed
		$this->addBodyClass( 'module'.$moduleKeyOld );

		$modules			= $this->env->getModules();												//  get installed modules
		$controllerClass	= str_replace( ' ', '_', ucwords( str_replace( '/', ' ', $controller ) ) );
		$module				= $modules->getModuleFromControllerClassName( $controllerClass );		//  try to get module of controller
		if( $module ){																				//  module has been identified
			$moduleKey		= str_replace( '_', '', $module->id );
			$this->addBodyClass( 'module'.$moduleKey );
		}

		$this->addBodyClass( 'controller-'.$controllerKey );
		$this->addBodyClass( 'action-'.$actionKey );
		$this->addBodyClass( 'site-'.$controllerKey.'-'.$actionKey );

		$data	= [
			'content'   => $this->getBody(),
		];
		$modules->callHookWithPayload( 'Page', 'build', $this, $data );									//  call related module event hooks
		$this->setBody( $data['content'] );

		if( $this->packStyleSheets && $this->env->getRequest()->has( 'flushStyleCache') ){
			$this->css->primer->clearCache();
			$this->css->common->clearCache();
			$this->css->theme->clearCache();
			$this->css->lib->clearCache();
		}

		$headStyleBlocks	= [
			'primer'	=> $this->css->primer->render( $this->packStyleSheets ),
			'common'	=> $this->css->common->render( $this->packStyleSheets ),
			'theme'		=> $this->css->theme->render( $this->packStyleSheets ),
			'lib'		=> $this->css->lib->render( $this->packStyleSheets ),
		];
		foreach( $headStyleBlocks as $blocksContent )
			if( strlen( trim( $blocksContent ) ) !== 0 )
				$this->addHead( $blocksContent );

		$this->addBody( $this->js->render() );

		/*  --  BODY CLASSES  --  */
		$classes	= [];
		foreach( $this->bodyClasses as $class )
			$classes[]	= $class;
		if( isset( $bodyAttributes['class'] ) && strlen( trim( $bodyAttributes['class'] ) ) )
			foreach( explode( " ", trim( $bodyAttributes['class'] ) ) as $class )
				$classes[]	= $class;
		$bodyAttributes['class']	= join( ' ', $classes );
		$this->env->getModules()->callHook( 'App', 'respond', $this );				//  call related module event hooks
		return parent::build( $bodyAttributes, $htmlAttributes );
	}

	/**
	 *	Returns path to theme or primer.
	 *	@access		public
	 *	@param		boolean		$primer			Flag: return path to primer instead of theme
	 *	@return		string						Path to theme or primer
	 */
	public function getThemePath( bool $primer = FALSE ): string
	{
		return $primer ? $this->pathPrimer : $this->pathTheme;
	}

	/**
	 *	Try to understand given load level.
	 *	Matches given value into a scale between 0 and 9.
	 *	Contains fallback for older module versions using level as string (top,mid,end) or boolean.
	 *	Understands:
	 *	- integer (limited to [0-9])
	 *	- NULL or empty string as level 4 (mid).
	 *	- boolean TRUE as level 1 (top).
	 *	- boolean FALSE as level 4 (mid).
	 *	- string {top,head,start} as level 1.
	 *	- string {mid,center,normal,default} as level 4.
	 *	- string {end,tail,bottom} as level 8.
	 *	@static
	 *	@access		public
	 *	@param		mixed			$level 			Load level: 0-9 or {top(1),mid(4),end(8)} or {TRUE(1),FALSE(4)} or NULL(4)
	 *	@return		integer			Level as integer value between 0 and 9
	 *	@throws		InvalidArgumentException		if level is not if type NULL, boolean, integer or string
	 *	@throws		RangeException					if given string is not within {top,head,start,mid,center,normal,default,end,tail,bottom}
	 */
	public static function interpretLoadLevel( $level ): int
	{
		if( is_null( $level ) || !strlen( trim( $level ) ) )
			return Captain::LEVEL_MID;
		if( is_int( $level ) || ( is_string( $level ) && preg_match( '/^[0-9]$/', trim( $level ) ) ) )
			return min( max( abs( $level ), Captain::LEVEL_TOP), Captain::LEVEL_END );
		if( is_bool( $level ) )
			return $level ? Captain::LEVEL_HIGH : Captain::LEVEL_LOW;
		if( !is_string( $level ) )
			throw new InvalidArgumentException( 'Load level must be integer or string' );
		if( in_array( $level, array( 'top', 'head', 'start' ) ) )
			return Captain::LEVEL_HIGHEST;
		if( in_array( $level, array( 'mid', 'center', 'normal', 'default' ) ) )
			return Captain::LEVEL_MID;
		if( in_array( $level, array( 'end', 'tail', 'bottom' ) ) )
			return Captain::LEVEL_LOWEST;
		throw new RangeException( 'Invalid load level: '.$level );
	}

	/**
	 *	Notes to load a JavaScript in local script folder.
	 *	@access		public
	 *	@param		string		$filePath		Script file path within scripts folder
	 *	@param		integer		$level			Run level (load order), default: 5 (mid), less: earlier, more: later
	 *	@return		self						Instance for method chaining
	 *	@throws		RuntimeException			if script file is not existing
	 */
	public function loadLocalScript( string $filePath, int $level = Captain::LEVEL_MID ): self
	{
		$path	= $this->env->getConfig()->get( 'path.scripts' );
		if( !file_exists( $path.$filePath ) )
			throw new RuntimeException( 'Local script "'.$filePath.'" not found in folder "'.$path.'"' );
		$this->js->addUrl( $path.$filePath, $this->interpretLoadLevel( $level ) );
		return $this;
	}

	/**
	 *	Appends JavaScript code to be run after browser finished rendering (document.ready).
	 *	@access		public
	 *	@param		string		$script			JavaScript code to execute on ready
	 *	@param		integer		$level			Run level (load order), default: 5 (mid), less: earlier, more: later
	 *	@return		self						Instance for method chaining
	 */
	public function runScript( string $script, int $level = Captain::LEVEL_MID ): self
	{
		$this->js->addScriptOnReady( $script, $this->interpretLoadLevel( $level ) );
		return $this;
	}

	/**
	 *	Deprecated.
	 *	@param		boolean		$packJavaScripts		Flag: pack collected script files
	 *	@param		boolean		$packStyleSheets		Flag: pack collected style files
	 *	@return		self								Instance for method chaining
	 *	@deprecated		will be removed in favour of module UI_Compressor
	 *	@todo			step 1: enable messenger note and let apps adjust
	 *	@todo			step 2: remove method
	 *	@todo			step 3: remove compression in JS and CSS helpers
	 */
	public function setPackaging( bool $packJavaScripts = FALSE, bool $packStyleSheets = FALSE ): self
	{
//		$this->env->getMessenger()->noteNotice( '<b>Deprecation: </b>Calling Page::setPackaging is deprecated. Please use module UI:Compressor instead.' );
#		$this->js->setCompression( $packJavaScripts );
#		$this->css->primer->setCompression( $packStyleSheets );
#		$this->css->theme->setCompression( $packStyleSheets );
		$this->packJavaScripts	= $packJavaScripts;
		$this->packStyleSheets	= $packStyleSheets;
		return $this;
	}
}
