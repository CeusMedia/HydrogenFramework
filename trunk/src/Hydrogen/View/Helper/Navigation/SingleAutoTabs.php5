<?php
/**
 *	View helper to render a tab navigation from defined module page links.
 *
 *	Copyright (c) 2012 Christian Würker (ceusmedia.com)
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
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
/**
 *	View helper to render a tab navigation from defined module page links.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper.Navigation
 *	@extends		CMF_Hydrogen_View_Helper_Navigation_SingleList
 *	@uses			UI_HTML_Tag
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
class CMF_Hydrogen_View_Helper_Navigation_SingleAutoTabs extends CMF_Hydrogen_View_Helper_Navigation_SingleList{

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}

	public function render( $cornerTabs = "ui-corner-top", $cornerHelper = "ui-corner-all", $cornerWidget = "ui-corner-all" ){
		$request	= $this->env->getRequest();
		$acl		= $this->env->getAcl();
		$userId		= $this->env->getSession()->get( 'userId' );
		$language	= $this->env->getLanguage()->getLanguage();

		$current	= $request->get( 'controller' ).'/'.$request->get( 'action' );

		$linkMap	= array();
		foreach( $this->env->getModules()->getAll() as $module ){
			foreach( $module->links as $link ){
				if( $link->language && $link->language != $language )
					continue;
				if( $link->access == 'none' )
					continue;
				if( !strlen( $link->label ) )
					continue;
#				if( isset( $linkMap[$link->path] ) )												//  link has been added already
#					continue;

				$pathParts	= explode( '/', $link->path );
				$action		= array_pop( $pathParts );
				$controller	= implode( '_', $pathParts ); 
				if( !( $acl->has( $controller.'_'.$action ) + $acl->has( $controller, $action ) ) )
					continue;
				if( $link->access == 'inside' && !$userId || $link->access == 'outside' && $userId )		//  @todo	not needed anymore?
					continue;																				//  @todo	not needed anymore?
				$linkMap[$link->path]	= $link;
			}
		}

		$active				= $this->getCurrentKey( $linkMap, $current );
		$linkItemList		= array();
		$rankedLinkItemList	= array();

		foreach( $linkMap as $link ){
			$class	= 'ui-state-default '.$cornerTabs.' access-'.$link->access;
			$class	.= $active == $link->path ? ' ui-tabs-selected ui-state-active' : '';
			$label	= $link->label;
			$uri	= $link->link ? $link->link : $link->path;
			$anchor	= UI_HTML_Tag::create( 'a', $label, array( 'href' => './'.$uri ) );
			$item	= UI_HTML_Tag::create( 'li', $anchor, array( 'class' => $class ) );
			if( !isset( $rankedLinkItemList[$link->rank] ) )
				$rankedLinkItemList[$link->rank]	= array();
			$rankedLinkItemList[$link->rank][]	= $item;
		}
		ksort( $rankedLinkItemList );
		foreach( $rankedLinkItemList as $rank => $links )
			foreach( $links as $item )
				$linkItemList[]	= $item;

		$attributes	= array(
			'class'	=> 'ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header '.$cornerHelper,
			'style'	=> 'float: left; height: auto;'
		);
		$list		= UI_HTML_Tag::create( 'ul', join( $linkItemList ), $attributes	);
		return UI_HTML_Tag::create( 'div', $list, array( 'class' => 'ui-tabs ui-widget '.$cornerWidget ) );
	}
}
?>
