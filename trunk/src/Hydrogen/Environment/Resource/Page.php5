<?php
/**
 *	XHTML Page Resource of Framework Hydrogen.
 *
 *	Copyright (c) 2010-2012 Christian Würker (ceusmedia.com)
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
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	XHTML Page Resource of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Page extends UI_HTML_PageFrame
{
	/**	@var	CMF_Hydrogen_Environment_Abstract		$env				Environment object */
	public $env;
	protected $bodyClasses		= array();
	protected $packJavaScripts	= FALSE;
	protected $packStyleSheets	= FALSE;
	protected $pathPrimer;
	protected $pathTheme;
	/**	@var	CMF_Hydrogen_View_Helper_JavaScript		$js					JavaScript Collector Helper */
	public $js;
	/**	@var	stdClass								$css				CSS containers (primer, theme) */
	public $css;
	/**	@var	array									$scriptsOnReady		List if JavaScripts to run on load if browser is ready */
	protected $scriptsOnReady	= array();
	/**	@var		CMM_TEA_Factory						$tea				Instance of TEA (Template Engine Abstraction) Factory (from cmModules) OR empty if TEA is not available */
	public $tea					= NULL;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$language	= 'en';
		$this->env	= $env;
		if( $this->env->has( 'language' ) )
			$language	= $this->env->getLanguage()->getLanguage();

		parent::__construct( 'XHTML_10_STRICT', $language );
		$this->js			= CMF_Hydrogen_View_Helper_JavaScript::getInstance();

		$path	= $env->config->get( 'path.themes' );
		if( $env->config->get( 'layout.primer' ) )
			$this->pathPrimer	= $path.$env->config->get( 'layout.primer' ).'/';
		$this->pathTheme	= $path.$env->config->get( 'layout.theme' ).'/';
		$this->css			= new stdClass;
		$this->css->primer	= new CMF_Hydrogen_View_Helper_StyleSheet( $this->pathPrimer.'css/' );
		$this->css->theme	= new CMF_Hydrogen_View_Helper_StyleSheet( $this->pathTheme.'css/' );
		if( $env->config->get( 'app.revision' ) ){
			$this->css->primer->setRevision( $env->config->get( 'app.revision' ) );
			$this->css->theme->setRevision( $env->config->get( 'app.revision' ) );
		}

		
		if( strlen( $title	= $env->config->get( 'app.name' ) ) )
			$this->setTitle( $title );
		if( ( $modules = $this->env->getModules() ) )												//  get module handler resource if existing
			$modules->callHook( 'Page', 'init', $this );											//  call related module event hooks
