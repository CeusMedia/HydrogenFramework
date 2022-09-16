<?php
/**
 *	...
 *
 *	Copyright (c) 2007-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper\Navigation;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction as AbstractViewHelper;

/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper.Navigation
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@deprecated		use modules Info_Pages + UI_Navigation instead
 *	@todo			remove in version 0.9
 */
class MultiList extends AbstractViewHelper
{
	protected $multiple		= FALSE;
	protected $needsEnv		= FALSE;
	protected $words;

	public function __construct( $words )
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.5' )
			->setExceptionVersion( '0.9' )
			->message( 'Use modules Info_Pages + UI_Navigation instead' );
		$this->words	= $words;
	}

	public function render( string $current = NULL ): string
	{
		$active		= FALSE;
		$navi		= $this->buildNavigationLinkList( 'links', $current, $active );
		$classes	= array( $this->multiple ? 'multiple' : 'single' );
		if( $active )
			$classes[]	= 'active';
		$classes	= implode( ' ', $classes );
		$container	= HtmlTag::create( 'div', $navi, array( 'id' => 'navigation-inner', 'class' => $classes ) );
		return $container;
	}

	protected function buildNavigationLinkList( string $linkSectionKey, string $current, &$parentActive, int $level = 0 ): string
	{
		if( !isset( $this->words[$linkSectionKey] ) )
			return '';

		if( $level > 0 && !$this->multiple )
			$this->multiple	= TRUE;

		$list		= array();
		$active		= FALSE;

		$active	= FALSE;
		foreach( $this->words[$linkSectionKey] as $key => $label ){
			$active			= $current == $key;
			$sub			= $this->buildNavigationLinkList( $linkSectionKey.'.'.$key, $current, $active, $level+1 );
			$parentActive	= $parentActive || $active;
			$class			= ( $active || $current == $key ) ? 'active' : NULL;
			$link			= HtmlElements::Link( "?controller=".$key, $label.$sub, $class );
			$list[]			= HtmlElements::ListItem( $link, $level );
		}
		$classes	= array( 'level-'.$level );
		if( $parentActive )
			$classes[]	=  'active';
		return HtmlElements::unorderedList( $list, $level, array( 'class' => implode( ' ', $classes ) ) );
	}
}
