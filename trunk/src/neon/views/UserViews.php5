<?php
/**
 *	...
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
 *	@package		framework.neon.views
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
import( 'de.ceus-media.framework.neon.DefinitionView' );
import( 'de.ceus-media.database.StatementBuilder' );
import( 'de.ceus-media.framework.neon.models.UserStatements' );
import( 'de.ceus-media.framework.neon.models.User' );
import( 'de.ceus-media.framework.neon.models.Role' );
import( 'de.ceus-media.framework.neon.models.RightObject' );
import( 'de.ceus-media.framework.neon.models.RightAction' );
import( 'de.ceus-media.framework.neon.models.RightObjectAction' );
/**
 *	...
 *	@category		cmClasses
 *	@package		framework.neon.views
 *	@extends		Framework_Neon_DefinitionView
 *	@uses			Database_StatementBuilder
 *	@uses			Framework_Neon_Models_UserStatements
 *	@uses			Framework_Neon_Models_User
 *	@uses			Framework_Neon_Models_Role
 *	@uses			Framework_Neon_Models_RightObject
 *	@uses			Framework_Neon_Models_RightAction
 *	@uses			Framework_Neon_Models_RightObjectAction
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 *	@todo			Language Support of Messages
 *	@todo			Code Documentation
 */
class Framework_Neon_Views_UserViews extends Framework_Neon_DefinitionView
{
	public function buildContent()
	{
		$request	= $this->ref->get( 'request' );
		$auth	= $this->ref->get( "auth" );

		$link		= $request->get( 'link' );
		$content	= "";
		if( $userId = $request->get( 'userId' ) )
		{
			if( $auth->hasRight( 'user', 'edit' ) )
			{
				$content	.= $this->buildContentEdit();
				$content	.= $this->buildContentRole();
				$content	.= $this->buildContentRightField();
			}
		}
		else if( $request->has( 'add' ) )
		{
			if( $auth->hasRight( 'user', 'add' ) )
				return $this->buildContentAdd();
		}
		else
		{
			if( $auth->hasRight( 'user', 'list' ) )
				$content	.= $this->buildContentList();
		}
		return $content;
	}
	
	public function buildControl()
	{
		return $this->buildContentFilter();
	}

