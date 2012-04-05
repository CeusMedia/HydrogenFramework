<?php
/**
 *	...
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
 *	@package		Hydrogen.View.Helper.Navigation
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
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
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			Code doc
 */
class CMF_Hydrogen_View_Helper_Navigation_SingleAclList extends CMF_Hydrogen_View_Helper_Navigation_SingleList
{
	protected $needsEnv			= TRUE;

	protected function getFilteredLinkMap(){
		$roleId		= $this->env->session->get( 'roleId' );
		if( !$roleId )
			return $this->linkMap;

		$map	= array();
		foreach( $this->linkMap as $key => $label ){
			$key	= strlen( trim( $key ) ) ? $key : 'index';
			if( $this->env->acl->hasRight( $roleId, str_replace( '/', '_', $key ), 'index' ) )
				$map[$key]	= $label;
			else{
				$parts	= explode( '/', str_replace( '_', '/', $key ) );
				$last	= array_pop( $parts );
				$first	= join( '/', $parts );
				if( $this->env->acl->hasRight( $roleId, $first, $last ) )
					$map[$key]	= $label;
			}
		}
		return $map;
	}

	public function render( $current = NULL )
	{
		$path		= empty( $_REQUEST['path'] ) ? $current : $_REQUEST['path'];
		$linkMap	= $this->getFilteredLinkMap();
		$active		= $this->getCurrentKey( $linkMap, $current );
		$list		= array();
		foreach( $linkMap as $key => $label )
		{
			$key		= str_replace( '_', '/', $key );

/*			$levelPath	= count( explode( '/', $path ) );
			$levelKey	= count( explode( '/', $key ) );
			if( $levelPath == $levelKey )
				$active		= $path == $key;
			else if( $levelPath < $levelKey )
				$active		= $path.'/index' == $key;
			else
				 $active	= substr( $path, 0, strlen( $key ) + 1 ) == $key.'/';
			$class		= $active == $key ? 'active' : NULL;
*/
			$class		= $active == $key ? 'active' : NULL;
			$url		= $key == "index" ? "./" : './'.$key;
			$link		= UI_HTML_Elements::Link( $url, $label, $class );
			$attributes	= array( 'id' => 'navi-link-'.str_replace( '/', '-', $key ), 'class' => $class );
			$list[]		= UI_HTML_Elements::ListItem( $link, 0, $attributes );
		}
		if( !$list )
			return '';
		$list	= UI_HTML_Elements::unorderedList( $list );
		$attr	= array(
			'id'	=> $this->innerId,
			'class'	=> $this->innerClass
		);
		return UI_HTML_Tag::create( 'div', $list, $attr );
	}
}
?>