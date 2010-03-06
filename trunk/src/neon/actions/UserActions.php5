<?php
/**
 *	Actions for Users.
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
import( 'de.ceus-media.framework.neon.models.UserRole' );
/**
 *	Actions for Users.
 *	@category		cmClasses
 *	@package		framework.neon.actions
 *	@extends		Framework_Neon_DefinitionAction
 *	@uses			Framework_Neon_Models_Role
 *	@uses			Framework_Neon_Models_UserRole
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
class Framework_Neon_Actions_UserActions extends Framework_Neon_DefinitionAction
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loadLanguage( 'user' );
		$this->add( 'addUser' );
		$this->add( 'editUser' );
		$this->add( 'removeUser' );
		$this->add( 'addRole' );
		$this->add( 'removeRole' );
		$this->add( 'resetFilters' );
		$auth	= $this->ref->get( 'auth' );
		if( !$auth->hasRight( 'user', 'navigate' ) )
			$this->restart( "./" );
		$this->filterUsers();
	}

	/**
	 *	Adds a new User.
	 *	@access		protected
	 *	@return		void
	 */
	protected function addUser()
	{
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['msg'];
		if( $this->validateForm( 'user', 'addUser', 'user', 'add' ) )
		{
			$username	= $request->get( 'add_username' );
			$email		= $request->get( 'add_email' );
			$password	= $request->get( 'add_password' );
			$password2	= $request->get( 'add_confirm' );
			if( $roleId = $request->get( 'add_role' ) )
			{
				$user	= new Framework_Neon_Models_User();
				$users	= $user->getAllData();
				foreach( $users as $user )
				{
					if( strtolower( $user['username'] ) == strtolower( $username ) )
						return $this->messenger->noteError( $words['error_duplicate_username'], $username );
					if( strtolower( $user['email'] ) == strtolower( $email ) )
						return $this->messenger->noteError( $words['error_duplicate_email'], $email );
				}
				if( $password != $password2 )
					return $this->messenger->noteError( $words['error_password_mismatch'] );
				$data		= array(
					"username"	=> $username,
					"email"		=> $email,
					"password"	=> md5( $password ),
					"language"	=> $request->get( 'add_language' ),
					"notify"	=> $request->get( 'add_notify' ),
					);
				$user	= new Framework_Neon_Models_User();
				$userId	= $user->addData( $data );

				$data		= array(
					"userId"		=> $userId,
					"roleId"		=> $roleId,
					);
				$role	= new Framework_Neon_Models_UserRole();
				$role->addData( $data );
				$request->remove( 'add' );
				$this->messenger->noteSuccess( $words['success_added'], $username );
			}
		}
	}

	/**
	 *	Edits an existing User.
	 *	@access		protected
	 *	@return		void
	 */
	protected function editUser()
	{
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['msg'];
		if( $this->validateForm( 'user', 'editUser', 'user', 'edit' ) )
		{
			$userId		= $request->get( 'userId' );
			$username	= $request->get( 'edit_username' );
			$email		= $request->get( 'edit_email' );

			$user		= new Framework_Neon_Models_User();
			$user->focusPrimary( $userId );
			$userdata	= $user->getData( true );
			$users		= $user->getAllData();
			foreach( $users as $entry )
			{
				if( $entry['userId'] != $userId && strtolower( $entry['username'] ) == strtolower( $username ) )
					return $this->messenger->noteError( $words['error_duplicate_username'], $username );
				if( $entry['userId'] != $userId && strtolower( $entry['email'] ) == strtolower( $email ) )
					return $this->messenger->noteError( $words['error_duplicate_email'], $email );
			}
			$data		= array(
				"username"	=> $username,
				"email"		=> $email,
				"language"	=> $request->get( 'edit_language' ),
				"notify"	=> (int)$request->get( 'edit_notify' ),
				);
			$user->modifyData( $data );
			if( $password = $request->get( 'edit_password' ) )
			{
				$password2	= $request->get( 'edit_confirm' );
				if( $password != $password2 )
					return $this->messenger->noteError( $words['error_password_mismatch'] );
				$user->modifyData( array( "password"	=> md5( $password ) ) );
				$this->messenger->noteSuccess( $words['success_password_edited'], $userdata['username'] );
			}
			else
			{
				$user->modifyData( $data );
				$this->messenger->noteSuccess( $words['success_edited'], $userdata['username'] );
			}
			$request->remove( 'edit_username' );
			$request->remove( 'edit_email' );
			$request->remove( 'edit_password' );
			$request->remove( 'edit_confirm' );
			$request->remove( 'userId' );
		}
	}

	/**
	 *	Removes an existing User.
	 *	@access		protected
	 *	@return		void
	 */
	protected function removeUser()
	{
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['msg'];
		if( $userId = $request->get( 'userId' ) )
		{
			$role	= new Framework_Neon_Models_UserRole();
			$role->focusForeign( "userId", $userId );
			$roles	= $role->getData();
			$roles_has	= array();
			foreach( $roles as $role )
				$roles_has[]	= $role['roleId'];
			$request->set( "roles_has", $roles_has );
			$this->removeRole();

			$user	= new Framework_Neon_Models_User( $userId );
			$data	= $user->getData( true );
			$user->deleteData();
			$request->remove( 'userId' );
			$this->messenger->noteSuccess( $words['success_removed'], $data['username'] );
		}
	}

	/**
	 *	Adds a Role to a User.
	 *	@access		protected
	 *	@return		void
	 */
	protected function addRole()
	{
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['msg'];
		if( $userId = $request->get( 'userId' ) )
		{
			if( is_array( $roles = $request->get( 'roles' ) ) && count( $roles ) )
			{
				$user	= new Framework_Neon_Models_User();
				$user->focusPrimary( $userId );
				$user	= $user->getData( true );

				$role	= new Framework_Neon_Models_Role();
				$userrole	= new Framework_Neon_Models_UserRole();
				foreach( $roles as $roleId )
				{
					$role->focusPrimary( $roleId );
					$roledata	= $role->getData( true );
					$userrole->addData( array( "userId" => $userId, "roleId" => $roleId ) );
					$this->messenger->noteSuccess( $words['success_role_added'], $roledata['title'], $user['username'] );
				}
			}
		}
	}

	/**
	 *	Removes a Role from a User.
	 *	@access		protected
	 *	@return		void
	 */
	protected function removeRole()
	{
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['msg'];
		if( $userId = $request->get( 'userId' ) )
		{
			if( is_array( $roles = $request->get( 'roles_has' ) ) && count( $roles ) )
			{
				$user	= new Framework_Neon_Models_User();
				$user->focusPrimary( $userId );
				$user	= $user->getData( true );

				$role	= new Framework_Neon_Models_Role();
				$userrole	= new Framework_Neon_Models_UserRole();
				foreach( $roles as $roleId )
				{
					$role->focusPrimary( $roleId );
					$role	= $role->getData( true );
					$userrole->focusForeign( "userId", $userId );
					$userrole->focusForeign( "roleId", $roleId );
					$userrole->deleteData();
					$this->messenger->noteSuccess( $words['success_role_removed'], $role['title'], $user['username'] );
				}
			}
		}
	}

	/**
	 *	Sets current Filters in Session.
	 *	@access		protected
	 *	@return		void
	 */
	protected function filterUsers()
	{
		$config		= $this->ref->get( 'config' );
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );

		if( $request->get( 'filterUsers' ) )
		{
			$session->set( 'user_username', $request->get( 'filter_username' ) );
			$session->set( 'user_email', $request->get( 'filter_email' ) );
			$session->set( 'user_order', $request->get( 'filter_order' ) );
			$session->set( 'user_direction', $request->get( 'filter_direction' ) );
			$session->set( 'user_limit', $request->get( 'filter_limit' ) );
			$session->set( 'user_offset', 0 );
		}
		else
		{
			if( NULL !== ( $offset = $request->get( 'offset' ) ) )
				$session->set( 'user_offset', $offset );
			if( !$session->get( 'user_limit' ) )
				$session->set( 'user_limit', $config['layout']['list_limit'] );
		}

		if( !$session->get( 'user_limit' ) )
			$session->set( 'user_limit', $config['layout']['list_limit'] );
		if( $session->get( 'user_offset' ) < 0 )
			$session->set( 'user_offset', 0 );
	}

	/**
	 *	Resets Filters in Session and Request.
	 *	@access		protected
	 *	@return		void
	 */
	protected function resetFilters()
	{
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );
		$session->remove( 'user_username' );
		$session->remove( 'user_email' );
		$session->remove( 'user_order' );
		$session->remove( 'user_direction' );
		$session->remove( 'user_limit' );
		$session->remove( 'user_offset' );
		$request->remove( 'filter_username' );
		$request->remove( 'filter_email' );
		$request->remove( 'filter_order' );
		$request->remove( 'filter_direction' );
		$request->remove( 'filter_limit' );
		$request->remove( 'filter_offset' );
	}
}
?>