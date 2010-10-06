<?php
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
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
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@extends		Framework_Hydrogen_Environment_Web
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@deprecated		use web (or later console etc.) environment instead
 */
class CMF_Hydrogen_Environment extends CMF_Hydrogen_Environment_Web
{
	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->initClock();
		$this->initConfiguration();																	//  --  CONFIGURATION  --  //
		$this->initSession();																		//  --  SESSION HANDLING  --  //
		$this->initMessenger();																		//  --  UI MESSENGER  --  //
		$this->initDatabase();																		//  --  DATABASE CONNECTION  --  //
		$this->initLanguage();																		//  --  LANGUAGE SUPPORT  --  //
		$this->initRequest();																		//  --  HTTP REQUEST HANDLER  --  //
		$this->initResponse();																		//  --  HTTP RESPONSE HANDLER  --  //
//		$this->initFieldDefinition();																//  --  FIELD DEFINITION SUPPORT  --  //
	}

	public function close()
	{
		unset( $this->dbc );																		//
		unset( $this->session );																	//
		unset( $this->request );																	//
		unset( $this->response );																	//
		unset( $this->messenger );																	//
		unset( $this->language );																	//
		unset( $this->config );																		//
		unset( $this->clock );																		//
	}
}
?>