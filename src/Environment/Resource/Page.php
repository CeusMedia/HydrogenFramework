<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	XHTML Page Resource of Framework Hydrogen.
 *
 *	Copyright (c) 2010-2025 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Constant;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\View\Helper\StyleCascade;
use CeusMedia\HydrogenFramework\View\Helper\JavaScript as JsHelper;

use InvalidArgumentException;
use ReflectionException;
use RuntimeException;

/**
 *	XHTML Page Resource of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Page extends HtmlPage
{
	/**	@var	Environment			$env				Environment object */
	public Environment $env;

	protected array $bodyClasses	= [];

	protected bool $packJavaScripts	= FALSE;

	protected bool $packStyleSheets	= FALSE;

	protected string $pathPrimer;

	protected string $pathCommon;

	protected string $pathTheme;

	/**	@var	JsHelper			$js					JavaScript Collector Helper */
	public JsHelper $js;

	/**	@var	StyleCascade		$css				CSS containers (primer, theme) */
	public StyleCascade $css;

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
		$this->css	= new StyleCascade( [
			'primer'	=> $this->pathPrimer.'css/',
			'common'	=> $this->pathCommon.'css/',
			'theme'		=> $this->pathTheme.'css/',
			'lib'		=> NULL,
		] );
		if( $config->get( 'app.revision' ) ){
			$this->css->get( 'primer' )->setRevision( $config->get( 'app.revision' ) );
			$this->css->get( 'theme' )->setRevision( $config->get( 'app.revision' ) );
			$this->js->setRevision( $config->get( 'app.revision' ) );
		}

		$title	= trim( (string) $config->get( 'app.name', '' ) );
		if( '' !== $title )
			$this->setTitle( $title );
		$this->env->getCaptain()->callHook( 'Page', 'init', $this );					//  call related module event hooks
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
		$this->css->get( 'common' )->addUrl( $fileName, self::interpretLoadLevel( $level ), $attributes );
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
		$this->css->get( 'primer' )->addUrl( $fileName, self::interpretLoadLevel( $level ), $attributes );
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
		$this->css->get( 'theme' )->addUrl( $fileName, self::interpretLoadLevel( $level ), $attributes );
		return $this;
	}

	/**
	 *	@todo		apply JS levels after JsHelper is supporting it
	 */
	public function applyModules(): void
	{
		$modules	= $this->env->getModules()->getAll();											//  get list of active modules

		/** @var Dictionary $config */
		$config			= $this->env->getConfig()->getAll( 'module.', TRUE );			//  dictionary of (user modified) module settings
		$pathScripts	= $this->env->getConfig()->get( 'path.scripts', '' );
		$pathScriptsLib	= $this->env->getConfig()->get( 'path.scripts.lib', '' );
		$pathStylesLib	= $this->env->getConfig()->get( 'path.styles.lib', '' );

		$this->applyModulesStyles( $modules, $pathStylesLib, $pathScriptsLib );
		$this->applyModulesScripts( $modules, $pathScripts, $pathScriptsLib );
		$this->applyModulesConfigs( $modules, $config );

		$this->env->getCaptain()->callHook( 'Page', 'applyModules', $this );			//  call related module event hooks
	}

	/**
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public function build( $bodyAttributes = [], $htmlAttributes = [] ): string
	{
		$payload	= ['content' => $this->getBody()];
		$this->env->getCaptain()->callHook( 'Page', 'build', $this, $payload );	//  call related module event hooks
		$this->setBody( $payload['content'] );

		$this->decorateStyles();
		$this->decorateBodyClasses( $bodyAttributes );
		$this->addBody( $this->js->render() );

		// @todo move this call to app! refactor existing hooks in modules on this event
		$this->env->getCaptain()->callHook( 'App', 'respond', $this );				//  call related module event hooks
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
	 *	- integer (limited to [1-9])
	 *	- NULL, integer 0 or empty string as level 5 (mid).
	 *	- boolean TRUE as level 1 (top).
	 *	- boolean FALSE as level 9 (mid).
	 *	- string {top,start,head} as level 1.
	 *	- string {highest,higher,high} as levels 2, 3, 4.
	 *	- string {mid,center,normal,default,unknown} as level 5.
	 *	- string {low,lower,lowest} as levels 6, 7, 8.
	 *	- string {end,tail,bottom} as level 9.
	 *	- anything else as level 5.
	 *	@static
	 *	@access		public
	 *	@param		int|string|bool|NULL		$level		Load level: 0-9 or {top(1),mid(5),end(9)} or {TRUE(3),FALSE(7)} or NULL(5)
	 *	@return		integer						Level as integer value between 0 and 9
	 */
	public static function interpretLoadLevel( int|string|bool|NULL $level = NULL ): int
	{
		if( NULL === $level || 0 === $level )
			return Captain::LEVEL_MID;
		if( is_int( $level ) )
			return min( max( abs( $level ), Captain::LEVEL_TOP ), Captain::LEVEL_END );
		if( is_bool( $level ) )
			return $level ? Captain::LEVEL_HIGH : Captain::LEVEL_LOW;
		$level	= trim( $level );
		if( in_array( $level, ['center', 'normal', 'default', 'unknown', ''], TRUE ) )
			return Captain::LEVEL_MID;
		if( 0 !== preg_match( '/^[1-9]$/', $level ) )
			return intval( $level );
		$level	= $level == 'head' ? 'top' : $level;
		$level	= $level == 'tail' ? 'end' : $level;
		$levelConstants	= new Constant( Captain::class );											//  reflect constants of Captain

		try{
			if( $levelConstants->hasValue( strtoupper( $level ), 'LEVEL' ) )
				return $levelConstants->getValue( strtoupper( $level ), 'LEVEL' );
		}
		catch( ReflectionException ){
		}
		return Captain::LEVEL_MID;
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
		$this->js->addUrl( $path.$filePath, self::interpretLoadLevel( $level ) );
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
		$this->js->addScriptOnReady( $script, self::interpretLoadLevel( $level ) );
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
#		$this->css->get( 'primer' )->setCompression( $packStyleSheets );
#		$this->css->get( 'theme' )->setCompression( $packStyleSheets );
		$this->packJavaScripts	= $packJavaScripts;
		$this->packStyleSheets	= $packStyleSheets;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		ModuleDefinition[]	$modules
	 *	@param		Dictionary			$config
	 *	@return		void
	 */
	protected function applyModulesConfigs( array $modules, Dictionary $config ): void
	{
		$settings	= [];
		foreach( $modules as $module ){													//  iterate installed modules
			if( [] === $module->config )
				continue;
			$settings[$module->id]	= [
				//				'_id'		=> $module->id,
				//				'_title'	=> $module->title,
				//				'_version'	=> $module->version,
			];
			foreach( $module->config as $pair ){													//  iterate module configuration pairs
				if( 'yes' === $pair->protected )													//  don't carry protected pairs to js
					continue;
				$phpKey	= strtolower( $module->id ).'.'.$pair->key;
				$jsKey	= str_replace( '.', '_', $pair->key );
				$settings[$module->id][$jsKey]	= $config->get( $phpKey );							//  note module setting
			}
		}
		if( $this->env instanceof WebEnvironment ){
			$settings['Env']	= [
				'host'		=> $this->env->host,
				'port'		=> $this->env->port,
				'protocol'	=> $this->env->scheme,
				'domain'	=> $this->env->host.( '' !== trim( $this->env->port ) ? ':'.$this->env->port : '' ),
				'path'		=> $this->env->path,
	//			'title'		=> $this->env->title,
				'secure'	=> getenv( 'HTTPS' ),
			];
			$script		= 'var settings = '.json_encode( $settings ).';';
			$script		= HtmlTag::create( 'script', "<!--\n".$script."\n-->", ['type' => "text/javascript"] );
			$this->addHead( $script );
		}
	}

	/**
	 *	@param		ModuleDefinition[]	$modules
	 *	@param		string				$pathScripts
	 *	@param		string				$pathScriptsLib
	 *	@return		void
	 */
	protected function applyModulesScripts( array $modules, string $pathScripts, string $pathScriptsLib ): void
	{
		foreach( $modules as $module ){																//  iterate installed modules
			foreach( $module->files->scripts as $script ){											//  iterate module script files
				if( 'auto' !== $script->load )														//  script file is NOT to be loaded always
					continue;

				$source	= $script->source ?? 'local';
				$level	= self::interpretLoadLevel( $script->level ?? Captain::LEVEL_MID );	//  get load level (top, mid, end, ready), default: mid
				$isTopLevel	= Captain::LEVEL_TOP === $level;

				switch( $source ){
					case 'lib':																		//  script file is in script library
						$filePath	= $pathScriptsLib.$script->file;
						if( $isTopLevel )															//
							$this->addJavaScript( $filePath );										//
						else																		//
							// @todo why is level disabled? check if better to enable
							$this->js->addUrl( $filePath/*, $level*/ );								//  load script file from script library
						break;
					case 'local':																	//  script file is in app scripts folder
						$filePath	= $pathScripts.$script->file;
						if( $isTopLevel )															//
							$this->addJavaScript( $filePath );										//
						else																		//
							// @todo why is level disabled? check if better to enable
							$this->js->addUrl( $filePath/*, $level*/ );								//  load script file from app scripts folder
						break;
					case 'url':																		//  script file is absolute URL
						$matchSchema	= preg_match( "/^[a-z]+:\/\/.+$/", $script->file );
						if( 0 !== ( $matchSchema ?: 0 ) )											//  file is not a URI
							throw new InvalidArgumentException( 'Invalid script URL: '.$script->file );
						$this->js->addUrl( $script->file );											//  add script file URL
						break;
				}
			}
		}
	}

	/**
	 *	@param		ModuleDefinition[]	$modules
	 *	@param		string				$pathStylesLib
	 *	@param		string				$pathScriptsLib
	 *	@return		void
	 */
	protected function applyModulesStyles( array $modules, string $pathStylesLib, string $pathScriptsLib ): void
	{
		foreach( $modules as $module ){																//  iterate installed modules
			foreach( $module->files->styles as $style ){											//  iterate module style files
				if( 'auto' === $style->load ){														//  style file is to be loaded always
					$source	= $style->source;														//  get source attribute if possible
					$level	= self::interpretLoadLevel( $style->level ?? Captain::LEVEL_MID );	//  get load level (top, mid, end), default: mid
					if( 0 !== ( preg_match( "/^[a-z]+:\/\/.+$/", $style->file ) ?: 0 ) )		//  style file is absolute URL
						$this->css->get( 'theme' )->addUrl( $style->file, $level );					//  add style file URL
					else if( 'primer' === $source )													//  style file is in primer theme
						$this->addPrimerStyle( $style->file, $level );								//  load style file from primer theme folder
					else if( 'common' === $source )													//  style file is in common theme
						$this->addCommonStyle( $style->file, $level );								//  load style file from common theme folder
					else if( 'lib' === $source ){													//  style file is in styles library, which is enabled by configured path
						if( '' === trim( $pathStylesLib ) )
							throw new RuntimeException( 'Path to style library "path.styles.lib" is not configured' );
						$this->css->get( 'lib' )->addUrl( $pathStylesLib.$style->file, $level );	//  load style file from styles library
					}
					else if( 'scripts-lib' === $source ){											//  style file is in scripts library, which is enabled by configured path
						if( '' === trim( $pathScriptsLib ) )
							throw new RuntimeException( 'Path to script library "path.scripts.lib" is not configured' );
						$this->css->get( 'primer' )->addUrl( $pathScriptsLib.$style->file, $level );	//  load style file from scripts library
					}
					else if( 'theme' === $source || !$source )										//  style file is in custom theme
						$this->addThemeStyle( $style->file, $level );								//  load style file from custom theme folder
					else																			//  style file is in an individual source folder within themes folder
						$this->css->get( 'primer' )->addUrl( /*$path.$source.'/'.*/$style->file );	//  load style file /*from source folder within themes folder*/
				}
			}
		}
	}

	protected function decorateBodyClasses( array & $bodyAttributes ): void
	{
		$controller			= $this->env->getRequest()->get( '__controller' );
		$action				= $this->env->getRequest()->get( '__action' );
		$controllerKey		= str_replace( '/', '-', $controller );
		$actionKey			= str_replace( '/', '-', $action );										//  @todo to be removed
		$moduleKeyOld		= join( explode( ' ', ucwords( str_replace( '/', ' ', $controller ) ) ) );		//  @todo to be removed
		$this->addBodyClass( 'controller-'.$controllerKey );
		$this->addBodyClass( 'action-'.$actionKey );
		$this->addBodyClass( 'site-'.$controllerKey.'-'.$actionKey );
		$this->addBodyClass( 'module'.$moduleKeyOld );

		$modules			= $this->env->getModules();												//  get installed modules
		$controllerClass	= str_replace( ' ', '_', ucwords( str_replace( '/', ' ', $controller ) ) );
		$module				= $modules->getModuleFromControllerClassName( $controllerClass );		//  try to get module of controller
		if( NULL !== $module ){																		//  module has been identified
			$moduleKey		= str_replace( '_', '', $module->id );
			$this->addBodyClass( 'module'.$moduleKey );
		}

		/*  --  BODY CLASSES  --  */
		$classes	= [];
		foreach( $this->bodyClasses as $class )
			$classes[]	= $class;
		if( isset( $bodyAttributes['class'] ) && strlen( trim( $bodyAttributes['class'] ) ) )
			foreach( explode( " ", trim( $bodyAttributes['class'] ) ) as $class )
				$classes[]	= $class;
		$bodyAttributes['class']	= join( ' ', $classes );
	}

	/**
	 *	@return		void
	 */
	protected function decorateStyles(): void
	{
		$headStyleBlocks	= ['primer', 'common', 'theme','lib'];

		if( $this->packStyleSheets && $this->env->getRequest()->has( 'flushStyleCache') )
			foreach( $headStyleBlocks as $headStyleBlockKey )
				$this->css->get( $headStyleBlockKey )->clearCache();

		foreach( $headStyleBlocks as $headStyleBlockKey ){
			$block	= $this->css->get( $headStyleBlockKey )->render( $this->packStyleSheets );
			if( '' !== trim( $block ) )
				$this->addHead( $block );
		}
	}
}
