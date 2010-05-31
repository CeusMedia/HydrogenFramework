<?php
/**
 *	...
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
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
 *	@copyright		2007-2010 Christian Würker
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
 *	@extends		Framework_Hydrogen_View_Helper
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			Code doc
 */
class Framework_Hydrogen_View_Helper_Navigation_SingleList extends Framework_Hydrogen_View_Helper
{
	protected $linkMap;
	protected $classInner		= 'single';

	public function __construct( $linkMap )
	{
		$this->linkMap		= $linkMap;
	}

	public function render( $current = NULL )
	{
		$list	= array();
		$active	= FALSE;
		foreach( $this->linkMap as $key => $label )
		{
			$class		= ( $current == $key ) ? 'active' : NULL;
			$link		= UI_HTML_Elements::Link( './?controller='.$key, $label, $class );
			$list[]		= UI_HTML_Elements::ListItem( $link, 0 );
		}
		$attr	= array( 'class' => $class );
		$list	= UI_HTML_Elements::unorderedList( $list, 0, $attr );
		$attr	= array(
			'id'	=> 'navigation-inner',
			'class'	=> $this->classInner
		);
		return UI_HTML_Tag::create( 'div', $list, $attr );
	}

	public function setClassInner( $class )
	{
		$this->classInner	= $class;
	}
}
?>