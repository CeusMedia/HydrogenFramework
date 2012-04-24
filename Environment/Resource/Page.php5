<?php
/**
 *	XHTML Page Resource of Framework Hydrogen.
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
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	XHTML Page Resource of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Page extends UI_HTML_PageFrame
{
	protected $packJavaScripts	= FALSE;
	protected $packStyleSheets	= FALSE;
	protected $pathPrimer;
	protected $pathTheme;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$language	= 'en';
		$this->env	= $env;
		if( $this->env->has( 'language' ) )
			$language	= $this->env->getLanguage()->getLanguage();
		
		parent::__construct( $language );
		$this->js	= CMF_Hydrogen_View_Helper_JavaScript::getInstance();
		$this->css			= new stdClass;
		$this->css->primer	= new CMF_Hydrogen_View_Helper_StyleSheet;
		$this->css->theme	= new CMF_Hydrogen_View_Helper_StyleSheet;

		$path	= $env->config->get( 'path.themes' );
		if( $env->config->get( 'layout.primer' ) )
			$this->pathPrimer	= $path.$env->config->get( 'layout.primer' ).'/';
		$this->pathTheme	= $path.$env->config->get( 'layout.theme' ).'/';

		$pathScripts	= $env->config->get( 'path.scripts' );
		$pathScriptsLib	= $env->config->get( 'path.scripts.lib' );
		$pathStylesLib	= $env->config->get( 'path.styles.lib' );

		
		$modules	= $this->env->getModules();														//  get module handler resource
		if( !$modules )																				//  module handler resource is not existing
			return;

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
					$source	= !empty( $script->source ) ? $script->source : NULL;					//  get source attribute if possible
					$top	= !empty( $script->top );												//  get flag attribute for appending on top
					if( preg_match( "/^[a-z]+:\/\/.+$/", $script->file ) )							//  script file is absolute URL
						$this->js->addUrl( $script->file, $source );								//  add script file URL
					else if( !empty( $script->source ) && $script->source == 'lib' )				//  script file is in script library
						$this->js->addUrl( $pathScriptsLib.$script->file, $top );					//  load script file from script library
					else																			//  script file is in app scripts folder
						$this->js->addUrl( $pathScripts.$script->file, $top );						//  load script file from app scripts folder
				}
			}
		}
	}

	public function addPrimerStyle( $fileName, $onTop = FALSE ){
		$this->css->primer->addUrl( $this->pathPrimer.'css/'.$fileName, $onTop );
	}

	public function addThemeStyle( $fileName ){
		$this->css->theme->addUrl( $this->pathTheme.'css/'.$fileName );
	}

	public function build( $bodyAttributes = array() )
	{
		$this->addHead( $this->css->primer->render( $this->packStyleSheets ) );
		$this->addHead( $this->css->theme->render( $this->packStyleSheets ) );
		$this->addBody( $this->js->render( $this->packJavaScripts ) );
		
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
		return parent::build( $bodyAttributes );
	}

	public function setPackaging( $packJavaScripts = FALSE, $packStyleSheets = FALSE )
	{
		$this->packJavaScripts	= $packJavaScripts;
		$this->packStyleSheets	= $packStyleSheets;
	}
}
?>
