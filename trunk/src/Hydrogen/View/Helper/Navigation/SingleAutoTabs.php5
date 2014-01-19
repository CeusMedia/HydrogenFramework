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

	public $classContainer	= "";
	public $classWidget		= "ui-tabs ui-widget ui-corner-all";
	public $classHelper		= "ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all";
	public $classTab		= "ui-state-default ui-corner-top";
	public $classTabActive	= "ui-tabs-selected ui-state-active";
	protected $container	= FALSE;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}

	public function render( $current = NULL, $niceUrls = FALSE ){
		$request	= $this->env->getRequest();
		$userId		= $this->env->getSession()->get( 'userId' );
		$language	= $this->env->getLanguage()->getLanguage();

		$current			= $request->get( 'controller' ).'/'.$request->get( 'action' );
		$linkMap			= $this->getUserModuleLinks( $userId, $language, TRUE );
		$active				= $this->getCurrentKey( $linkMap, $current );
		$linkItemList		= array();
		$rankedLinkItemList	= array();

		foreach( $linkMap as $link ){
			if( in_array( $link->path, $this->linksToSkip ) )
				continue; 
			$class	= $this->classTab.' access-'.$link->access;
			$class	.= $active == $link->path ? ' '.$this->classTabActive : '';
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
			'class'	=> $this->classHelper,
//			'style'	=> 'float: left; height: auto;'
		);
		$list		= UI_HTML_Tag::create( 'ul', join( $linkItemList ), $attributes	);
		if( $this->container )
			$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'container' ) );

		$widget		= UI_HTML_Tag::create( 'div', $list, array( 'class' => $this->classWidget ) );
		return UI_HTML_Tag::create( 'div', $widget, array( 'class' => $this->classContainer ) );
	}

	public function setContainer( $boolean ){
		$this->container	= (boolean) $boolean;
	}

	protected function getUserModuleLinks( $userId, $language, $useAcl = TRUE ){
		$acl		= $this->env->getAcl();
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
				if( $useAcl ){
					$right1	= (int) $acl->has( $controller.'_'.$action );
					$right2	= (int) $acl->has( $controller, $action );
					if( !( $right1 + $right2 ) )
						continue;
				}
				if( $link->access == 'inside' && !$userId )											//  @todo	not needed anymore?
					continue;
				if( $link->access == 'outside' && $userId )											//  @todo	not needed anymore?
					continue;
				$linkMap[$link->path]	= $link;
			}
		}
		return $linkMap;
	}
}
?>
