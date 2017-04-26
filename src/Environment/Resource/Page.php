<?php
/**
 *	XHTML Page Resource of Framework Hydrogen.
 *
 *	Copyright (c) 2010-2016 Christian Würker (ceusmedia.de)
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
 *	@copyright		2010-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	XHTML Page Resource of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
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
	/**	@var		CMM_TEA_Factory						$tea				Instance of TEA (Template Engine Abstraction) Factory (from cmModules) OR empty if TEA is not available */
	public $tea					= NULL;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$language	= 'en';
		$this->env	= $env;
		if( $this->env->has( 'language' ) )
			$language	= $this->env->getLanguage()->getLanguage();

		parent::__construct( 'XHTML_10_STRICT', $language );
		$this->js			= CMF_Hydrogen_View_Helper_JavaScript::getInstance();

		$path	= preg_replace( '/\/+$/', '', $env->config->get( 'path.themes' ) ).'/';
		$this->pathPrimer	= $path;
		if( $env->config->get( 'layout.primer' ) )
			$this->pathPrimer	= $path.$env->config->get( 'layout.primer' ).'/';
		$this->pathCommon	= $path.'common/';
		$this->pathTheme	= $path.$env->config->get( 'layout.theme' ).'/';
		$this->css			= new stdClass;
		$this->css->primer	= new CMF_Hydrogen_View_Helper_StyleSheet( $this->pathPrimer.'css/' );
		$this->css->common	= new CMF_Hydrogen_View_Helper_StyleSheet( $this->pathCommon.'css/' );
		$this->css->theme	= new CMF_Hydrogen_View_Helper_StyleSheet( $this->pathTheme.'css/' );
		if( $env->config->get( 'app.revision' ) ){
			$this->css->primer->setRevision( $env->config->get( 'app.revision' ) );
			$this->css->theme->setRevision( $env->config->get( 'app.revision' ) );
			$this->js->setRevision( $env->config->get( 'app.revision' ) );
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

	public function addCommonStyle( $fileName, $level = 'mid', $attributes = array() ){
		$this->css->common->addUrl( $fileName, $level, $attributes );
	}

	public function addPrimerStyle( $fileName, $level = 'mid', $attributes = array() ){
		$this->css->primer->addUrl( $fileName, $level, $attributes );
	}

	public function addThemeStyle( $fileName, $level = 'mid', $attributes = array() ){
		$this->css->theme->addUrl( $fileName, $level, $attributes );
	}

	/**
	 *	@todo		kriss: apply JS levels after CMF_Hydrogen_View_Helper_JavaScript is supporting it
	 */
	public function applyModules(){
		$config		= $this->env->getConfig()->getAll( 'module.', TRUE );							//  dictionary of (user modified) module settings
		$modules	= $this->env->getModules();														//  get module handler resource
		if( !$modules )																				//  module handler resource is not existing
			return;

		$pathScripts	= $this->env->config->get( 'path.scripts' );
		$pathScriptsLib	= $this->env->config->get( 'path.scripts.lib' );
		$pathStylesLib	= $this->env->config->get( 'path.styles.lib' );
		$listConfig		= array();
		$settings		= array();

		foreach( $modules->getAll() as $module ){													//  iterate installed modules
			$settings[$module->id]	= array(
//				'_id'		=> $module->id,
//				'_title'	=> $module->title,
//				'_version'	=> $module->version,
			);
			foreach( $module->files->styles as $style ){											//  iterate module style files
				if( !empty( $style->load ) && $style->load == "auto" ){								//  style file is to be loaded always
					$source	= !empty( $style->source ) ? $style->source : NULL;						//  get source attribute if possible
					$level	= !empty( $style->level ) ? $style->level : 'mid';						//  get load level (top, mid, end), default: mid
					if( preg_match( "/^[a-z]+:\/\/.+$/", $style->file ) )							//  style file is absolute URL
						$this->css->theme->addUrl( $style->file, $level );							//  add style file URL
					else if( $source == 'primer' )													//  style file is in primer theme
						$this->addPrimerStyle( $style->file, $level );								//  load style file from primer theme folder
					else if( $source == 'common' )													//  style file is in common theme
						$this->addCommonStyle( $style->file, $level );								//  load style file from common theme folder
					else if( $source == 'lib' ){													//  style file is in styles library, which is enabled by configured path
						if( !strlen( trim( $pathStylesLib ) ) )
							throw new RuntimeException( 'Path to style library "path.styles.lib" is not configured' );
						$this->css->primer->addUrl( $pathStylesLib.$style->file, $level );			//  load style file from styles library
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
			foreach( $module->files->scripts as $script ){											//  iterate module script files
				if( !empty( $script->load ) && $script->load == "auto" ){							//  script file is to be loaded always
					$source	= empty( $script->source ) ? 'local' : $script->source;
					$level	= !empty( $script->level ) ? $script->level : 'mid';					//  get load level (top, mid, end, ready), default: mid
					$top	= !empty( $script->top ) || $level === "top";							//  get flag attribute for appending on top
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
			foreach( $module->config as $pair ){													//  iterate module configuration pairs
				if( !empty( $pair->protected ) && $pair->protected !== 'yes' ){
					$value	= $config->get( strtolower( $module->id ).'.'.$pair->key );				//  get (user modified) module setting value
					$settings[$module->id][str_replace( '.', '_', $pair->key )]	= $value;			//  note module setting
					$key	= 'module.'.strtolower( $module->id ).'.'.$pair->key;					//  @deprecated see line below
					$listConfig[str_replace( '.', '_', $key )]	 = $pair->value;					//  @deprecated in favour of settings
				}
			}
			if( !$settings[$module->id] )
				unset( $settings[$module->id] );
		}
		$modules->callHook( 'Page', 'applyModules', $this );										//  call related module event hooks
//		$script		= 'var config = '.json_encode( $listConfig ).';';								//  @deprecated leave only next line
		$script		= 'var settings = '.json_encode( $settings ).';';
		$script		= UI_HTML_Tag::create( 'script', "<!--\n".$script."\n-->", array( 'type' => "text/javascript" ) );
		$this->addHead( $script );
	}

	public function build( $bodyAttributes = array(), $htmlAttributes = array() ){
		$controller			= $this->env->getRequest()->get( 'controller' );
		$action				= $this->env->getRequest()->get( 'action' );
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

		if( ( $modules = $this->env->getModules() ) ){												//  get module handler resource if existin
			$data	= (object) array(
				'content'   => $this->getBody(),
			);
			$modules->callHook( 'Page', 'build', $this, $data );									//  call related module event hooks
			$this->setBody( $data->content );
		}

		if( $this->packStyleSheets && $this->env->getRequest()->has( 'flushStyleCache') ){
			$this->css->primer->clearCache();
			$this->css->common->clearCache();
			$this->css->theme->clearCache();
		}

		$this->addHead( $this->css->primer->render( $this->packStyleSheets ) );
		$this->addHead( $this->css->common->render( $this->packStyleSheets ) );
		$this->addHead( $this->css->theme->render( $this->packStyleSheets ) );
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
	 *	Returns path to theme or primer.
	 *	@access		public
	 *	@param		boolean		$primer			Flag: return path to primer instead of theme
	 *	@return		string						Path to theme or primer
	 */
	public function getThemePath( $primer = FALSE ){
		return $primer ? $this->pathPrimer : $this->pathTheme;
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
	 *	Appends JavaScript code to be run after Browser finished rendering (document.ready).
	 *	@access		public
	 *	@param		string		$script			JavaScript code to execute on ready
	 *	@param		integer		$runlevel		Run order level of JavaScript code, default: 5, less: earlier, more: later
	 *	@return		void
	 */
	public function runScript( $script, $runlevel = 5 ){
		return $this->js->addScriptOnReady( $script, $runlevel );
	}

	/**
	 *	Deprecated.
	 *	@param type $packJavaScripts
	 *	@param type $packStyleSheets
	 *	@deprecated		will be removed
	 *	@todo			step 1: enable messenger note and let apps adjust
	 *	@todo			step 2: remove method
	 */
	public function setPackaging( $packJavaScripts = FALSE, $packStyleSheets = FALSE ){
//		$this->env->getMessenger()->noteNotice( '<b>Deprecation: </b>Calling Page::setPackaging is deprecated. Please use module UI:Compressor instead.' );

#		$this->js->setCompression( $packJavaScripts );
#		$this->css->primer->setCompression( $packStyleSheets );
#		$this->css->theme->setCompression( $packStyleSheets );
		$this->packJavaScripts	= $packJavaScripts;
		$this->packStyleSheets	= $packStyleSheets;
	}
}
?>
