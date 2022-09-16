<?php
/**
 *	...
 *
 *	Copyright (c) 2010-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2010-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper\Navigation;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper.Navigation
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code doc
 */
class SingleAclList extends SingleList
{
	protected $needsEnv			= TRUE;

	public function getFilteredLinkMap( array $linkMap ): array
	{
		$roleId		= $this->env->getSession()->get( 'auth_role_id' );
		if( !$roleId )
			return $linkMap;

		$map	= array();
		foreach( $linkMap as $key => $label ){
			$key	= strlen( trim( $key ) ) ? $key : 'index';
			if( $this->env->getAcl()->hasRight( $roleId, str_replace( '/', '_', $key ), 'index' ) )
				$map[$key]	= $label;
			else{
				$parts	= explode( '_', str_replace( '/', '_', $key ) );
				$last	= array_pop( $parts );
				$first	= join( '/', $parts );
				if( $this->env->getAcl()->hasRight( $roleId, $first, $last ) )
					$map[$key]	= $label;
			}
		}
		return $map;
	}

	public function render( string $current = NULL, bool $niceUrls = FALSE ): string
	{
		$path		= empty( $_REQUEST['path'] ) ? $current : $_REQUEST['path'];
		$linkMap	= $this->getFilteredLinkMap( $this->linkMap );
		$active		= $this->getCurrentKey( $linkMap, $path );
		$list		= array();
		foreach( $linkMap as $key => $label ){
			$key		= str_replace( '_', '/', $key );
			$class		= $active == $key ? 'active' : NULL;
			$url		= $key == "index" ? "./" : './'.$key;
			$link		= HtmlElements::Link( $url, $label, $class );
			$attributes	= array( 'id' => 'navi-link-'.str_replace( '/', '-', $key ), 'class' => $class );
			$list[]		= HtmlElements::ListItem( $link, 0, $attributes );
		}
		if( !$list )
			return '';
		$list	= HtmlElements::unorderedList( $list );
		$attr	= array(
			'id'	=> $this->innerId,
			'class'	=> $this->innerClass
		);
		return HtmlTag::create( 'div', $list, $attr );
	}
}
