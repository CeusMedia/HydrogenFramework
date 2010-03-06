<?php
/**
 *	Logic for Data Lists from Database.
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
 *	@package		framework.krypton.logic
 *	@uses			Database_StatementBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			23.02.2007
 *	@version		0.1
 */
import( 'de.ceus-media.database.StatementBuilder' );
/**
 *	Logic for Data Lists from Database.
 *	@category		cmClasses
 *	@package		framework.krypton.logic
 *	@uses			Database_StatementBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			23.02.2007
 *	@version		0.1
 */
class Framework_Krypton_Logic_List
{
	/**	@var		StatementBuilder	$builder		Statement Builder Object */
	private $builder	= NULL;
	/**	@var		StatementCollection	$collection		Statement Collection Object */
	private $collection	= NULL;
	
	/**
	 *	Constructor, build Statement Builder and Statement Collection.
	 * 	@access		public
	 *	@param		mixed		$collection		Class Name or Object of Statement Collection to use
	 *	@param		string		$tablePrefix	Prefix of Tables
	 *	@return		void
	 */
	public function __construct( $collection, $tablePrefix = "" )
	{
		$this->builder		= new Database_StatementBuilder( $tablePrefix );
		if( !is_object( $collection ) )
		{
			if( !class_exists( $collection ) )
				throw new Exception( 'Collection Class "'.$collection.'" was not been loaded.' );
			$collection	= new $collection( $this->builder );
		}
		$this->collection	= $collection;
	}

	/**
	 *	Adds a Component by Name and Arguments.
	 * 	@access		public
	 *	@param		string		$name			Name of Component
	 *	@param		array		$arguments		Component Arguments
	 *	@return		void
	 */
	public function __call( $component, $arguments )
	{
		if( !method_exists( $this->collection, $component ) )
			throw new BadMethodCallException( 'Collection Component "'.$component.'" is not defined.' );
		call_user_func_array( array( $this->collection, $component ), $arguments );
	}

	/**
	 *	Adds a Component by Name and Arguments.
	 * 	@access		public
	 *	@param		string		$component		Name of Component
	 *	@param		array		$arguments		Component Arguments
	 *	@return		void
	 */
	public function addComponent( $component, $arguments )
	{
		if( !method_exists( $this->collection, $component ) )
			throw new BadMethodCallException( 'Collection Component "'.$component.'" is not defined.' );
		call_user_func_array( array( $this->collection, $component ), $arguments );
	}

	/**
	 *	Returns Rowcount of Statement Result.
	 * 	@access		public
	 *	@param		Database_PDO_Connection	$dbc			Database Connection
	 *	@param		bool					$verbose		Show Query Information
	 *	@return		int
	 */
	public function getCount( $dbc, $verbose = FALSE )
	{
		$query	= $this->builder->buildCountStatement();
		try{
			$result	= $dbc->query( $query );
			$result	= $result->fetch( FALSE );
			$count	= $result['rowcount'];
			if( $verbose )
			{
				remark( "Query: ".$query );
				remark( "Count: ".$count );
			}
			return $count;
		}
		catch( Framework_Krypton_Exception_SQL $e )
		{
			die( $e->getMessage().": ".$e->getError() );
		}
	}
	
	/**
	 *	Returns resulting List of Statement.
	 * 	@access		public
	 *	@param		Database_PDO_Connection	$dbc			Database Connection
	 *	@param		bool					$verbose		Show Query Information
	 *	@return		array
	 */
	public function getList( $dbc, $verbose = FALSE )
	{
		$list	= array();
		$query	= $this->builder->buildStatement();
		if( $verbose )
		{
			remark( "Query: ".$query );
		}
		$result	= $dbc->query( $query );
		return $result->fetchAll( PDO::FETCH_ASSOC );
	}
}
?>