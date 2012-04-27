<?php
class CMF_Hydrogen_View_Helper_Navigation_SingleAutoTabs extends CMF_Hydrogen_View_Helper_Abstract{

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}
	
	public function render( $cornerTabs = "ui-corner-top", $cornerHelper = "ui-corner-all", $cornerWidget = "ui-corner-all" ){
		
		$request	= $this->env->getRequest();
		$acl		= $this->env->getAcl();
		$userId		= $this->env->getSession()->get( 'userId' );
		$language	= $this->env->getLanguage()->getLanguage();
	
		$requestedPath			= $request->get( 'path' );
		$linkItemList			= array();
		$rankedLinkItemList		= array();
		foreach( $this->env->getModules()->getAll() as $module ){
			foreach( $module->links as $link ){
				if( $link->language != $language )
					continue;
				if( $link->access == 'none' )
					continue;

				$pathParts	= explode( '/', $link->path );
				$action		= array_pop( $pathParts );
				$controller	= implode( '_', $pathParts ); 
				if( !( $acl->has( $controller.'_'.$action ) + $acl->has( $controller, $action ) ) )
					continue;
				if( $link->access == 'inside' && !$userId || $link->access == 'outside' && $userId )		//  @todo	not needed anymore?
					continue;																				//  @todo	not needed anymore?
				$class	= 'ui-state-default '.$cornerTabs.' access-'.$link->access;
				if( strlen( $requestedPath ) )
					$isCurrent	= substr( $requestedPath, 0, strlen( $link->path ) ) == $link->path;
				else
					$isCurrent	= !strlen( $link->link ); 
				if( $isCurrent )
					$class	.= ' ui-tabs-selected ui-state-active';
				$label	= $link->label;
				$anchor	= UI_HTML_Elements::Link( './'.$link->link, $label );
				$item	= UI_HTML_Tag::create( 'li', $anchor, array( 'class' => $class ) );
				if( !isset( $rankedLinkItemList[$link->rank] ) )
					$rankedLinkItemList[$link->rank]	= array();
				$rankedLinkItemList[$link->rank][]	= $item;
			}
		}
		ksort( $rankedLinkItemList );
		foreach( $rankedLinkItemList as $rank => $links )
			foreach( $links as $item )
				$linkItemList[]	= $item;

		$attributes	= array(
			'class'	=> 'ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header '.$cornerHelper,
			'style'	=> 'float: left; width: 100%; height: auto;'
		);
		$list		= UI_HTML_Tag::create( 'ul', join( $linkItemList ), $attributes	);
		return UI_HTML_Tag::create( 'div', $list, array( 'class' => 'ui-tabs ui-widget '.$cornerWidget ) );
	}
}
?>
