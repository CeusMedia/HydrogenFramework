<?php
/**
 *	...
 *
 *	Copyright (c) 2007-2022 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.View.Helper.Navigation
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper\Navigation;

use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction as AbstractViewHelper;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper.Navigation
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code doc
 *	@deprecated		use modules Info_Pages + UI_Navigation instead
 *	@todo			remove in version 0.9
 */
class SingleList extends AbstractViewHelper
{
	protected $linkMap;
	protected $innerClass			= 'single';
	protected $innerId				= 'navigation-inner';
	protected $needsEnv				= FALSE;
	protected $linksToSkip			= array();
	public static $pathRequestKey	= "__path";

	public function __construct( array $linkMap, string $innerClass = NULL, string $innerId = NULL )
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.5' )
			->setExceptionVersion( '0.9' )
			->message( 'Use modules Info_Pages + UI_Navigation instead' );
		$this->linkMap		= $linkMap;
		if( $innerClass )
			$this->innerClass	= $innerClass;
		if( $innerId )
			$this->innerId		= $innerId;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		array		$linkMap		Map of links paths and labels
	 *	@param		string		$current		Currently requested path, autodetected if not set
	 *	@todo		correct implementation: rank by depth, not length, see todo below
	 *	@return		string|NULL
	 */
	public static function getCurrentKey( array $linkMap, string $current = NULL ): ?string
	{
		$path		= $current;
		if( isset( $_REQUEST[self::$pathRequestKey] ) && $current === NULL )
			$path	= utf8_decode( $_REQUEST[self::$pathRequestKey] );
		$matches	= array();																		//  empty array to regular matching
		$selected	= array();																		//  list of possibly selected links
		foreach( $linkMap as $key => $label ){														//  iterate link map
			$regExp	= '';																			//  prepare empty regular expression
			$parts	= explode( '/', $path );														//  split currently requested path into parts
			while( count( $parts ) ){																//  iterate parts
				$part = array_pop( $parts );														//  backwards
				$regExp	= count( $parts ) ? '(/'.$part.$regExp.')?' : '('.$part.$regExp.')';		//  insert part into regular expression
			}
			preg_match_all( '@^'.str_replace( '@', '\@', $regExp ).'@', $key, $matches );			//  match expression against link path
			if( isset( $matches[0] ) && !empty( $matches[0] ) )										//  found something
				$selected[$matches[0][0]]	= strlen( $matches[0][0] );								//  note link path and its length @todo WRONG! note DEPTH, not length
		}
		arsort( $selected );																		//  sort link paths by its length, longest on top
		$selected	= array_keys( $selected );
		$selected	= array_shift( $selected );
		return $selected;												//  return longest link path
	}

	public function render( string $current = NULL, bool $niceUrls = FALSE ): string
	{
		$path	= empty( $_REQUEST[self::$pathRequestKey] ) ? $current : $_REQUEST[self::$pathRequestKey];
		$active	= $this->getCurrentKey( $this->linkMap, $current );
		$list	= array();
		$class	= NULL;
		foreach( $this->linkMap as $key => $label )
		{
			if( in_array( $key, $this->linksToSkip ) )
				continue;
			$class		= $active == $key ? 'active' : NULL;
			$url		= $key == "index" ? "./" : ( $niceUrls ? './'.$key : './?controller='.$key );
			$link		= HtmlElements::Link( $url, $label, $class );
			$list[]		= HtmlElements::ListItem( $link, 0, array( 'class' => $class ) );
		}
		if( !$list )
			return $list;
		$attr	= array( 'class' => $class );
		$list	= HtmlElements::unorderedList( $list, 0, $attr );
		$attr	= array(
			'id'	=> $this->innerId,
			'class'	=> $this->innerClass
		);
		return HtmlTag::create( 'div', $list, $attr );
	}

	public function setInnerClass( string $class ): self
	{
		$this->innerClass	= $class;
		return $this;
	}

	public function setInnerId( string $id ): self
	{
		$this->innerId	= $id;
		return $this;
	}

	public function skipLink( string $path ): self
	{
		$this->linksToSkip[]	= $path;
		return $this;
	}
}
