<?php
/**
 *	Generic Action Handler.
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
 *	@package		framework.neon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
import( 'de.ceus-media.framework.neon.Component' );
import( 'de.ceus-media.adt.Reference' );
import( 'de.ceus-media.file.ini.Reader' );
import( 'de.ceus-media.alg.TimeConverter' );
/**
 *	Generic Action Handler.
 *	@category		cmClasses
 *	@package		framework.neon
 *	@extends		Framework_Neon_Component
 *	@uses			ADT_Reference
 *	@uses			File_INI_Reader
 *	@uses			Alg_TimeConverter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
class Framework_Neon_Action extends Framework_Neon_Component
{
	/**	@var	array			$actions	Array of Action events and methods */
	protected $actions	= array();

	/**
	 *	Calls Actions by checking calls in Request.
	 *	@access		public
	 *	@return		void
	 */
	public function act()
	{
		$request	= $this->ref->get( 'request' );
		foreach( $this->actions as $event => $action )
			if( $request->has( $event ) )
				$this->$action();
	}

	/**
	 *	Adds an Action by an event name and a method name.
	 *	@access		public
	 *	@param		string		$event			Event name of Action
	 *	@param		string		$action			Method name of Action
	 *	@return		void
	 */
	public function add( $event, $action = false )
	{
		if( !$action )
			$action = $event;
		$this->actions[$event]	= $action;
	}

	/**
	 *	Indicates whether an Action is registered.
	 *	@access		public
	 *	@param		string		$event			Event name of Action
	 *	@return		bool
	 */
	public function has( $event )
	{
		return isset( $this->actions[$event]);
	}

	/**
	 *	Removes a registered Action.
	 *	@access		public
	 *	@param		string		$event			Event name of Action
	 *	@return		void
	 */
	public function remove( $event )
	{
		if( $this->has( $event ) )
			unset( $this->actions[$event] );
	}

	/**
	 *	Restart application after realizing an Action.
	 *	@access		public
	 *	@param		string		$request			Request URL with Query String
	 *	@return		void
	 */
	public function restart( $request )
	{
		header( "Location: ".$request );
		die;
	}
}
?>