//		$this->applyModules();																		//  @todo kriss: remove, is called by environment now
	}

	public function addBodyClass( $class ){
		if( strlen( trim( $class ) ) )
			$this->bodyClasses[]	= trim( htmlentities( $class, ENT_QUOTES, 'UTF-8' ) );
	}

	public function addPrimerStyle( $fileName, $onTop = FALSE ){
		$this->css->primer->addUrl( $fileName, $onTop );
	}

	public function addThemeStyle( $fileName ){
		$this->css->theme->addUrl( $fileName );
	}

	public function applyModules(){
		$modules	= $this->env->getModules();														//  get module handler resource
		if( !$modules )																				//  module handler resource is not existing
			return;

		$pathScripts	= $this->env->config->get( 'path.scripts' );
		$pathScriptsLib	= $this->env->config->get( 'path.scripts.lib' );
		$pathStylesLib	= $this->env->config->get( 'path.styles.lib' );
		$listConfig		= array();
		$settings		= array();

		foreach( $modules->getAll() as $module ){													//  iterate installed modules
			$settings[$module->id]	= array();
			foreach( $module->files->styles as $style ){											//  iterate module style files
				if( !empty( $style->load ) && $style->load == "auto" ){								//  style file is to be loaded always
					$source	= !empty( $style->source ) ? $style->source : NULL;						//  get source attribute if possible
					$top	= !empty( $style->top );												//  get flag attribute for appending on top
					if( preg_match( "/^[a-z]+:\/\/.+$/", $style->file ) )							//  style file is absolute URL
						$this->css->theme->addUrl( $style->file, $top );							//  add style file URL
					else if( $source == 'primer' )													//  style file is in primer theme
						$this->addPrimerStyle( $style->file, $top );								//  load style file from primer theme folder
					else if( $source == 'lib' ){													//  style file is in styles library, which is enabled by configured path
						if( !strlen( trim( $pathStylesLib ) ) )
							throw new RuntimeException( 'Path to style library "path.styles.lib" is not configured' );
						$this->css->primer->addUrl( $pathStylesLib.$style->file, $top );			//  load style file from styles library
					}
					else if( $source == 'scripts-lib' && $pathScriptsLib ){							//  style file is in scripts library, which is enabled by configured path
						if( !strlen( trim( $pathScriptsLib ) ) )
							throw new RuntimeException( 'Path to script library "path.scripts.lib" is not configured' );
						$this->css->primer->addUrl( $pathScriptsLib.$style->file, $top );			//  load style file from scripts library
					}
					else if( $source == 'theme' || !$source )										//  style file is in custom theme
						$this->addThemeStyle( $style->file );										//  load style file from custom theme folder
					else																			//  style file is in an individual source folder within themes folder
						$this->css->primer->addUrl( $path.$source.'/'.$style->file );				//  load style file from source folder within themes folder
				}
			}
			foreach( $module->files->scripts as $script ){											//  iterate module script files
				if( !empty( $script->load ) && $script->load == "auto" ){							//  script file is to be loaded always
					$source	= empty( $script->source ) ? 'local' : $script->source;
					$top	= !empty( $script->top );												//  get flag attribute for appending on top
					if( $source == 'lib' ){															//  script file is in script library
						if( $top )																	//  
							$this->addJavaScript( $pathScriptsLib.$script->file );					//  
						else																		//  
							$this->js->addUrl( $pathScriptsLib.$script->file, $top );				//  load script file from script library
					}
					else if( $source == 'local' ){													//  script file is in app scripts folder
						if( $top )																	//  
							$this->addJavaScript( $pathScripts.$script->file );						//	
						else																		//  
							$this->js->addUrl( $pathScripts.$script->file, $top );					//  load script file from app scripts folder
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
			foreach( $module->config as $pair ){													//  iterate module configuration pairs
				if( !empty( $pair->protected ) && $pair->protected !== 'yes' ){
					$key	= 'module.'.strtolower( $module->id ).'.'.$pair->key;
					$key	= str_replace( '.', '_', $key );
					$listConfig[$key]	 = $pair->value;											//  @deprecated in favour of next line
					$settings[$module->id][str_replace( '.', '_', $pair->key )]	= $pair->value;
				}
			}
			if( !$settings[$module->id] )
				unset( $settings[$module->id] );
		}
		$modules->callHook( 'Page', 'applyModules', $this );										//  call related module event hooks
		$script		= 'var config = '.json_encode( $listConfig ).';';								//  @deprecated leave only next line
		$script		.= 'var settings = '.json_encode( $settings ).';';
		$script		= UI_HTML_Tag::create( 'script', "<!--\n".$script."\n-->", array( 'type' => "text/javascript" ) );
		$this->addHead( $script );
	}

	public function build( $bodyAttributes = array(), $htmlAttributes = array() ){
		$controller	= str_replace( '/', '-', $this->env->getRequest()->get( 'controller' ) );
		$action		= str_replace( '/', '-', $this->env->getRequest()->get( 'action' ) );
		$moduleKey	= join( explode( ' ', ucwords( str_replace( '-', ' ', $controller ) ) ) );
		$this->addBodyClass( 'module'.$moduleKey );
		$this->addBodyClass( 'controller-'.$controller );
		$this->addBodyClass( 'action-'.$action );
		$this->addBodyClass( 'site-'.$controller.'-'.$action );

		if( ( $modules = $this->env->getModules() ) )												//  get module handler resource if existing
			$modules->callHook( 'Page', 'build', $this );											//  call related module event hooks

		if( $this->packStyleSheets && $this->env->getRequest()->has( 'flushStyleCache') ){
			$this->css->primer->clearCache();
			$this->css->theme->clearCache();
		}

		$this->addHead( $this->css->primer->render( $this->packStyleSheets ) );
		$this->addHead( $this->css->theme->render( $this->packStyleSheets ) );

		if( $this->scriptsOnReady )																	//  JavaScripts to call on start have been collected
			$this->js->addScript( $this->renderScriptsOnReady() );									//  append collected onReady-JavaScripts to page

		$this->addBody( $this->js->render() );

		/*  --  BODY CLASSES  --  */
		$classes	= array();
		foreach( $this->bodyClasses as $class )
			$classes[]	= $class;
		if( isset( $bodyAttributes['class'] ) && strlen( trim( $bodyAttributes['class'] ) ) )
			foreach( explode( " ", trim( $bodyAttributes['class'] ) ) as $class )
				$classes[]	= $class;
		$bodyAttributes['class']	= join( ' ', $classes );
#		if( empty( $bodyAttributes['id'] ) )
#			$bodyAttributes['id']	= 
		if( ( $modules = $this->env->getModules() ) )												//  get module handler resource if existing
			$modules->callHook( 'App', 'respond', $this );											//  call related module event hooks
		return parent::build( $bodyAttributes, $htmlAttributes );
	}

	/**
	 *	Notes to load a JavaScript in local scripts folder.
	 *	@access		public
	 *	@param		string		$filePath		Script file path within scripts folder
	 *	@return		void
	 *	@throws		RuntimeException			if script file is not existint
	 */
	public function loadLocalScript( $filePath ){
		$path	= $this->env->getConfig()->get( 'path.scripts' );
		if( !file_exists( $path.$filePath ) )
			throw new RuntimeException( 'Local script "'.$filePath.'" not found in folder "'.$path.'"' );
		$this->js->addUrl( $path.$filePath );
	}

	/**
	 *	Inserts collected JavaScript code into page bottom with directive to run if Browser finished loading (using jQuery event document.ready).
	 *	@access		protected
	 *	@param		boolean		$compress		Flag: compress code
	 *	@param		boolean		$wrapInTag		Flag: wrap code in HTML script tag
	 *	@return		string		Combinded JavaScript code to run if Browser is ready
	 */
	protected function renderScriptsOnReady( $compress = FALSE, $wrapInTag = FALSE ){
		$list	= array();
		foreach( $this->scriptsOnReady as $level => $scripts )
			foreach( $scripts as $script )
				$list[]	= preg_replace( "/;?$/", ";", trim( $script ) );
		$list	= join( "\n\t", $list );
		$script		= "$(document).ready(function(){\n\t".$list."\n});";
		if( !$wrapInTag )
			return $script;
		return UI_HTML_Tag::create( 'script', $script, array( 'type' => 'text/javascript' ) );
	}

	/**
	 *	Appends JavaScript code to be run after Browser finished rendering (document.ready).
	 *	@access		public
	 *	@param		string		$script			JavaScript code to execute on ready
	 *	@param		integer		$runlevel		Run order level of JavaScript code, default: 5, less: earlier, more: later
	 *	@return		void
	 */
	public function runScript( $script, $runlevel = 5 ){
		if( !isset( $this->scriptsOnReady[(int) $runlevel] ) )										//  runlevel is not yet defined in scripts list
			$this->scriptsOnReady[(int) $runlevel]	= array();										//  create empty scripts list for runlevel
		$this->scriptsOnReady[(int) $runlevel][]	= $script;										//  note JavaScript code on runlevel
	}

	public function setPackaging( $packJavaScripts = FALSE, $packStyleSheets = FALSE ){
#		$this->js->setCompression( $packJavaScripts );
#		$this->css->primer->setCompression( $packStyleSheets );
#		$this->css->theme->setCompression( $packStyleSheets );
		$this->packJavaScripts	= $packJavaScripts;
		$this->packStyleSheets	= $packStyleSheets;
	}
}
?>
