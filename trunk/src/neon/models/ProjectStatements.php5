<?php
/**
 *	Project Collection of SQL Statement Components.
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
 *	@since			26.03.2007
 *	@version		0.2
 */
import( 'de.ceus-media.database.StatementCollection' );
/**
 *	Project Collection of SQL Statement Components.
 *	@category		cmClasses
 *	@package		framework.neon.models
 *	@extends		Database_StatementCollection
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			26.03.2007
 *	@version		0.2
 */
class Framework_Neon_Models_ProjectStatements extends Database_StatementCollection
{
	/**
	 *	Constructor, sets Base Component.
	 *	@access		public
	 *	@param		StatementBuilder	$statementBuilder		Instance of Statement Builder
	 *	@return		void
	 */
	public function __construct( $statementBuilder )
	{
		parent::__construct( $statementBuilder );
		$this->collectProjects();
	}

	/**
	 *	Base Component.
	 *	@access		private
	 *	@return		void
	 */
	private function collectProjects()
	{
		$keys	= array(
			"p.projectId",
			"p.title",
			"p.created",
			"p.modified",
		);
		$table	= "projects as p";
		$this->builder->addKeys( $keys );
		$this->builder->addTable( $table );
	}
	
	/**
	 *	Title Component.
	 *	@access		public
	 *	@param		string		$title		Title to be found
	 *	@return		void
	 */
	public function withTitle( $title )
	{
		$condition	= "p.title LIKE '%".$title."%'";
		$this->builder->addCondition( $condition );
	}
}
?>