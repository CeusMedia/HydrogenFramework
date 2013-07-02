<?php
/**
 *	...
 *
 *	Copyright (c) 2007-2012 Christian Würker (ceusmedia.com)
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
 *	@package		Hydrogen.View.Helper.Navigation
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	...
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper.Navigation
 *	@extends		CMF_Hydrogen_View_Helper_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			Code doc
 */
class CMF_Hydrogen_View_Helper_Navigation_SingleList extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $linkMap;
	protected $innerClass		= 'single';
	protected $innerId			= 'navigation-inner';
	protected $needsEnv			= FALSE;

	public function __construct( $linkMap, $innerClass = NULL, $innerId = NULL )
	{
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
	 *	@return		string
	 */
	static public function getCurrentKey( $linkMap, $current = NULL ){
		$path		= $current;
		if( isset( $_REQUEST['path'] ) && $current !== NULL )
			$path	= utf8_decode( $_REQUEST['path'] );
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

	public function render( $current = NULL, $niceUrls = FALSE )
	{
		$path	= empty( $_REQUEST['path'] ) ? $current : $_REQUEST['path'];
		$active	= $this->getCurrentKey( $this->linkMap, $current );
		$list	= array();
		foreach( $this->linkMap as $key => $label )
		{
			$class		= $active == $key ? 'active' : NULL;
			$url		= $key == "index" ? "./" : ( $niceUrls ? './'.$key : './?controller='.$key );
			$link		= UI_HTML_Elements::Link( $url, $label, $class );
			$list[]		= UI_HTML_Elements::ListItem( $link, 0, array( 'class' => $class ) );
		}
		if( !$list )
			return $list;
		$attr	= array( 'class' => $class );
		$list	= UI_HTML_Elements::unorderedList( $list, 0, $attr );
		$attr	= array(
			'id'	=> $this->innerId,
			'class'	=> $this->innerClass
		);
		return UI_HTML_Tag::create( 'div', $list, $attr );
	}

	public function setInnerClass( $class )
	{
		$this->innerClass	= $class;
	}

	public function setInnerId( $id )
	{
		$this->innerId	= $id;
	}
}
?>
