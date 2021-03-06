<?php
/**
 *	Setup for fake access control list for fully public projects.
 *
 *	Copyright (c) 2010-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2010-2021 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Setup for access control list using a Database.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Acl_AllPublic extends CMF_Hydrogen_Environment_Resource_Acl_Abstract
{
	/**
	 *	Indicates whether access to a controller action is allowed for a given role.
	 *	@access		public
	 *	@param		integer		$roleId			Role ID
	 *	@param		string		$controller		Name of controller
	 *	@param		string		$action			Name of action
	 *	@return		integer		Always returns 1 for "access"
	 */
	public function hasRight( $roleId, string $controller = 'index', string $action = 'index' ): int
	{
		return 1;
	}

	/**
	 *	Return list controller actions or matrix of controllers and actions of role.
	 *	@access		public
	 *	@param		string		$controller		Controller to list actions for, otherwise return matrix
	 *	@param		integer		$roleId			Specified role, otherwise current role
	 *	@return		array						List of actions or matrix of controllers and actions
	 */
	public function index( string $controller = NULL, $roleId = NULL ): array
	{
		if( !$this->controllerActions )
			$this->scanControllerActions();
		if( $controller === NULL )
			return $this->controllerActions;
		if( array_key_exists( $controller, $this->controllerActions ) )
			return $this->controllerActions[$controller];
		return array();
	}

	/**
	 *	Allowes access to a controller action for a role.
	 *	@access		public
	 *	@param		integer		$roleId			Role ID
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		integer
	 */
	public function setRight( $roleId, string $controller, string $action ): int
	{
		return 1;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Returns all rights of a role.
	 *	@access		protected
	 *	@param		integer		$roleId			Role ID
	 *	@return		array
	 */
	protected function getRights( $roleId ): array
	{
		return array();
	}

	/**
	 *	Returns all rights of a role.
	 *	@access		protected
	 *	@param		integer		$roleId			Role ID
	 *	@return		array
	 */
	protected function getRole( $roleId )
	{
		return [
			'roleId'		=> 0,
			'title'			=> 'public visitor',
			'description'	=> 'Since there is no authentication, no roles are available. This role is a placeholder.',
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		];
	}

	/**
	 *	Scan controller classes for actions using disclosure.
	 *	@access		protected
	 *	@return		void
	 */
	protected function scanControllerActions()
	{
		$disclosure	= new CMF_Hydrogen_Environment_Resource_Disclosure();
		$classes	= $disclosure->reflect( 'classes/Controller/' );
		foreach( $classes as $className => $classData ){
			$className	= strtolower( str_replace( '_', '/', $className ) );
			$this->controllerActions[$className]	= array();
			foreach( $classData->methods as $methodName => $methodData )
				$this->controllerActions[$className][]	= $methodName;
		}
	}
}
