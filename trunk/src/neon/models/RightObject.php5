<?php
/**
 *	Data Model of Right Objects.
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
 *	Data Model of Right Objects.
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
class Framework_Neon_Models_RightObject extends Framework_Neon_Model
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $rightObjectId = false )
	{
		$tableName	= "rightobjects";
		$fieldList	= array (
			"rightObjectId",
			"title",
			"description",
			);
		$primaryKey	= "rightObjectId";
		$foreignKeys	= array( "title" );

		parent::__construct( $tableName, $fieldList, $primaryKey, $rightObjectId );
		$this->setForeignKeys ( $foreignKeys );
	}

	/**
	 *	Returns Array of Right Objects.
	 *	@access		public
	 *	@return		array
	 */
	public function getObjects()
	{
		$list = $this->getAllData();
		$objects	= array();
		foreach( $list as $entry )
			$objects[$entry['rightObjectId']]	= $entry['title'];
		return $objects;
	}

	/**
	 *	Returns Object Name of Object ID.
	 *	@access		public
	 *	@param		int			rightObjectId		Object ID
	 *	@return		string
	 */
	public function getObject( $rightObjectId )
	{
		$this->focusPrimary( $rightObjectId );
		$data	= $this->getData( true );
		$this->defocus();
		if( isset( $data['title'] ) )
			return $data['title'];
		return "";
	}

	/**
	 *	Returns Action ID from Action Name.
	 *	@access		public
	 *	@param		string		title			Object Title
	 *	@return		int
	 */
	public function getObjectID( $title )
	{
		if( isset( $this->_cache[$title] ) )
			return $this->_cache[$title];
		$this->defocus();
		$this->focusForeign( "title", $title );
		$data	= $this->getData( true );
		$this->defocus();
		
		if( isset( $data['rightObjectId'] ) )
			return $this->_cache[$title] = $data['rightObjectId'];
		return 0;
	}
}
?>
