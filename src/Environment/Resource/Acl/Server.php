<?php
/**
 *	Setup for access control list using a remote server.
 *
 *	Copyright (c) 2010-2020 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Setup for access control list using a remote server.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@extends		CMF_Hydrogen_Environment_Resource_Acl_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Acl_Server extends CMF_Hydrogen_Environment_Resource_Acl_Abstract
{
	/**
	 *	Returns all rights of a role.
	 *	@access		protected
	 *	@param		integer		$roleId			Role ID
	 *	@return		array
	 */
	protected function getRights( $roleId ): array
	{
		if( $this->hasFullAccess( $roleId ) )
			return array();
		if( $this->hasNoAccess( $roleId ) )
			return array();
		if( !isset( $this->rights[$roleId] ) )
		{
			$rights	= $this->env->getServer()->getData( 'role', 'getRights', array( $roleId ) );
			$this->rights[$roleId]	= array();
			foreach( $rights as $right ){
				if( !isset( $this->rights[$roleId][$right->controller] ) )
					$this->rights[$roleId][$right->controller]	= array();
				$this->rights[$roleId][$right->controller][]	= $right->action;
			}
		}
		return $this->rights[$roleId];
	}

	/**
	 *	Return list controller actions or matrix of controllers and actions of role.
	 *	@abstract
	 *	@public
	 *	@param		string		$controller		Controller to list actions for, otherwise return matrix
	 *	@param		integer		$roleId			Specified role, otherwise current role
	 *	@return		array						List of actions or matrix of controllers and actions
	 */
	public function index( string $controller = NULL, $roleId = NULL ): array
	{
		throw new Exception( 'Not implemented yet' );
	}

	/**
	 *	Allowes access to a controller action for a role.
	 *	@access		public
	 *	@param		integer		$roleId			Role ID
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		integer
	 */
	public function setRight( $roleId, string $controller, string $action )
	{
		if( $this->hasFullAccess( $roleId ) )
			return -1;
		if( $this->hasNoAccess( $roleId ) )
			return -2;
		$data	= array( 'controller' => $controller, 'action' => $action );
		return $this->env->getServer()->postData( 'role', 'setRight', array( $roleId ), $data );
	}

	//  --  PROTECTED  --  //

	/**
	 *	Returns Role.
	 *	@access		protected
	 *	@param		integer		$roleId			Role ID
	 *	@return		array|object
	 */
	protected function getRole( $roleId )
	{
		if( !$this->roles )
			foreach( $this->env->getServer()->getData( 'role', 'index' ) as $role )
				$this->roles[$role->roleId]	= $role;
		return $this->roles[$roleId];
	}
}
