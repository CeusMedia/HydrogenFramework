<?php
/**
 *	Data Model of Users.
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
 *	@since			01.12.2005
 *	@version		0.6
 */
import( 'de.ceus-media.framework.neon.Model' );
import( 'de.ceus-media.framework.neon.models.UserRole' );
import( 'de.ceus-media.framework.neon.models.Role' );
/**
 *	Data Model of Users.
 *	@category		cmClasses
 *	@package		framework.neon.models
 *	@extends		Framework_Neon_Model
 *	@uses			Framework_Neon_Models_UserRole
 *	@uses			Framework_Neon_Models_Role
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.6
 */
class Framework_Neon_Models_User extends Framework_Neon_Model
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		int		userId		ID of User
	 *	@return		void
	 */
	public function __construct( $userId = false )
	{
		$tableName	= "users";
		$fieldList	= array (
			"userId",
			"username",
			"password",
			"email",
			"language",
			"notify",
			"created",
			"modified"
			);
		$primaryKey	= "userId";
		$foreignKeys	= array( "username", "email", "notify" );

		parent::__construct( $tableName, $fieldList, $primaryKey, $userId );
		$this->setForeignKeys ( $foreignKeys );
	}
	
	public function getRolesFromUID( $userId )
	{
		$role	= new Framework_Neon_Models_UserRole();
		$role->focusForeign( "userId", $userId );
		$roles	= $role->getData();
		$list		= array();
		foreach( $roles as $role )
		{
			$data	= new Framework_Neon_Models_Role( $role['roleId'] );
			$data	= $data->getData( true );
			$list[$role['roleId']]	= $data['title'];
		}
		return $list;
	}

	/**
	 *	Returns ID of User by its Name.
	 *	@access		public
	 *	@param		string		username		Name of User
	 *	@return		int
	 */
	public function getUidFromUsername( $username )
	{
		$this->defocus();
		$this->focusForeign( "username", $username );
		$data	= $this->getData( true );
		$this->defocus();
		if( count( $data ) )
			return $data['userId'];
		return 0;
	}

	/**
	 *	Returns the Name of an existing User.
	 *	@access		public
	 *	@return		string
	 */
	public function getUsernameFromUid( $userId )
	{
		$this->focusPrimary( $userId );
		$data	= $this->getData( true );
		$this->defocus();
		if( count( $data ) )
			return $data['username'];
	}

	/**
	 *	Returns the Name of current User.
	 *	@access		public
	 *	@return		string
	 */
	public function getUsername()
	{
		if( $this->isFocused() )
		{
			$data	= $this->getData( true );
			if( isset( $data['username'] ) )
				return $data['username'];
		}
		return "";
	}

	public function isNotify()
	{
		if( $this->isFocused() )
		{
			$data	= $this->getData( true );
			return $data['notify'];
		}
		return false;
	}
	
	public function hasRole( $userId, $roleId )
	{
		$role	= new Framework_Neon_Models_UserRole();
		$role->focusForeign( "userId", $userId );
		$role->focusForeign( "roleId", $roleId );
		$roles	= $role->getData();
		return count( $roles ) > 0 ;
	}

	public function hasRight( $userId, $object, $action )
	{
		$role	= new Framework_Neon_Models_UserRole();
		$role->focusForeign( "userId", $userId );
		$roles	= $role->getData( false );
		$role	= new Framework_Neon_Models_Role();
		foreach( $roles as $roledata )
			if( $role->hasRight( $roledata['roleId'], $object, $action ) )
				return true;
		return false;		
	}

	public function hasRightByID( $userId, $object, $action )
	{
		$role	= new Framework_Neon_Models_UserRole();
		$role->focusForeign( "userId", $userId );
		$roles	= $role->getData();
		$role	= new Framework_Neon_Models_Role();
		foreach( $roles as $roledata )
			if( $role->hasRightByID( $roledata['roleId'], $object, $action ) )
				return true;
		return false;		
	}
}
?>