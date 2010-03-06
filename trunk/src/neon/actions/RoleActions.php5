<?php
/**
 *	Actions for Roles.
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
 *	@package		framework.neon.actions
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
import( 'de.ceus-media.framework.neon.DefinitionAction' );
import( 'de.ceus-media.framework.neon.models.Role' );
import( 'de.ceus-media.framework.neon.models.RightObject' );
import( 'de.ceus-media.framework.neon.models.RightAction' );
/**
 *	Actions for Roles.
 *	@category		cmClasses
 *	@package		framework.neon.actions
 *	@extends		Framework_Neon_DefinitionAction
 *	@uses			Framework_Neon_Models_Role
 *	@uses			Framework_Neon_Models_RightObject
 *	@uses			Framework_Neon_Models_RightAction
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
class Framework_Neon_Actions_RoleActions extends Framework_Neon_DefinitionAction
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loadLanguage( 'role' );
		$this->add( 'addRole', 'addRole' );
		$this->add( 'editRole', 'editRole' );
		$this->add( 'removeRole','removeRole' );
		$this->add( 'addRight','addRight' );
		$this->add( 'removeRight', 'removeRight' );
		$this->add( 'saveRights', 'setRights' );
		$auth	= $this->ref->get( 'auth' );
		if( !$auth->hasRight( 'role', 'navigate' ) )
			$this->restart( "./" );
	}

	/**
	 *	Adds a Role.
	 *	@access		protected
	 *	@return		void
	 */
	protected  function addRole()
	{
		$request	=& $this->ref->get( 'request' );
		if( $this->validateForm( 'role', 'addRole', 'role', 'add' ) )
		{
			$title = $request->get( 'add_title' );
			$role	= new Framework_Neon_Models_Role();
			$roles	= $role->getAllData();
			foreach( $roles as $role )
				if( strtolower( $role['title'] ) == strtolower( $title ) )
					return $this->messenger->noteError( $this->words['role']['msg']['error3'], $title );
			$role	= new Framework_Neon_Models_Role();
			$data	= array(
				"title"	=> $title,
				"description"	=> $request->get( 'add_description' ),
				);
			$roleId	= $role->addData( $data );
			$request->remove( 'add_title' );
			$request->remove( 'add_description' );
			$this->messenger->noteSuccess( $this->words['role']['msg']['success1'], $title );
		}
	}

	/**
	 *	Edits a Role.
	 *	@access		protected
	 *	@return		void
	 */
	protected function editRole()
	{
		$request	=& $this->ref->get( 'request' );
		if( $roleId = $request->get( 'roleId' ) )
		{
			if( $this->validateForm( 'role', 'editRole', 'role', 'edit' ) )
			{
				$title = $request->get( 'edit_title' );
				$role	= new Framework_Neon_Models_Role();
				$roles	= $role->getAllData();
				foreach( $roles as $role )
					if( $role['roleId'] != $roleId && strtolower( $role['title'] ) == strtolower( $title ) )
						return $this->messenger->noteError( $this->words['role']['msg']['error3'], $title );
				$data	= array(
					"title"	=> $title,
					"description"	=> $request->get( 'edit_description' ),
					);
				$role	= new Framework_Neon_Models_Role( $roleId );
				$role->modifyData( $data );
				$this->messenger->noteSuccess( $this->words['role']['msg']['success2'], $title );
			}
		}
		else
			$this->messenger->noteError( $this->words['role']['msg']['error1'] );
	}

	/**
	 *	Removes a Role.
	 *	@access		protected
	 *	@return		void
	 *	@todo		finish Implementation
	 */
	protected function removeRole()
	{
		print_m( $_POST );
		die();
/*		$request	=& $this->ref->get( 'request' );
		if( $request->get( 'rid' ) )
		{
			if( is_array( $rights = $request->get( 'rights' ) ) && count( $rights ) )
			{
				$roleright	= new Framework_Neon_Models_RoleRight();
				foreach( $rights as $right )
				{
					$parts	= explode( "|", $right );
					$data	= array(
						"rid"		=> $rid,
						"object"	=> $parts[0],
						"action"	=> $parts[1],
					);
					$roleright->addData( $data );
					$this->messenger->noteSuccess( "Role Right '".$parts[0]."->".$parts[1]."' has been set." );
				}
			}
			else
				$this->messenger->noteError ( $lan['roles']['msg']['error4'] );
		}
*/	}
	
	protected function setRights()
	{
		$request	=& $this->ref->get( 'request' );

		if( $roleId = $request->get( 'roleId' ) )
		{
			$object		=  new Framework_Neon_Models_RightObject();
			$action		=  new Framework_Neon_Models_RightAction();
			$objects	= $object->getObjects();
			$actions	= $action->getActions();

			$changes	= 0;
			$role	= new Framework_Neon_Models_RoleRight();
			$right	= $request->get( 'right' );

			foreach( $objects as $objectId => $object )
			{
				foreach( $actions as $actionId => $action )
				{
					$old	= (int)$role->hasRightByID( $roleId, $objectId, $actionId );
					$new	= empty( $right[$objectId][$actionId] ) ? 0 : 1;
					if( $old != $new )
					{
						if( $old < $new )
						{
							$data	= array(
								"roleId"		=> $roleId,
								"rightObjectId"	=> $objectId,
								"rightActionId"	=> $actionId,
							);
							$role->addData( $data );
						}
						else
						{
							$role->focusForeign( 'roleId', $roleId );
							$role->focusForeign( 'rightObjectId', $objectId );
							$role->focusForeign( 'rightActionId', $actionId );
							$role->deleteData();
							$role->defocus();
						}
						$changes++;
					}
				}
			}
			if( $changes )
				$this->messenger->noteSuccess( $this->words['role']['msg']['success6'], $changes );
			else
				$this->messenger->noteError( $this->words['role']['msg']['error5'] );
		}
		else
			$this->messenger->noteError ( $this->words['role']['msg']['error1'] );
	}
}
?>
