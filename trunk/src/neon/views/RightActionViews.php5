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
import( 'de.ceus-media.framework.neon.models.RightAction' );
/**
 *	...
 *	@category		cmClasses
 *	@package		framework.neon.views
 *	@extends		Framework_Neon_DefinitionView
 *	@uses			Framework_Neon_Models_RightAction
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 *	@todo			Language Support of Messages
 *	@todo			Code Documentation
 */
class Framework_Neon_Views_RightActionViews extends Framework_Neon_DefinitionView
{
	public function buildContent()
	{
		$request	= $this->ref->get( "request" );
		$auth		= $this->ref->get( 'auth' );

		$content	= "";
		if( $rightActionId	= $request->get( 'rightActionId' ) )
		{
			if( $auth->hasRight( 'right_action', 'edit' ) )
				$content	.= $this->buildContentEdit();
		}
		else
		{
			if( $auth->hasRight( 'right_action', 'list' ) )
				$content	.= $this->buildContentList();
			if( $auth->hasRight( 'right_action', 'add' ) )
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
		$words		= $this->words['right_action']['add'];

		$ui		= $this->buildForm( 'right_action', 'addAction', 'right_action', 'add' );

		$button_add		= $this->html->Button( 'addAction', $words['button_add'], 'but add' );
		$ui['field_button']	= $this->html->Field( '', $button_add );

		$ui['form']		= $this->html->Form( 'addAction', $request->get( 'link' ).".html" );
		$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];

		return $this->loadTemplate( "right.action.add", $ui );
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
		$words		= $this->words['right_action']['edit'];

		if( $rightActionId = $request->get( 'rightActionId' ) )
		{
			$action	= new Framework_Neon_Models_RightAction( $rightActionId );
			$data	= $action->getData( true );

			$ui		= $this->buildForm( 'right_action', 'editAction', 'right_action', 'edit', $data );

			$button_cancel	= $this->html->LinkButton( $request->get( 'link' ).".html", $words['button_cancel'], 'but cancel' );
			$button_edit	= $this->html->Button( 'editAction', $words['button_edit'], 'but edit' );
			$button_remove	= $this->html->Button( 'removeAction', $words['button_remove'], 'buthot', $words['button_remove_confirm'] );
			$ui['field_button_cancel']	= $this->html->Field( '', $button_cancel );
			$ui['field_buttons']		= $this->html->Field( '', $button_edit.$button_remove );

			$ui['form']		= $this->html->Form( 'editAction', $request->get( 'link' ).".html;rightActionId,".$request->get( 'rightActionId' ) );
			$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
			$ui['caption']		= $this->html->TableCaption( $words['caption'] );
			$ui['heading']		= $words['heading'];

			return $this->loadTemplate( "right.action.edit", $ui );
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
		$words		= $this->words['right_action']['list'];

		$editor	= $auth->hasRight( 'right_action', 'edit' );

		$list		= array();
		$action		= new Framework_Neon_Models_RightAction();
		$actions	= $action->getAllData();
		if( count( $actions ) )
		{
			$i = 0;
			foreach( $actions as $action )
			{
				$label_desc	= $this->html->Label( '', $action['description'] );
				$style	= ++$i % 2 ? 'list1' : 'list2';
				if( $editor )
				{
					$link			= $this->html->Link( $request->get( 'link' ).".html;rightActionId,".$action['rightActionId'], $action['title'] );
					$label_link	= $this->html->Label( '', $link );
				}
				else
					$label_link	= $this->html->Label( '', $action['title'] );
				$list[]	= "<tr class='".$style."'>".$label_link.$label_desc."</tr>";
			}
			$ui['actions']	= implode( "", $list);
		}
		else
			$ui['actions']	= "<tr>".$this->html->Label( '', $words['no_entries'] )."</tr>";

		$heads	= array(
			$words['head_title'],
			$words['head_description'],
		);
		$ui['heads']		= $this->html->TableHeads( $heads );
		$ui['colgroup']	= $this->html->ColumnGroup( "30%", "70%" );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['heading']		= $words['heading'];

		return $this->loadTemplate( "right.action.list", $ui );
	}
}
?>