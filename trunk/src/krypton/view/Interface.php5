<?php
/**
 *	Basic User Interface Views, to be extended with UI Blocks.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.krypton.view
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			22.12.2008
 *	@version		0.1
 */
import( 'de.ceus-media.framework.krypton.core.View' );
/**
 *	Basic User Interface Views, to be extended with UI Blocks.
 *	@category		cmClasses
 *	@package		framework.krypton.view
 *	@extends		Framework_Krypton_Core_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			22.12.2008
 *	@version		0.1
 */
class Framework_Krypton_View_Interface extends Framework_Krypton_Core_View
{
	/** @var	array		$ui			User Interface Data */
	protected $ui	= array();

	/**
	 *	Builds Header View.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildHeader()
	{
		$words		= $this->registry->get( "words" );
		return $this->loadTemplate( 'interface.header', $words['main']['header'] );
	}

	/**
	 *	Builds Dev Center View.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildDevCenter( $content )
	{
		import( 'de.ceus-media.framework.krypton.view.component.DevCenter' );
		$view	= new Framework_Krypton_View_Component_DevCenter();
		return $view->buildContent( $content );
	}

	/**
	 *	Builds complete User Interface as HTML Page by calling Interface Components and adding build Contents.
	 *	@access		public
	 *	@param		string			$content		Page Content Area
	 *	@param		string			$control		Page Control Area
	 *	@param		string			$extra			Page Extra Area
	 *	@param		string			$dev			Remarks on catched for Developement
	 *	@return		string
	 */
	public function buildInterface( $content, $control = "", $extra = "", $dev = "" )
	{
		ob_start();
		$uiData		= $this->getUserInterfaceData();
		$uiParts	= array(
			'title'			=> $this->words['main']['main']['title'],
			'messages'		=> $this->messenger->buildMessages(),
			'control'		=> $control,
			'content'		=> $content,
			'extra'			=> $extra,
			'metatags'		=> $this->buildMetaTags(),
			'header'		=> $this->buildHeader(),
			'navigation'	=> $this->buildNavigation(),
			'mainfooter'	=> $this->buildMainFooter(),
			'subfooter'		=> $this->buildSubFooter(),
			'languages'		=> $this->buildLanguageSwitch(),
			'themes'		=> $this->buildThemeSwitch(),
			'styles'		=> $this->buildStyleLinks(),
			'scripts'		=> $this->buildScriptLinks(),
			'noscript'		=> $this->buildNoScript(),
			'dev'			=> $this->buildDevCenter( $dev.ob_get_clean() ),
		);
		$ui			= array_merge( $uiData, $uiParts );
		$content	= $this->loadTemplate( 'interface.master', $ui );
		$content	= str_replace( "[[%", "&lt;%", $content );
		$content	= str_replace( "%]]", "%&gt;", $content );
		return $content;
	}

