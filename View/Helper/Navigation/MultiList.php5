<?php
class Framework_Hydrogen_View_Helper_Navigation_MultiList extends Framework_Hydrogen_View_Helper
{
	protected $multiple	= FALSE;

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