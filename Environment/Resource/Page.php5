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

		parent::__construct( $language );
		$this->js			= CMF_Hydrogen_View_Helper_JavaScript::getInstance();
		$this->css			= new stdClass;
		$this->css->primer	= new CMF_Hydrogen_View_Helper_StyleSheet();
		$this->css->theme	= new CMF_Hydrogen_View_Helper_StyleSheet();

		$path	= $env->config->get( 'path.themes' );
		if( $env->config->get( 'layout.primer' ) )
			$this->pathPrimer	= $path.$env->config->get( 'layout.primer' ).'/';
		$this->pathTheme	= $path.$env->config->get( 'layout.theme' ).'/';
		if( strlen( $title	= $env->config->get( 'app.name' ) ) )
			$this->setTitle( $title );

		$this->applyModules();
	}

	protected function applyModules(){
		$modules	= $this->env->getModules();														//  get module handler resource
		if( !$modules )																				//  module handler resource is not existing
			return;

		$pathScripts	= $this->env->config->get( 'path.scripts' );
		$pathScriptsLib	= $this->env->config->get( 'path.scripts.lib' );
		$pathStylesLib	= $this->env->config->get( 'path.styles.lib' );
		$listConfig		= array();

		foreach( $modules->getAll() as $module ){													//  iterate installed modules
			foreach( $module->files->styles as $style ){											//  iterate module style files
				if( !empty( $style->load ) && $style->load == "auto" ){								//  style file is to be loaded always
					$source	= !empty( $style->source ) ? $style->source : NULL;						//  get source attribute if possible
					$top	= !empty( $style->top );												//  get flag attribute for appending on top
					if( preg_match( "/^[a-z]+:\/\/.+$/", $style->file ) )							//  style file is absolute URL
						$this->css->theme->addUrl( $style->file, $top );							//  add style file URL
					else if( $source == 'primer' )													//  style file is in primer theme
						$this->addPrimerStyle( $style->file, $top );								//  load style file from primer theme folder
					else if( $source == 'lib' && $pathStylesLib )									//  style file is in styles library, which is enabled by configured path
						$this->css->primer->addUrl( $pathStylesLib.$style->file, $top );			//  load style file from styles library
					else if( $source == 'scripts-lib' && $pathScriptsLib )							//  style file is in scripts library, which is enabled by configured path
						$this->css->primer->addUrl( $pathScriptsLib.$style->file, $top );			//  load style file from scripts library
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
				if( empty( $pair->protected ) || $pair->protected == 'no' ){
					$key	= 'module.'.strtolower( $module->id ).'.'.$pair->key;
					$key	= str_replace( '.', '_', $key );
					$listConfig[$key]	 = $pair->value;
				}
			}
		}
		$modules->callHook( 'Page', 'applyModules', $this );										//  call related module event hooks
		$this->addHead( '<script type="text/javascript">var config = '.json_encode( $listConfig ).';</script>' );
	}

	public function addPrimerStyle( $fileName, $onTop = FALSE ){
		$this->css->primer->addUrl( $this->pathPrimer.'css/'.$fileName, $onTop );
	}

	public function addThemeStyle( $fileName ){
		$this->css->theme->addUrl( $this->pathTheme.'css/'.$fileName );
	}

	public function build( $bodyAttributes = array() ){
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

		$controller	= str_replace( '/', '-', $this->env->getRequest()->get( 'controller' ) );
		$action		= str_replace( '/', '-', $this->env->getRequest()->get( 'action' ) );

		$classes	= isset( $bodyAttributes['class'] ) ? $bodyAttributes['class'] : NULL;
		$classes	= strlen( trim( $classes ) ) ? explode( ' ', $classes) : array();
		$classes[]	= 'module'.join( explode( ' ', ucwords( str_replace( '-', ' ', $controller ) ) ) );
		$classes[]	= 'controller-'.$controller;
		$classes[]	= 'action-'.$action;
		$classes[]	= 'site-'.$controller.'-'.$action;
		$bodyAttributes['class']	= join( ' ', $classes );
#		if( empty( $bodyAttributes['id'] ) )
#			$bodyAttributes['id']	= 
		if( ( $modules = $this->env->getModules() ) )												//  get module handler resource if existing
			$modules->callHook( 'App', 'respond', $this );											//  call related module event hooks
		return parent::build( $bodyAttributes );
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
		$this->packJavaScripts	= $packJavaScripts;
		$this->packStyleSheets	= $packStyleSheets;
	}
}
?>
