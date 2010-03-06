<?php
/**
 *	Actions for Right Objects.
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
import( 'de.ceus-media.framework.neon.models.RightObject' );
import( 'de.ceus-media.framework.neon.models.RightObjectAction' );
/**
 *	Actions for Right Objects.
 *	@category		cmClasses
 *	@package		framework.neon.actions
 *	@extends		Framework_Neon_DefinitionAction
 *	@uses			Framework_Neon_Models_RightObject
 *	@uses			Framework_Neon_Models_RightObjectAction
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
class Framework_Neon_Actions_RightObjectActions extends Framework_Neon_DefinitionAction
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loadLanguage( 'right_object' );
	}
	
	/**
	 *	Handles Actions.
	 *	@access		public
	 *	@return		void
	 */
	public function act()
	{
		$config		= $this->ref->get( "config" );
		$request	= $this->ref->get( "request" );
		$auth		= $this->ref->get( 'auth' );

		if( !$auth->isAuthenticated() )
			$this->restart( ";" );

		if( $request->get( 'rightObjectId' ) )
		{
			if( $request->get( 'editObject' ) )
				$this->editObject();
			else if( $request->get( 'removeObject' ) )
				$this->removeObject();
			else if( $request->get( 'addActions' ) )
				$this->addActions();
			else if( $request->get( 'removeActions' ) )
				$this->removeActions();
		}
		else if( $request->get( 'addObject' ) )
			$this->addObject();
	}

	/**
	 *	Adds a Right Object.
	 *	@access		private
	 *	@return		void
	 */
	private function addObject()
	{
		$request	=& $this->ref->get( 'request' );
		if( $this->validateForm( 'right_object', 'addObject', 'right_object', 'add' ) )
		{
			$title = $request->get( 'add_title' );
			$object	= new Framework_Neon_Models_RightObject();
			$objects	= $object->getAllData();
			foreach( $objects as $object )
				if( strtolower( $object['title'] ) == strtolower( $title ) )
					return $this->messenger->noteError( $this->words['right_object']['msg']['error3'], $title );
			$object	= new Framework_Neon_Models_RightObject();
			$data	= array(
				"title"		=> $title,
				"description"	=> $request->get( 'add_description' ),
				);
			$roid	= $object->addData( $data );
			$request->remove( 'add_title' );
			$request->remove( 'add_description' );
			$this->messenger->noteSuccess( $this->words['right_object']['msg']['success1'], $title );
		}
	}

	/**
	 *	Edits a Right Object.
	 *	@access		private
	 *	@return		void
	 */
	private function editObject()
	{
		$request	=& $this->ref->get( 'request' );
		if( $rightObjectId = $request->get( 'rightObjectId' ) )
		{
			if( $this->validateForm( 'right_object', 'editObject', 'right_object', 'edit' ) )
			{
				$title = $request->get( 'edit_title' );
				$object	= new Framework_Neon_Models_RightObject();
				$objects	= $object->getAllData();
				foreach( $objects as $object )
					if( $object['rightObjectId'] != $rightObjectId && strtolower( $object['title'] ) == strtolower( $title ) )
						return $this->messenger->noteError( $this->words['right_object']['msg']['error3'], $title );
				$data	= array(
					"title"		=> $title,
					"description"	=> $request->get( 'edit_description' ),
					);
				$object	= new Framework_Neon_Models_RightObject( $rightObjectId );
				$object->modifyData( $data );
				$this->messenger->noteSuccess( $this->words['right_object']['msg']['success2'], $title );
			}
		}
		else
			$this->messenger->noteError( $this->words['right_object']['msg']['error1'] );
	}

	/**
	 *	Removes a Right Object.
	 *	@access		private
	 *	@return		void
	 *	@todo		finish Implementation
	 */
	private function removeObject()
	{
		$request	=& $this->ref->get( 'request' );
		if( $rightObjectId = $request->get( 'rightObjectId' ) )
		{
			$object	= new Framework_Neon_Models_RightObject( $rightObjectId );
			$data	= $object->getData( true );
			$object->deleteData();
			$request->remove( 'rightObjectId' );
			$this->messenger->noteSuccess( $this->words['right_object']['msg']['success3'], $data['title'] );
		}
	}

	/**
	 *	Adds a Right Actions to a Right Object.
	 *	@access		private
	 *	@return		void
	 */
	private function addActions()
	{
		$request	=& $this->ref->get( 'request' );
		if( $rightObjectId = $request->get( 'rightObjectId' ) )
		{
			$relation	= new Framework_Neon_Models_RightObjectAction();
			$actions	= (array)$request->get( 'actions' );
			foreach( $actions as $action )
			{
				$data	= array(
					"rightObjectId"	=> $rightObjectId,
					"rightActionId"	=> $action,
				);
				$relation->addData( $data );
			}
		}
		else
			$this->messenger->noteError( $this->words['right_object']['msg']['error1'] );
	}

	/**
	 *	Removes Right Actions from a Right Object.
	 *	@access		private
	 *	@return		void
	 */
	private function removeActions()
	{
		$request	= $this->ref->get( 'request' );
		if( $rightObjectId = $request->get( 'rightObjectId' ) )
		{
			$relation	= new Framework_Neon_Models_RightObjectAction();
			$relation->focusForeign( "rightObjectId", $rightObjectId );
			$actions	= (array)$request->get( 'actions_has' );
			foreach( $actions as $action )
			{
				$relation->focusForeign( "rightActionId", $action );
				$relation->deleteData();
			}
		}
		else
			$this->messenger->noteError( $this->words['right_object']['msg']['error1'] );
	}
}
?>