	/**
	 *	...
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildContentAdd()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['add'];

		$role	= new Framework_Neon_Models_Role();
		$roles	= $role->getAllData();
		$opt_role	= array();
		foreach( $roles as $role )
			$opt_role[$role['roleId']]	= $role['title'];
		krsort( $opt_role );
		$opt_role['_selected']	= $request->get( 'add_role' );

		$opt_language	= $this->words['main']['languages'];

		$sources	= array(
			'opt_role'	=> $opt_role,
			'opt_language'	=> $opt_language,
			);

		$ui	= $this->buildForm( 'user', 'addUser', 'user', 'add', array(), $sources );

		$button_cancel	= $this->html->LinkButton( $request->get( 'link' ).".html", $words['button_cancel'], 'but cancel' );
		$button_add		= $this->html->Button( 'addUser',			$words['button_add'], 'but add' );
		$ui['field_cancel']	= $this->html->Field( '', $button_cancel );
		$ui['field_button']		= $this->html->Field( '', $button_add );

		$ui['form']		= $this->html->Form( 'addUser', $request->get( 'link' ).".html;add" );
		$ui['colgroup']		= $this->html->ColumnGroup( $words['colgroup'] );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];

		return $this->loadTemplate( "user.add", $ui );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildContentEdit()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['edit'];
		if( $userId = $request->get( 'userId' ) )
		{
			$user	= new Framework_Neon_Models_User( $userId );
			$data	= $user->getData( true );
			$data['password']	= "";

			$opt_language	= $this->words['main']['languages'];
			$opt_language['_selected']	= $data['language'];

			$sources	= array(
				'opt_language'	=> $opt_language,
				);

			$ui	= $this->buildForm( 'user', 'editUser', 'user', 'edit', $data, $sources );
			$button_cancel	= $this->html->LinkButton( $request->get( 'link' ).".html", $words['button_cancel'], 'but cancel' );
			$button_edit	= $this->html->Button( 'editUser', $words['button_edit'], 'but edit' );
			$button_remove	= $this->html->Button( 'removeUser', $words['button_remove'], 'buthot remove', $words['button_remove_confirm'] );
			$ui['field_button_cancel']	= $this->html->Field( '', $button_cancel );
			$ui['field_buttons']		= $this->html->Field( '', $button_edit.$button_remove );
			$ui['form']		= $this->html->Form( 'editUser', $request->get( 'link' ).".html;userId,".$userId );
			$ui['colgroup']	= $this->html->ColumnGroup( $words['colgroup'] );
			$ui['caption']	= $this->html->TableCaption( $words['caption'] );
			$ui['heading']	= $words['heading'];
			return $this->loadTemplate( "user.edit", $ui );
		}
		else
			$this->messenger->noteError( $words['msg_error1'] );
	}

	protected function buildContentRole()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['roles'];

		if( $userId = $request->get( 'userId' ) )
		{
			$roles	= new Framework_Neon_Models_UserRole();
			$roles->focusForeign( "userId", $userId );
			$data	= $roles->getData();

			$_role	= new Framework_Neon_Models_Role();
			$all		= $_role->getAllData();

			$opt_roles		= array();
			$opt_roles_has	= array();
			foreach( $all as $role )
				$opt_roles[$role['roleId']] = $role['title'];

			foreach( $data as $role )
			{
				$_role->focusPrimary( $role['roleId'] );
				$role	= $_role->getData( true );
				$opt_roles_has[$role['roleId']]	= $role['title'];
				unset( $opt_roles[$role['roleId']] );
			}

			$inputs	= $this->buildInputs( 'user', 'editUserRoles', 'user', 'roles', array(), array( 'opt_roles' => $opt_roles, 'opt_roles_has' => $opt_roles_has ) );
			$ui		= $this->buildFields( 'user', 'editUserRoles', 'user', 'roles', $inputs );

			$button_add		= $this->html->Button( 'addRole', $words['button_role_add'], 'but add' );
			$button_remove	= $this->html->Button( 'removeRole', $words['button_role_remove'], 'buthot remove', $words['button_role_remove_confirm'] );

			$ui['field_button_add']		= $this->html->Field( '', $button_add );
			$ui['field_button_remove']	= $this->html->Field( '', $button_remove );

			$ui['form']		= $this->html->Form( 'editUserRole', $request->get( 'link' ).".html;userId,".$userId );
			$ui['colgroup']		= $this->html->ColumnGroup( $words['colgroup'] );
			$ui['caption']		= $this->html->TableCaption( $words['caption'] );
			$ui['heading']		= $words['heading'];

			return $this->loadTemplate( "user.roles", $ui );
		}
		else
			$this->messenger->noteError( $words['msg_error1'] );
	}

	/**
	 *	Build View for Author Search.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildContentFilter()
	{
		$config		= $this->ref->get( 'config' );
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['filter'];

		$user	= new Framework_Neon_Models_User();
		$fieldList	= $user->getFields();
		$opt_order	= array( "" => $words ['order_none'] );
		foreach( $fieldList as $field )
			if( isset( $words['order_'.$field] ) )
				$opt_order[$field]	= $words['order_'.$field];
		if( $select = $session->get( 'user_order' ) )
			$opt_order['_selected']	= $select;

		$opt_direction	= array(
			"ASC"	=> $words['direction_asc'],
			"DESC"	=> $words['direction_desc']
			);
		if( $select = $session->get( 'user_direction' ) )
			$opt_direction['_selected']	= $select;
			
		$values	= array(
			'username'	=> $session->get( 'user_username' ),
			'email'		=> $session->get( 'user_email' ),
			'limit'			=> $session->get( 'user_limit' ),
			);
		$sources	= array(
			'opt_order'	=> $opt_order,
			'opt_direction'	=> $opt_direction,
			);

		$ui	= $this->buildForm( 'user', 'filterUsers', 'user', 'filter', $values, $sources );

		$button_filter	= $this->html->Button( 'filterUsers', $words['button_filter'], 'but filter' );
		$button_reset	= $this->html->LinkButton( $request->get( 'link' ).".html;resetFilters", $words['button_reset'], 'but reset' );
		$ui['field_button_filter']	= $this->html->Field( 'filterUsers', $button_filter );
		$ui['field_button_reset']	= $this->html->Field( 'resetFilters', $button_reset );

		$ui['form']		= $this->html->Form( 'filterUsers', $request->get( 'link' ).".html", "post" );
		$ui['colgroup']		= $this->html->ColumnGroup( $words['colgroup'] );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];

		return $this->loadTemplate( "user.filter", $ui );
	}

	/**
	 *	Build View for Author List.
	 *	@access		protected
	 *	@return		string
	 */
	protected function buildContentList()
	{
		$config		= $this->ref->get( 'config' );
		$dbc		= $this->ref->get( 'dbc' );
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );
		$auth		= $this->ref->get( 'auth' );
		$words		= $this->words['user']['list'];

