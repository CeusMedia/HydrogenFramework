<?php
/**
 *	Abstract access control list resource.
 *
 *	Copyright (c) 2011 Christian Würker (ceus-media.de)
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
 *	@package		Hydrogen.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.3
 *	@version		$Id$
 */
/**
 *	Abstract access control list resource.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.3
 *	@version		$Id$
 */
abstract class CMF_Hydrogen_Environment_Resource_Acl_Abstract
{
	public $roleAccessNone	= 0;
	public $roleAccessFull	= 1;
	public $roleAccessAcl	= 2;

	protected $rights	= array();
	protected $roles	= array();

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment Object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env	= $env;
	}

	/**
	 *	Indicates wheter a role is system operator and has access to all controller actions.
	 *	@access		public
	 *	@param		integer		$roleId			Role ID
	 *	@return		boolean
	 */
	public function hasFullAccess( $roleId )
	{
		$role	= $this->getRole( $roleId );
		if( !$role )
			throw new InvalidArgumentException( 'Role with ID '.$roleId.' is not existing' );
		return $role->access == $this->roleAccessFull;
	}

	/**
	 *	Indicates whether a role has no access as all.
	 *	@access		public
	 *	@param		integer		$roleId			Role ID
	 *	@return		boolean
	 */
	public function hasNoAccess( $roleId )
	{
		$role	= $this->getRole( $roleId );
		if( !$role )
			throw new InvalidArgumentException( 'Role with ID '.$roleId.' is not existing' );
		return $role->access == $this->roleAccessNone;
	}

	/**
	 *	Indicates whether access to a controller action is allowed.
	 *	@access		public
	 *	@param		integer		$roleId			Role ID
	 *	@param		string		$controller		Name of controller
	 *	@param		string		$action			Name of action
	 *	@return		boolean
	 */
	public function hasRight( $roleId, $controller = 'index', $action = 'index' )
	{
		if( $this->hasFullAccess( $roleId ) )
			return TRUE;
		if( $this->hasNoAccess( $roleId ) )
			return FALSE;
		$rights	= $this->getRights( $roleId );
		foreach( $rights as $right )
			if( $right->controller == $controller )
				if( $right->action == $action )
					return TRUE;
		return FALSE;
	}
}
?>
