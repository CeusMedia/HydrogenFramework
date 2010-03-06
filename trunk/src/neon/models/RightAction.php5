<?php
/**
 *	Data Model of Right Actions.
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
/**
 *	Data Model of Right Actions.
 *	@category		cmClasses
 *	@package		framework.neon.models
 *	@extends		Framework_Neon_Model
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.01.2007
 *	@version		0.2
 */
class Framework_Neon_Models_RightAction extends Framework_Neon_Model
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $rightActionId = false )
	{
		$tableName	= "rightactions";
		$fieldList	= array (
			"rightActionId",
			"title",
			"description",
			);
		$primaryKey	= "rightActionId";
		$foreignKeys	= array( "title" );

		parent::__construct( $tableName, $fieldList, $primaryKey, $rightActionId );
		$this->setForeignKeys ( $foreignKeys );
	}

	/**
	 *	Returns Array of Right Actions.
	 *	@access		public
	 *	@return		array
	 */
	public function getActions()
	{
		$list	= $this->getAllData();
		$actions	= array();
		foreach( $list as $entry )
			$actions[$entry['rightActionId']]	= $entry['title'];
		return $actions;
	}

	/**
	 *	Returns Action Name of Action ID.
	 *	@access		public
	 *	@param		int			rightActionId		Action ID
	 *	@return		string
	 */
	public function getAction( $rightActionId )
	{
		$this->focusPrimary( $rightActionId );
		$data	= $this->getData( true );
		$this->defocus();
		if( isset( $data['title'] ) )
			return $data['title'];
		return "";
	}
	
	/**
	 *	Returns Action ID from Action Name.
	 *	@access		public
	 *	@param		string		title			Action Name
	 *	@return		int
	 */
	public function getActionID( $title )
	{
		if( isset( $this->_cache[$title] ) )
			return $this->_cache[$title];
		$this->focusForeign( "title", $title );
		$data	= $this->getData( true );
		$this->defocus();
		if( isset( $data['rightActionId'] ) )
			return $this->_cache[$title] = $data['rightActionId'];
		return 0;
	}
}
?>