		$sb	= new Database_StatementBuilder( $config['config']['table_prefix'] );
		$us	= new Framework_Neon_Models_UserStatements( $sb );
		$us->setOffset( $session->get( 'user_offset' ) );
		$us->setLimit( $session->get( 'user_limit' ) );
		if( $session->get( 'user_order' ) )
			$us->orderBy( $session->get( 'user_order'), $session->get( 'user_direction' ) );
		if( $session->get( 'user_username' ) )
			$us->withUsername( $session->get( 'user_username' ) );
		if( $session->get( 'user_email' ) )
			$us->withEmail( $session->get( 'user_email' ) );

		$query	= $sb->buildCountStatement();
		$data	= $dbc->execute( $query );
		$row	= $data->fetchArray();

		$query	= $sb->buildSelectStatement();
		$data	= $dbc->execute( $query );
		$ui['paging']	= "";
		if( $data->recordCount() )
		{
			$tc		= new Alg_TimeConverter;
			if( $row['rowcount'] > $session->get( 'user_limit' ) )
			{
				$pages	= $this->buildPaging( $row['rowcount'], $session->get( 'user_limit' ), $session->get( 'user_offset') );
				$ui['paging']	= $this->loadTemplate( "paging", array( 'pages' => $pages ) );
			}

			$user_editor	= $auth->hasRight( 'user', 'edit' );
			$role_editor	= $auth->hasRight( 'role', 'edit' );
			$icon_check	= $this->html->Image( $config['paths']['themes'].$config['layout']['theme']."/images/icons/check.gif", $words['alt_icon_check'] );
			$icon_cross	= $this->html->Image( $config['paths']['themes'].$config['layout']['theme']."/images/icons/cross.gif", $words['alt_icon_cross'] );

			$i = 0;
			$userdata	= new Framework_Neon_Models_User();
			while( $user	= $data->fetchNextArray( false ) )
			{
				$style	= ++$i % 2 ? 'list1' : 'list2';
				$title	= htmlspecialchars( $this->str_shorten( $user['username'], $config['layout']['shorten_content'] ) );
				$email	= $this->str_shorten( $user['email'], $config['layout']['shorten_content'] );
			//	$time	= $tc->convertToHuman( $user['timestamp'], $config['layout']['format_timestamp'] );
				$notify	= $user['notify'] ? $icon_check : $icon_cross;

				$rolelist		= array();
				$roles	= $userdata->getRolesFromUID( $user['userId'] );
				foreach( $roles as $roleId => $roleTitle )
				{
					if( $role_editor )
						$rolelist[]	= $this->html->Link( "role.html;roleId,".$roleId, $roleTitle );
					else
						$rolelist[]	= $roleTitle;
				}
				$roles	= implode( ", ", $rolelist );
				if( $user_editor )
					$link		= $this->html->Link( $request->get( 'link' ).".html;userId,".$user['userId'], $title );
				else
					$link		= $title;
				$list[]	= "<tr class='".$style."'><td>".$link."</td><td>".$email."</td><td>".$roles."</td><td>".$notify."</td></tr>";
			}
			$ui['list'] = implode( "\n        ", $list );
		}
		else
			$ui['list']			= "<tr><td colspan='4'>".$words['no_entries']."</td></tr>";

