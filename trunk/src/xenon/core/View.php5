<?php
/**
 *	Basic View Component.
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
 *	@package		framework.xenon.core
 *	@uses			Framework_Xenon_Core_Registry
 *	@uses			UI_HTML_Paging
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.6
 */
import( 'de.ceus-media.framework.xenon.core.Component' );
/**
 *	Basic View Component.
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@uses			Framework_Xenon_Core_Registry
 *	@uses			UI_HTML_Paging
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.6
 */
class Framework_Xenon_Core_View extends Framework_Xenon_Core_Component
{
	public static $titleMode	= "right";
	public static $baseUrl		= "index.php5";
	
	/**
	 *	Constructor, references Output Objects.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $useWikiParser = FALSE )
	{
		parent::__construct( $useWikiParser );
	}
	
	/**
	 *	Build and return Content Views, to be overwritten.
	 *	@access		public
	 *	@return		string
	 */
	public function buildContent()
	{
		return "";
	}
	
	/**
	 *	Build and return Control Views, to be overwritten.
	 *	@access		public
	 *	@return		string
	 */
	public function buildControl()
	{
		return "";
	}

	/**
	 *	Build and return Extra Views, to be overwritten.
	 *	@access		public
	 *	@return		string
	 */
	public function buildExtra()
	{
		return "";
	}

	/**
	 *	Builds HTML for Paging of Lists.
	 *	@access		public
	 *	@param		int			$numberTotal	Total mount of total entries
	 *	@param		int			$rowLimit		Number of displayed Rows
	 *	@param		int			$rowOffset		Number of of skipped Rows
	 *	@param		array		$optionMap		Array of Options to set
	 *	@return		string
	 */
	public function buildPaging( $numberTotal, $rowLimit, $rowOffset, $optionMap = array())
	{
		import( 'de.ceus-media.ui.html.Paging' );
		$request	= Framework_Xenon_Core_Registry::getStatic( "request" );
		$link		= $request->get( 'link');
		
		$paging			= new UI_HTML_Paging;
		$paging->setOption( 'uri', self::$baseUrl );
		$paging->setOption( 'param', array( 'link'	=> $link ) );
		$paging->setOption( 'indent', "" );

		foreach( $optionMap as $key => $value )
		{
			if( !$paging->hasOption( $key ) )
				throw new InvalidArgumentException( 'Option "'.$key.'" is not a valid Paging Option.' );
			$paging->setOption( $key, $value );
		}

		if( isset( $this->words['main']['paging'] ) )
		{
			$words		= $this->words['main']['paging'];
			if( isset( $words['first'] ) )
				$paging->setOption( 'text_first', $words['first'] );
			if( isset( $words['previous'] ) )
				$paging->setOption( 'text_previous', $words['previous'] );
			if( isset( $words['next'] ) )
				$paging->setOption( 'text_next', $words['next'] );
			if( isset( $words['last'] ) )
				$paging->setOption( 'text_last', $words['last'] );
			if( isset( $words['more'] ) )
				$paging->setOption( 'text_more', $words['more'] );
		}

		$pages	= $paging->build( $numberTotal, $rowLimit, $rowOffset );
		return $pages;
	}

	protected function getFlagIcon( $country, $label )
	{
		$fileName	= "ceus-media/flags/".$country.".png";
		$image		= $this->getIcon( $fileName, $label );
		return $image;
	}

	protected function getIcon( $fileName, $label, $configKey = 'paths.icons' )
	{
		$url		= $this->config[$configKey].$fileName;
		$image		= UI_HTML_Elements::Image( $url, $label );
		return $image;
	}

	/**
	 *	Sets a List of Keywords to Configuration for HTML Page.
	 *	@access		protected
	 *	@param		string		$description	Description String for HTML Meta Tags
	 *	@return		void
	 */
	protected function setDescription( $description )
	{
		$this->words['main']['meta']['description']		= $description;
		$this->words['main']['meta']['dc.Description']	= $description;
	}

	/**
	 *	Sets a List of Keywords to Configuration for HTML Page.
	 *	@access		protected
	 *	@param		mixed		$words			String or List of Keywords for HTML Meta Tags
	 *	@return		void
	 */
	protected function setKeywords( $words )
	{
		if( is_array( $words ) )
			$words	= implode( ",", $words );
		$this->words['main']['meta']['keywords']	= $words;
		$this->words['main']['meta']['dc.Subject']	= $words;
	}

	/**
	 *	Set the Title of HTML Page.
	 *	@access		protected
	 *	@param		string		$title			Title to set or add
	 *	@param		string		$separator		Separator if a Title is added
	 *	@return		void
	 */
	protected function setTitle( $title, $separator = NULL )
	{
		$words		= $this->words['main']['main'];
		$current	= $this->words['main']['main']['title'];

		$separator	= $separator ? $separator : " ".$words['separator']." ";

		if( self::$titleMode == "left" )
			$current	= $title.$separator.$current;
		else if( self::$titleMode == "right" )
			$current	= $current.$separator.$title;
		else
			$current	= $title;
		
		$this->words['main']['main']['title']		= $current;
		$this->words['main']['meta']['dc.Title']	= $current;
	}
}
?>