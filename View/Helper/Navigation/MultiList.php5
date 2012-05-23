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
class CMF_Hydrogen_View_Helper_Navigation_MultiList extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $multiple		= FALSE;
	protected $needsEnv		= FALSE;

	public function __construct( $words )
	{
		$this->words	= $words;
	}

	public function render( $current = NULL )
	{
		$active		= FALSE;
		$navi		= $this->buildNavigationLinkList( 'links', $current, $active );
		$classes	= array( $this->multiple ? 'multiple' : 'single' );
		if( $active )
			$classes[]	= 'active';
		$classes	= implode( ' ', $classes );
		$container	= UI_HTML_Tag::create( 'div', $navi, array( 'id' => 'navigation-inner', 'class' => $classes ) );
		return $container;
	}

	protected function buildNavigationLinkList( $linkSectionKey, $current, &$parentActive, $level = 0 )
	{
		if( !isset( $this->words[$linkSectionKey] ) )
			return '';

		if( $level > 0 && !$this->multiple )
			$this->multiple	= TRUE;

		$list		= array();
		$active		= FALSE;

		$active	= FALSE;
		foreach( $this->words[$linkSectionKey] as $key => $label )
		{
			$active			= $current == $key;
			$sub			= $this->buildNavigationLinkList( $linkSectionKey.'.'.$key, $current, $active, $level+1 );
			$parentActive	= $parentActive || $active;
			$class			= ( $active || $current == $key ) ? 'active' : NULL;
			$link			= UI_HTML_Elements::Link( "?controller=".$key, $label.$sub, $class );
			$list[]			= UI_HTML_Elements::ListItem( $link, $level );
		}
		$classes	= array( 'level-'.$level );
		if( $parentActive )
			$classes[]	=  'active';
		return UI_HTML_Elements::unorderedList( $list, $level, array( 'class' => implode( ' ', $classes ) ) );
	}
}
?>