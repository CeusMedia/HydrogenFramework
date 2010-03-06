<?php
/**
 *	Data Model of Roles.
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
 *	@package		framework.neon.models
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
import( 'de.ceus-media.framework.neon.Model' );
import( 'de.ceus-media.framework.neon.models.RoleRight' );
/**
 *	Data Model of Roles.
 *	@category		cmClasses
 *	@package		framework.neon.models
 *	@extends		Framework_Neon_Model
 *	@uses			RoleRight
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
class Framework_Neon_Models_Role extends Framework_Neon_Model
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		int		roleId		ID of Role
	 *	@return		void
	 */
	public function __construct( $roleId = false )
	{
		$tableName	= "roles";
		$fieldList	= array (
			"roleId",
			"title",
			"description",
			);
		$primaryKey	= "roleId";
		$foreignKeys	= array();

		parent::__construct( $tableName, $fieldList, $primaryKey, $roleId );
		$this->setForeignKeys ( $foreignKeys );
	}
	
	/**
	 *	Indicates whether a RoleRight is set by Object ID and Action ID.
	 *	@access		public
	 *	@param		int			roleId		Role ID
	 *	@param		string		object		Object Name
	 *	@param		string		action		Action Name
	 *	@return		bool
	 */
	public function hasRightByID( $roleId, $objectId, $actionId )
	{
		$right	= new Framework_Neon_Models_RoleRight();
		return $right->hasRightByID( $roleId, $objectId, $actionId );
	}
	
	/**
	 *	Indicates whether a Role Right is set by Right Object Name and Right Action Name.
	 *	@access		public
	 *	@param		int			roleId		Role ID
	 *	@param		string		object		Right Object Name
	 *	@param		string		action		Right Action Name
	 *	@return		bool
	 */
	public function hasRight( $roleId, $object, $action )
	{
		$right	= new Framework_Neon_Models_RoleRight();
		return $right->hasRight( $roleId, $object, $action );
	}
}
?>