<?php
/**
 *	...
 *
 *	Copyright (c) 2011 Christian Würker (ceusmedia.com)
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
 *	@package		Hydrogen.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.4
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Router_Single extends CMF_Hydrogen_Environment_Router_Abstract implements CMF_Hydrogen_Environment_Router_Interface
{
	protected $isSetUp	= FALSE;

	public function parseFromRequest()
	{
		if( !$this->env->request )
			throw new RuntimeException( 'Routing needs a registered request resource' );
		$request	= $this->env->getRequest();
		
		if( $request->has( '__path' ) ) 
			self::$pathKey	= '__path';
		
		$path		= $request->getFromSource( self::$pathKey, 'get' );
		if( !trim( $path ) )
			return;

		$parts	= explode( '/', $path );
		$request->set( 'controller',	array_shift( $parts ) );
		$request->set( 'action',		array_shift( $parts ) );
		$arguments	= array();
		while( count( $parts ) )
		{
			$part = trim( array_shift( $parts ) );
			if( strlen( $part ) )
				$arguments[]	= $part;
		}
		$request->set( 'arguments', $arguments );
	}
}
?>
