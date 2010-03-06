<?php
/**
 *	User Collection of SQL Statement Components.
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
 *	User Collection of SQL Statement Components.
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
class Framework_Neon_Models_UserStatements extends Database_StatementCollection
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
		$this->collectUser();
	}

	/**
	 *	Base Component.
	 *	@access		private
	 *	@return		void
	 */
	private function collectUser()
	{
		$keys	= array(
			"u.userId",
			"u.username",
			"u.email",
			"u.notify",
			"u.created",
			"u.modified",
		);
		$table	= "users as u";
		$this->builder->addKeys( $keys );
		$this->builder->addTable( $table );
	}
	
	/**
	 *	Username Component.
	 *	@access		public
	 *	@param		string		$username		Username to be found
	 *	@return		void
	 */
	public function withUsername( $username )
	{
		$condition	= "u.username LIKE '%".$username."%'";
		$this->builder->addCondition( $condition );
	}
	
	/**
	 *	Mail Component.
	 *	@access		public
	 *	@param		string		$email		Mail to be found
	 *	@return		void
	 */
	public function withEmail( $email )
	{
		$condition	= "u.email LIKE '%".$email."%'";
		$this->builder->addCondition( $condition );
	}
}
?>