		$ui['link_add']		= "";
		if( $auth->hasRight( 'user', 'add' ) )
			$ui['link_add']		= $this->html->Link( $request->get( 'link' ).".html;add", $words['link_add'] );

		$heads	= array(
			$words['head_username'],
			$words['head_email'],
			$words['head_role'],
			$words['head_notify'],
		);
		$ui['heads']		= $this->html->TableHeads( $heads );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['colgroup']		= $this->html->ColumnGroup( $words['colgroup'] );	
		$ui['heading']		= $words['heading'];

		return $this->loadTemplate( "user.list", $ui );
	}
	
	protected function buildContentRightField()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['user']['rights'];

		if( $userId	= $request->get( 'userId' ) )
		{
			$ui['colgroup']	= "";
			$object	=  new Framework_Neon_Models_RightObject();
			$action	=  new Framework_Neon_Models_RightAction();
			$objects	= $object->getObjects();
			$actions	= $action->getActions();
			if( count( $objects ) )
			{
				if( count( $actions ) )
				{
					$user	= new Framework_Neon_Models_User();
					$relation	= new Framework_Neon_Models_RightObjectAction();
					$icon_check	= $this->html->Image( $config['paths']['themes'].$config['layout']['theme']."/images/icons/check.gif", $words['alt_right_check'] );
					$icon_cross	= $this->html->Image( $config['paths']['themes'].$config['layout']['theme']."/images/icons/cross.gif", $words['alt_right_cross'] );
					$i = 0;
					foreach( $objects as $objectId => $object )
					{
						$object	= new Framework_Neon_Models_RightObject( $objectId );
						$object	= $object->getData( true );
						$object_rights	= array();
						foreach( $actions as $actionId => $action )
						{
							if( $relation->isObjectActionByID( $objectId, $actionId ) )
							{
								$value	= $user->hasRightByID( $userId, $objectId, $actionId );
								$input	= $value ? $icon_check : $icon_cross;
							}
							else
								$input	= $words['no_relation'];
							$object_rights[]	= $this->html->Field( '', $input );			
						}
						$style	= ++$i % 2 ? 'list1' : 'list2';
						if( $object['description'] )
							$object['title']	= $this->html->Acronym( $object['title'], $object['description'] );
						$link		= $this->html->Link( "right_object.html;rightObjectId,".$object['rightObjectId'], $object['title'] );
						$label	= $this->html->Label( '', $link );
						$rights[]	= "<tr class='".$style."'>".$label.implode( "", $object_rights )."</tr>";
					}
					$cols	= floor( 80 / count( $actions ) );
					$cols	= implode( ", ", array_fill( 0, count( $actions ), "'".$cols."%'" ) );	
					$ui['rights']	= implode( "\n\t", $rights );	
					$ui['colgroup']	= eval( "return $"."this->html->ColumnGroup( '20%', ".$cols." );" );
				}
				else
					$ui['rights']	= "<tr><td>".$words['no_actions']."</td></tr>";
			}
			else
				$ui['rights']	= "<tr><td>".$words['no_objects']."</td></tr>";

			$heads	= array( $words['right_legend'] );
			foreach( $actions as $actionId => $action )
			{
				$action	= new Framework_Neon_Models_RightAction( $actionId );
				$action	= $action->getData( true );
				if( $action['description'] )
					$action['title']	= $this->html->Acronym( $action['title'], $action['description'] );
				$heads[]	= $action['title'];
			}
			$ui['heads']		= $this->html->TableHeads( $heads );
			$ui['caption']		= $this->html->TableCaption( $words['caption'] );
			$ui['heading']		= $words['heading'];
			
			return $this->loadTemplate( "user.rights", $ui );
		}
		else
			$this->messenger->noteError( $words['msg_error1'] );
	}
}
?>