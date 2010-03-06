<?php
/**
 *	View of static Contents.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.neon.views
 *	@extends		Framework_Neon_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			15.10.2006
 *	@version		0.2
 */
import( 'de.ceus-media.framework.neon.View' );
/**
 *	View of static Contents.
 *	@category		cmClasses
 *	@package		framework.neon.views
 *	@extends		Framework_Neon_View
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			15.10.2006
 *	@version		0.2
 */
class Framework_Neon_Views_ContentViews extends Framework_Neon_View
{
	public function buildContent()
	{
		$config		= $this->ref->get( 'config' );
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );
		
		$link		= $request->get( 'link' );
		$lan		= $session->get( 'language' );
		$file		= $config['paths']['html'].$lan."/".$link.".html";
		if( file_exists( $file ) )
			return implode( "", file( $file ) );
		else
//			header( "Location: ./" );
			$this->messenger->noteFailure( str_replace( "#URI#", $file, $this->words['main']['msg']['error_no_content'] ) );
	}
}
?>