<?php
/**
 *	Integration of Piwik tracker.
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
 *	@package		Hydrogen.View.Helper.Tracker
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
/**
 *	Integration of Piwik tracker.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper.Tracker
 *	@extends		CMF_Hydrogen_View_Helper_Abstract
 *	@uses			UI_HTML_Tag
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
class CMF_Hydrogen_View_Helper_Tracker_Piwik extends CMF_Hydrogen_View_Helper_Abstract{

	public function render(){
		$config	= $this->env->getConfig();
		$page	= $this->env->getPage();
		if( !$config->get( 'tracker.enabled' ) )
			return '';
		$id	= $config->get( 'tracker.id' );
		if( !$id )
			throw new RuntimeException( 'Track code is missing in config::tracker.id' );
		$path	= $config->get( 'tracker.uri' );
		$page->js->addUrl( $path.'piwik.js' );
		$script	= '
try {
	var piwikTracker = Piwik.getTracker("'.$path.'piwik.php", '.$id.');
	piwikTracker.trackPageView();
	piwikTracker.enableLinkTracking();
} catch( err ) {}
';
		$page->js->addScript( $script );
		$noscript	= UI_HTML_Tag::create( 'noscript',
			UI_HTML_Tag::create( 'p',
				UI_HTML_Tag::create( 'img', NULL, array(
					'src'	=> $path.'piwik.php?idsite='.$id,
					'style'	=> 'border: 0',
					'alt'	=> ''
				) )
			)
		);
		return $noscript;
	}
}
?>