	/**
	 *	Builds Sub Header View.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildLanguageSwitch()
	{
		$config		= $this->registry->get( 'config' );
		$request	= $this->registry->get( 'request' );
		$language	= $this->registry->get( 'language' );
		$words		= $this->words['main']['switchLanguage'];

		$allowed	= $language->getAllowedLanguages();
		$current	= $language->getLanguage();

		if( count( $allowed ) < 2 )
			return "";
		$list		= array();
		foreach( $allowed as $languageKey )
		{
			$label	= $this->words['main']['languages'][$languageKey];
			$icon	= $this->getFlagIcon( $languageKey, $label );
			if( $languageKey != $current )
				$icon	= UI_HTML_Elements::Link( "?link=".$request->get( 'link' )."&switchLanguageTo=".$languageKey, $icon );
			if( $languageKey == $current )
				continue;
			$list[]	= UI_HTML_Elements::ListItem( $icon );
		}
		$ui['list']		= UI_HTML_Elements::unorderedList( $list );
		$ui['heading']	= $words['heading'];
		$ui['caption']	= UI_HTML_Elements::TableCaption( $words['caption'], 'list' );
		return $this->loadTemplate( 'interface.languages', $ui );
	}

	/**
	 *	Builds Main Footer View.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildMainFooter()
	{
		$request	= $this->registry->get( 'request' );
		$controller	= $this->registry->get( 'controller' );

		$pages		= $controller->getPages( "foot" );
		$list		= array();
		foreach( $pages as $page )
		{
			if( $page['hidden'] || $page['disabled'] )
				continue;
			$label	= $this->words['main']['links_footer'][$page['id']];
			$link	= $this->html->Link( "?link=".$page['id'], $label );
			$list[]	= $this->html->ListItem( $link );
		}
		$list	= $this->html->unorderedList( $list );

		$ui	= array(
			'link'		=> $request->get( 'link' ),
			'list'		=> $list,
			);
		$uiData		= $this->getUserInterfaceData();
		$ui			= array_merge( $ui, $uiData );
		return $this->loadTemplate( "interface.mainfooter", $ui );
	}
	/**
	 *	Builds MetaTag Component for HTML Page using Meta Information from Main Language File, Section 'meta'.
	 *  Also builds automated LogOut if Options are set.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildMetaTags()
	{
		$config		= $this->registry->get( 'config' );
		$auth		= $this->registry->get( 'auth' );
		$words		= $this->registry->get( "words" );
		$ui			= $this->getUserInterfaceData();
		
		$ui['meta']		= $config['metatags'];
		foreach( $words['main']['meta'] as $key => $value )
			if( $value )
				$ui['meta'][$key]	= $value;
		$ui['date']		= date( "c" );
		$ui['referrer']	= getEnv( "HTTP_REFERER" );

		return $this->loadTemplate( 'interface.metatags', $ui );
	}

	/**
	 *	Builds Navigation View.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildNavigation()
	{
		import( 'de.ceus-media.framework.krypton.view.component.Navigation' );
		$navigation	= new Framework_Krypton_View_Component_Navigation();
		return $navigation->buildNavigation();
	}

	/**
	 *	Builds No Script Screen.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildNoScript()
	{
		return $this->loadTemplate( 'interface.noscript' );	
	}

	/**
	 *	Builds JavaScript Link Component.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildScriptLinks()
	{
		return $this->loadTemplate( 'interface.scripts', $this->getUserInterfaceData() );
	}

	/**
	 *	Builds StyleSheet Link Component.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildStyleLinks()
	{
		return $this->loadTemplate( 'interface.styles', $this->getUserInterfaceData() );
	}
	
	/**
	 *	Builds Sub Footer View.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildSubFooter()
	{
		$request	= $this->registry->get( 'request' );
		$stopwatch	= $this->registry->get( 'stopwatch' );
		$dbc		= $this->registry->get( 'dbc' );
		$words		= $this->words['main']['footer'];

		$ui			= $this->getUserInterfaceData();
		$ui['link']				= $request->get( 'link' );
		$ui['time']				= $stopwatch->stop( 0, 3 )."s";
		$ui['db_executes']		= $dbc->numberExecutes;
		$ui['db_statements']	= $dbc->numberStatements;
		$ui['words']			= $words;
		return $this->loadTemplate( "interface.footer", $ui );
	}
	
	/**
	 *	Builds Theme Switch.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildThemeSwitch()
	{
		$config		= $this->registry->get( 'config' );
		$request	= $this->registry->get( 'request' );
		$language	= $this->registry->get( 'language' );
		$words		= $this->words['main']['switchTheme'];

		$current	= $config['layout.theme'];
		$list		= array();
		if( !file_exists( $config['paths.themes'] ) )
			throw new RuntimeException( 'Theme path "'.$config['paths.themes'].'" is not existing.' );
		$dir		= new DirectoryIterator( $config['paths.themes'] );
		foreach( $dir as $entry )
		{
			$theme	= $entry->getFilename();
			if( !( !$entry->isDot() && substr( $theme, 0, 1 ) != "." ) )
				continue;
			$item	= $theme;
			if( $current != $theme )
				$item	= UI_HTML_Elements::Link( "?link=".$request->get( 'link' )."&switchThemeTo=".$theme, $theme );
			$list[]	= UI_HTML_Elements::ListItem( $item );
		}
		if( count( $list ) < 2 )
			return "";
		$ui['list']		= UI_HTML_Elements::unorderedList( $list );
		$ui['heading']	= $words['heading'];
		$ui['caption']	= UI_HTML_Elements::TableCaption( $words['caption'], 'list' );
		return $this->loadTemplate( 'interface.themes', $ui );
	}

	/**
	 *	Builds and returns all Data for User Interface Templates.
	 *	@access		protected
	 *	@return		array
	 */
	protected function getUserInterfaceData()
	{
		if( !$this->ui )
		{
			$config		= $this->registry->get( "config" );
			$session	= $this->registry->get( "session" );
			$language	= $this->registry->get( "language" );

			$cssCompressionSuffix	= "";
			if( isset( $config['compression'][$config['layout.theme']] ) )
				if( $config['compression'][$config['layout.theme']] )
					$cssCompressionSuffix	= $config['layout.compression'];

/*			$this->ui['layout']				= $config['layout.style'];*/
			$this->ui['path_js']			= $config['paths.javascripts'];
			$this->ui['path_js_lib']		= max( $config['paths.javascript_library'], $config['paths.javascript.library'] );
			$this->ui['path_css']			= $config['paths.themes'].$config['layout.theme']."/css/";
			$this->ui['css_compression']	= $cssCompressionSuffix;
			$this->ui['language']			= $language->getLanguage();
			$this->ui["config"]				= $config->getAll();
		}
		return $this->ui;
	}
	
	public function setDescriptionByLink( $link )
	{
		if( isset( $this->words['main']['descriptions'][$link] ) )
			$this->setDescription( $this->words['main']['descriptions'][$link] );
	}

	public function setTitleByLink( $link )
	{
		if( isset( $this->words['main']['titles'][$link] ) )
			$this->setTitle( $this->words['main']['titles'][$link] );
	}
	
	public function setKeywordsByLink( $link )
	{
		if( isset( $this->words['main']['keywords'][$link] ) )
			$this->setKeywords( $this->words['main']['keywords'][$link] );
	}
}
?>