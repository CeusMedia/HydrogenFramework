<?php
class View_Helper_Navigation_SingleList extends Framework_Hydrogen_View_Helper
{
	protected $linkMap;
	protected $classInner		= NULL;

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