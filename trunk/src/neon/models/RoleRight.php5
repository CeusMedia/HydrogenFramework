<?php
/**
 *	Data Model of Role Rights.
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
 *	@package		framework.neon.models
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
import( 'de.ceus-media.framework.neon.Model' );
import( 'de.ceus-media.framework.neon.models.RightObject' );
import( 'de.ceus-media.framework.neon.models.RightAction' );
/**
 *	Data Model of Role ights.
 *	@category		cmClasses
 *	@package		framework.neon.models
 *	@extends		Framework_Neon_Model
 *	@uses			RightObject
 *	@uses			RightAction
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
class Framework_Neon_Models_RoleRight extends Framework_Neon_Model
{
	var $_right_cache = array();
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $roleRightId = false )
	{
		$tableName	= "rolerights";
		$fieldList	= array (
			"roleRightId",
			"roleId",
			"rightObjectId",
			"rightActionId",
			);
		$primaryKey	= "roleRightId";
		$foreignKeys	= array( "roleId", "rightObjectId", "rightActionId" );

		parent::__construct( $tableName, $fieldList, $primaryKey, $roleRightId );
		$this->setForeignKeys ( $foreignKeys );

		$this->_rightobject	= new Framework_Neon_Models_RightObject();
		$this->_rightaction	= new Framework_Neon_Models_RightAction();
	}

	/**
	 *	Returns Array of Right Object IDs and Action IDs.
	 *	@access		public
	 *	@return		array
	 */
	public function getRightIDs()
	{
		$list	= $this->getAllData();
		$objectactions	= array();
		foreach( $list as $entry )
		{
			$objectactions[]	= array( $entry['rightObjectId'], $entry['rightActionId'] );
		}
		return $objectactions;
	}
	
	/**
	 *	Returns Array of Right Object Names and Action Names.
	 *	@access		public
	 *	@return		array
	 */
	public function getRights()
	{
		$list	= $this->getAllData();
		$objectactions	= array();
		foreach( $list as $entry )
		{
			$object	= $this->_rightobject->getObject( $entry['rightObjectId'] );
			$action	= $this->_rightaction->getAction( $entry['rightActionId'] );
			$objectactions[$entry['rightId']]	= array( $object, $action );
		}
		return $objectactions;
	}

	public function getRoleRights( $roleId )
	{
		$this->focusForeign( "roleId", $roleId );
		$list	= $this->getData();
		return $list;
	}
	
	/**
	 *	Indicates whether Right Object and Action is set to a Role by their IDs.
	 *	@access		public
	 *	@param		int			roleId			Role ID
	 *	@param		int			rightObjectId		Right Object ID
	 *	@param		int			rightActionId		Right Action ID
	 *	@return		bool
	 */
	public function hasRightByID( $roleId, $rightObjectId, $rightActionId )
	{
		$this->defocus();
		$this->focusForeign( "roleId", $roleId );
		$this->focusForeign( "rightObjectId", $rightObjectId );
		$this->focusForeign( "rightActionId", $rightActionId );
		$data	= $this->getData();
		if( count( $data ) != 0 )
			return true;
		return false;
	}
	
	/**
	 *	Indicates whether Right Object and Action is set to a Role.
	 *	@access		public
	 *	@param		int			roleId			Role ID
	 *	@param		string		rightobject		Right Object Name
	 *	@param		string		rightaction		Right Action Name
	 *	@return		bool
	 */
	public function hasRight( $roleId, $rightobject, $rightaction )
	{
		$object	= $this->_rightobject->getObjectID( $rightobject );
		$action	= $this->_rightaction->getActionID( $rightaction );
		if( $object && $action )
			return $this->hasRightByID( $roleId, $object, $action );
		return false;
	}
}
?>
