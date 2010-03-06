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
 *	@uses			Framework_Neon_Models_RoleObject
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
class Framework_Neon_Views_RightObjectViews extends Framework_Neon_DefinitionView
{
	public function buildContent()
	{
		$request	= $this->ref->get( "request" );
		$auth	= $this->ref->get( 'auth' );

		$content	= "";
		if( $rightObjectId	= $request->get( 'rightObjectId' ) )
		{
			if( $auth->hasRight( 'right_object', 'edit' ) )
				$content	.= $this->buildContentEdit();
			if( $auth->hasRight( 'right_object', 'edit' ) )
				$content	.= $this->buildContentActions();
		}
		else
		{
			if( $auth->hasRight( 'right_object', 'list' ) )
				$content	.= $this->buildContentList();
			if( $auth->hasRight( 'right_object', 'add' ) )
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
		$words		= $this->words['right_object']['add'];

		$ui	= $this->buildForm( 'right_object', 'addObject', 'right_object', 'add' );

		$button_add		= $this->html->Button( 'addObject', $words['button_add'], 'but add' );
		$ui['field_button']			= $this->html->Field( '', $button_add );

		$ui['form']		= $this->html->Form( 'addObject', $request->get( 'link' ).".html" );
		$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];
		
		return $this->loadTemplate( "right.object.add", $ui );
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
		$words		= $this->words['right_object']['edit'];

		if( $rightObjectId = $request->get( 'rightObjectId' ) )
		{
			$object	= new Framework_Neon_Models_RightObject( $rightObjectId );
			$data	= $object->getData( true );

			$ui	= $this->buildForm( 'right_object', 'editObject', 'right_object', 'edit', $data );

			$button_cancel	= $this->html->LinkButton( $request->get( 'link' ).".html", $words['button_cancel'], 'but cancel' );
			$button_edit		= $this->html->Button( 'editObject', $words['button_edit'], 'but edit' );
			$button_remove	= $this->html->Button( 'removeObject', $words['button_remove'], 'buthot', $words['button_remove_confirm'] );
			$ui['field_button_cancel']	= $this->html->Field( '', $button_cancel );
			$ui['field_buttons']		= $this->html->Field( '', $button_edit.$button_remove );

			$ui['form']		= $this->html->Form( 'editObject', $request->get( 'link' ).".html;rightObjectId,".$request->get( 'rightObjectId' ) );
			$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
			$ui['caption']		= $this->html->TableCaption( $words['caption'] );
			$ui['heading']		= $words['heading'];

			return $this->loadTemplate( "right.object.edit", $ui );
		}
		else
			$this->messenger->noteError( $words['msg']['error1'] );
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
		$words		= $this->words['right_object']['list'];

		$editor	= $auth->hasRight( 'right_object', 'edit' );

		$list		= array();
		$object		= new Framework_Neon_Models_RightObject();
		$objects	= $object->getAllData();
		if( count( $objects ) )
		{
			$i = 0;
			foreach( $objects as $object )
			{
				$label_desc	= $this->html->Label( '', $object['description'] );
				$style	= ++$i % 2 ? 'list1' : 'list2';
				if( $editor )
				{
					$link			= $this->html->Link( $request->get( 'link' ).".html;rightObjectId,".$object['rightObjectId'], $object['title'] );
					$label_link	= $this->html->Label( '', $link );
				}
				else
					$label_link	= $this->html->Label( '', $object['title'] );
				$list[]	= "<tr class='".$style."'>".$label_link.$label_desc."</tr>";
			}
			$ui['objects']	= implode( "", $list);
		}
		else
			$ui['objects']	= "<tr>".$this->html->Label( '', $words['no_entries'] )."</tr>";

		$heads	= array(
			$words['head_title'],
			$words['head_description'],
		);
		$ui['heads']		= $this->html->TableHeads( $heads );
		$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];

		return $this->loadTemplate( "right.object.list", $ui );
	}
	
	private function buildContentActions()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$auth		= $this->ref->get( 'auth' );
		$words		= $this->words['right_object']['actions'];
	
		if( $rightObjectId = $request->get( 'rightObjectId' ) )
		{
			
			$relation	= new Framework_Neon_Models_RightObjectAction();

			$action		= new Framework_Neon_Models_RightAction();
			$actions	= $action->getAllData();
			
			$opt_actions		= array();
			$opt_actions_has	= array();
			foreach( $actions as $action )
			{
				if( $relation->isObjectActionByID( $rightObjectId, $action['rightActionId'] ) )
					$opt_actions_has[$action['rightActionId']]	= $action['title'];
				else
					$opt_actions[$action['rightActionId']]	= $action['title'];
			}

			$sources	= array(
				'opt_actions'		=> $opt_actions,
				'opt_actions_has'	=> $opt_actions_has,
				);
			$ui	= $this->buildForm( 'right_object', 'editObjectActions', 'right_object', 'actions', array(), $sources );

			$button_add		= $this->html->Button( 'addActions', $words['button_add'], 'but add' );
			$button_remove	= $this->html->Button( 'removeActions', $words['button_remove'], 'buthot remove', $words['button_remove_confirm'] );

			$ui['field_button_add']		= $this->html->Field( '', $button_add );
			$ui['field_button_remove']	= $this->html->Field( '', $button_remove );

			$ui['form']		= $this->html->Form( 'editObjectActions', $request->get( 'link' ).".html;rightObjectId,".$rightObjectId );
			$ui['colgroup']		= $this->html->ColumnGroup( "20%", "40%", "40%" );
			$ui['caption']		= $this->html->TableCaption( $words['caption'] );
			$ui['heading']		= $words['heading'];

			return $this->loadTemplate( "right.object.actions", $ui );
		}
		else
			$this->messenger->noteError( $words['msg']['error1'] );
	}
}
?>
