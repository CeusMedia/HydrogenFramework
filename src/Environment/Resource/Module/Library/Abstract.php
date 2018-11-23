<?php
/**
 *	Abstract module libraries.
 *
 *	Copyright (c) 2018 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Abstract module libraries.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class CMF_Hydrogen_Environment_Resource_Module_Library_Abstract implements CMF_Hydrogen_Environment_Resource_Module_Library_Interface{

	/**
	 *	Return module definition by module ID.
	 *	Returns NULL if module is not installed and strict mode is off.
	 *	Returns NULL if module is not active and strict mode is off and activeOnly is on.
	 *	@access		public
	 *	@param		string		$moduleId		ID of module to get definition for
	 *	@param		boolean		$activeOnly		Flag: exclude deactivated modules (default: yes)
	 *	@param		string		$strict			Flag: throw exception if not installed (default: yes)
	 *	@return		object|NULL
	 *	@throws		RangeException				if module is not installed (using strict mode)
	 *	@throws		RuntimeException			if module is not active (using strict mode and activeOnly)
	 */
	public function get( $moduleId, $activeOnly = TRUE, $strict = TRUE ){
		$moduleId	= $this->sanitizeId( $moduleId );
		if( !array_key_exists( $moduleId, $this->modules ) ){										//  module is not installed
			if( $strict )
				throw new RangeException( 'Module "'.$moduleId.'" is not installed' );
			return NULL;
		}
		if( $activeOnly && !$this->modules[$moduleId]->isActive ){
			if( $strict )
				throw new RuntimeException( 'Module "'.$moduleId.'" is not active' );
			return NULL;
		}
		return $this->modules[$moduleId];
	}

	/**
	 *	Return list of all installed (and active) modules.
	 *	@access		public
	 *	@param		boolean		$activeOnly		Flag: exclude deactivated modules (default: yes)
	 *	@return		array
	 */
	public function getAll( $activeOnly = TRUE ){
		if( !$activeOnly )
			return $this->modules;
		$modules	= array();
		foreach( $this->modules as $module )
			if( $module->isActive )
				$modules[$module->id]	= $module;
		return $modules;
	}

	/**
	 *	Indicate whether module is installed and active by module ID.
	 *	Returns false if module is not installed.
	 *	Returns false if module is installed and main switch "active" is existing and disabled.
	 *	Returns true otherwise.
	 *	@access		public
	 *	@param		string		$moduleId		ID of module to check for existance (and activity)
	 *	@param		boolean		$activeOnly		Flag: exclude deactivated modules (default: yes)
	 *	@return		boolean
	 */
	public function has( $moduleId, $activeOnly = TRUE ){
		$moduleId	= $this->sanitizeId( $moduleId );
		if( !array_key_exists( $moduleId, $this->modules ) )										//  module is not installed
			return FALSE;																			//
		if( $activeOnly )																			//  activity check is needed
			return $this->modules[$moduleId]->isActive;												//  return activity
		return TRUE;																				//  otherwise
	}

	protected function sanitizeId( $moduleId ){
		return str_replace( ':', '_', $moduleId );
	}
}