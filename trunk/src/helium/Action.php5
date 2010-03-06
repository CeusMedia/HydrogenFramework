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
 *	@package		framework.helium
 *	@uses			ADT_Reference
 *	@uses			Alg_TimeConverter
 *	@uses			File_INI_Reader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.5
 */
import( 'de.ceus-media.adt.Reference' );
import( 'de.ceus-media.file.ini.Reader' );
import( 'de.ceus-media.alg.time.Converter' );
/**
 *	Generic Action Handler.
 *	@category		cmClasses
 *	@package		framework.helium
 *	@uses			ADT_Reference
 *	@uses			Alg_TimeConverter
 *	@uses			File_INI_Reader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.5
 */
class Framework_Helium_Action
{
	/**	@var	array			$_actions		Array of Action events and methods */
	var $_actions	= array();
	/**	@var	ADT_Reference	$ref			Reference */
	var $ref;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->ref			= new ADT_Reference();
		$this->tc			= new Alg_Time_Converter;
		$this->messenger	= $this->ref->get( 'messenger' );
		$this->lan			=& $this->ref->get( 'language' );
	}

	/**
	 *	Calls Actions by checking calls in Request.
	 *	@access		public
	 *	@return		void
	 */
	public function act()
	{
		$request	=& $this->ref->get( 'request' );
		foreach( $this->_actions as $event => $action )
		{
			if( NULL !== $request->get( $event ) )
				return $this->$action();
		}
	}

	/**
	 *	Adds an Action by an event name and a method name.
	 *	@access		public
	 *	@param		string		$event			Event name of Action
	 *	@param		string		$action			Method name of Action
	 *	@return		void
	 */
	public function add( $event, $action )
	{
		$this->_actions[$event]	= $action;
	}

	/**
	 *	Indicates whether an Action is registered.
	 *	@access		public
	 *	@param		string		$event			Event name of Action
	 *	@return		bool
	 */
	public function has( $event )
	{
		return isset( $this->_actions[$event]);
	}

	/**
	 *	Loads a Language File into Language Space, needs Session.
	 *	@access		public
	 *	@param		string		$section			Section Name in Language Space
	 *	@param		string		$filename			File Name of Language File
	 *	@return		void
	 */
	public function loadLanguage( $section, $filename = false, $verbose = TRUE )
	{
		$session	= $this->ref->get( 'session' );
		if( !$filename )
			$filename	= $section;
		$uri	= "languages/".$session->get( 'language' )."/".$filename.".lan";
		if( file_exists( $uri ) )
		{
			$ir	= new File_INI_Reader( $uri, true );
			$this->lan[$section]	= $ir->toArray( true );
			return TRUE;
		}
		else if( $verbose )
			$this->messenger->noteFailure( "Language File '".$filename."' is not existing in '".$uri."'" );
		return FALSE;
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
			unset( $this->_actions[$event] );
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