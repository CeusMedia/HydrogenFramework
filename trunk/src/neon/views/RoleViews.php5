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
import( 'de.ceus-media.framework.neon.models.RightObject' );
import( 'de.ceus-media.framework.neon.models.RightAction' );
import( 'de.ceus-media.framework.neon.models.RightObjectAction' );
/**
 *	...
 *	@category		cmClasses
 *	@package		framework.neon.views
 *	@extends		Framework_Neon_DefinitionView
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
class Framework_Neon_Views_RoleViews extends Framework_Neon_DefinitionView
{
	public function buildContent()
	{
		$request	= $this->ref->get( "request" );
		$auth	= $this->ref->get( "auth" );

		$content	= "";
		if( $roleId	= $request->get( 'roleId' ) )
		{
			if( $auth->hasRight( 'role', 'edit' ) )
			{
				$content	.= $this->buildContentEdit();
				$content	.= $this->buildContentRightField();
			}
		}
		else
		{
			if( $auth->hasRight( 'role', 'list' ) )
				$content	= $this->buildContentList();
			if( $auth->hasRight( 'role', 'add' ) )
				$content	.= $this->buildContentAdd();
		}
		return $content;
		
	}

	/**
	 *	...
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentAdd()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['role']['add'];

		$ui	= $this->buildForm( 'role', 'addRole', 'role', 'add' );

		$button_add		= $this->html->Button( 'addRole', $words['button_add'], 'but add' );
		$ui['field_button']	= $this->html->Field( '', $button_add );
		
		$ui['form']		= $this->html->Form( 'addRole', $request->get( 'link' ).".html" );
		$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];
		
		return $this->loadTemplate( "role.add", $ui );
	}

	/**
	 *	...
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentEdit()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['role']['edit'];

		if( $roleId = $request->get( 'roleId' ) )
		{
			$role	= new Framework_Neon_Models_Role( $roleId );
			$data	= $role->getData( true );

			$ui	= $this->buildForm( 'role', 'editRole', 'role', 'edit', $data );

			$button_cancel	= $this->html->LinkButton( $request->get( 'link' ).".html", $words['button_cancel'], 'but cancel' );
			$button_edit	= $this->html->Button( 'editRole', $words['button_edit'], 'but edit' );
			$button_remove	= $this->html->Button( 'removeRole', $words['button_remove'], 'buthot', $words['button_remove_confirm'] );
			$ui['field_button_cancel']	= $this->html->Field( '', $button_cancel );
			$ui['field_buttons']		= $this->html->Field( '', $button_edit.$button_remove );

			$ui['form']		= $this->html->Form( 'editRole', $request->get( 'link' ).".html;roleId,".$request->get( 'roleId' ) );
			$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
			$ui['caption']		= $this->html->TableCaption( $words['caption'] );
			$ui['heading']		= $words['heading'];
		
			return $this->loadTemplate( "role.edit", $ui );
		}
		else
			$this->messenger->noteError( $words['msg_error1'] );
	}

	/**
	 *	...
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentList()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$auth		= $this->ref->get( 'auth' );
		$words		= $this->words['role']['list'];

		$editor	= $auth->hasRight( 'role', 'edit' );

		$list	= array();
		$role	= new Framework_Neon_Models_Role();
		$roles	= $role->getAllData();
		$i = 0;
		foreach( $roles as $role )
		{
			$style	= ++$i % 2 ? 'list1' : 'list2';
			$label_desc	= $this->html->Label( '', $role['description'] );
			if( $editor )
			{
				$link			= $this->html->Link( $request->get( 'link' ).".html;roleId,".$role['roleId'], $role['title'] );
				$label_link	= $this->html->Label( '', $link );
			}
			else
				$label_link	= $this->html->Label( '', $role['title'] );
			$list[]	= "<tr class='".$style."'>".$label_link.$label_desc."</tr>";
		}
		$ui['roles']	= implode( "\n\t", $list);

		$heads	= array(
			$words['head_title'],
			$words['head_description'],
		);
		$ui['heads']		= $this->html->TableHeads( $heads );
		$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];

		return $this->loadTemplate( "role.list", $ui );
	}

	/**
	 *	...
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentRightField()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['role']['rights'];

		if( $roleId	= $request->get( 'roleId' ) )
		{
			$ui['colgroup']	= "";
			$ui['field_button']	= "";
			$object	=  new Framework_Neon_Models_RightObject();
			$action	=  new Framework_Neon_Models_RightAction();
			$objects	= $object->getObjects();
			$actions	= $action->getActions();
			if( count( $objects ) )
			{
				if( count( $actions ) )
				{
					$roleright	= new Framework_Neon_Models_RoleRight();
					$relation	= new Framework_Neon_Models_RightObjectAction();
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
								$value	= $roleright->hasRightByID( $roleId, $objectId, $actionId );
								$checked	= $value ? " checked='checked'" : "";
								$input	= "<input name='right[".$objectId."][".$actionId."]' type='checkbox'".$checked.">";
							}
							else
							{
								$input	= $words['no_relation'];
							}
							$object_rights[]	= $this->html->Field( '', $input );
						}
						$style	= ++$i % 2 ? 'list1' : 'list2';
						if( $object['description'] )
							$object['title']	= $this->html->Acronym( $object['title'], $object['description'] );
						$link		= $this->html->Link( "right_object.html;rightObjectId,".$objectId, $object['title'] );
						$label	= $this->html->Label( '', $link );
						$rights[]	= "<tr class='".$style."'>".$label.implode( "", $object_rights )."</tr>";
					}
					$ui['rights']	= implode( "\n\t", $rights );	
			
					$input_button	= $this->html->Button( 'saveRights', $words['button'], 'but edit' );
					$ui['field_button']	= $this->html->Field( '', $input_button, '', '', count( $actions ) );
			
					$cols		= floor( 80 / count( $actions ) );
					$cols		= implode( ", ", array_fill( 0, count( $actions ), "'".$cols."%'" ) );	
					$ui['colgroup']	= eval( "return $"."this->html->ColumnGroup( '20%', ".$cols." );" );
				}
				else
					$ui['rights']	= "<tr><td>".$words['no_actions']."</td></tr>";
			}
			else
				$ui['rights']	= "<tr><td>".$words['no_objects']."</td></tr>";
			$heads	= array( $words['right_object'] );
			foreach( $actions as $actionId => $action )
			{
				$action	= new Framework_Neon_Models_RightAction( $actionId );
				$action	= $action->getData( true );
				if( $action['description'] )
					$action['title']	= $this->html->Acronym( $action['title'], $action['description'] );
				$heads[]	= $action['title'];
			}
			$ui['heads']		= $this->html->TableHeads( $heads );
			$ui['form']		= $this->html->Form( 'addRight', $request->get( 'link' ).".html;roleId,".$request->get( 'roleId' ) );
			$ui['caption']		= $this->html->TableCaption( $words['caption'] );
			$ui['heading']		= $words['heading'];

			return $this->loadTemplate( "role.rights", $ui );
		}
		else
			$this->messenger->noteError( $words['msg_error1'] );
	}
}
?>