<?php
/**
 *	Empty environment for remote dummy use.
 *
 *	Copyright (c) 2012-2020 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Empty environment for remote dummy use.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@extends		CMF_Hydrogen_Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Dummy extends CMF_Hydrogen_Environment {

	public $hasDatabase		= FALSE;

	public function __construct( $options ){
		$this->options	= $options;
		$this->path			= isset( $options['pathApp'] ) ? $options['pathApp'] : getCwd().'/';
		$this->initClock();
		$this->config		= new ADT_List_Dictionary();											//  create empty configuration object
	}
}
