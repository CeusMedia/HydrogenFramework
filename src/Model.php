<?php
/**
 *	Generic Model Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2021 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

use CeusMedia\Database\PDO\Table\Writer as DatabaseTableWriter;

/**
 *	Generic Model Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Model
{
	/**	@var		CMF_Hydrogen_Environment		$env			Application Environment Object */
	protected $env;
	/**	@var		string							$name			Name of Database Table without Prefix */
	protected $name									= "";
	/**	@var		array							$columns		List of Database Table Columns */
	protected $columns								= array();
	/**	@var		array							$name			List of foreign Keys of Database Table */
 	protected $indices								= array();
	/**	@var		string							$primaryKey		Primary Key of Database Table */
	protected $primaryKey							= "";
	/**	@var		DatabaseTableWriter				$table			Database Table Writer Object for reading from and writing to Database Table */
	protected $table;
	/**	@var		string							$prefix			Database Table Prefix */
 	protected $prefix;
	/**	@var		ADT_List_Dictionary				$cache			Model data cache */
	protected $cache;
	/**	@var		integer							$fetchMode		PDO fetch mode */
	protected $fetchMode;
	/**	@var		string							$cacheKey		Base key in cache */
	protected $cacheKey;

	public static $cacheClass						= 'ADT_List_Dictionary';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment		$env			Application Environment Object
	 *	@param		integer							$id				ID to focus on
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env, $id = NULL )
	{
		$this->setEnv( $env );
		$this->table	= new DatabaseTableWriter(
			$this->env->getDatabase(),
			$this->prefix.$this->name,
			$this->columns,
			$this->primaryKey,
			$id
		);
		if( $this->fetchMode )
			$this->table->setFetchMode( $this->fetchMode );
		$this->table->setIndices( $this->indices );
		$this->cache	= Alg_Object_Factory::createObject( self::$cacheClass );
		$this->cacheKey	= 'db.'.$this->prefix.$this->name.'.';

		if( !empty( $this->env->storage ) )
			$this->table->setUndoStorage( $this->env->storage );
	}

	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		array			$data			Data to add to Table
	 *	@param		boolean			$stripTags		Flag: strip HTML Tags from values, default: yes
	 *	@return		integer
	 */
	public function add( array $data, bool $stripTags = TRUE )
	{
		$id	= $this->table->insert( $data, $stripTags );
		$this->cache->set( $this->cacheKey.$id, $this->get( $id ) );
		return $id;
	}

	/**
	 *	Returns number of entries at all or for given conditions.
	 *	@access		public
	 *	@param		array			$conditions		Map of conditions
	 *	@return		integer			Number of entries
	 */
	public function count( array $conditions = array() ): int
	{
		return $this->table->count( $conditions );
	}

	/**
	 *	Returns number of entries within an index.
	 *	@access		public
	 *	@param		string			$key			Index Key
	 *	@param		string			$value			Value of Index
	 *	@return		integer			Number of entries within this index
	 */
	public function countByIndex( string $key, $value )
	{
		$conditions	= array( $key => $value );
		return $this->table->count( $conditions );
	}

	/**
	 *	Returns number of entries selected by map of indices.
	 *	@access		public
	 *	@param		array			$indices		Map of index conditions
	 *	@return		integer			Number of entries within this index
	 */
	public function countByIndices( array $indices ): int
	{
		return $this->count( $indices );
	}

	/**
	 *	Returns number of entries of a large table by map of conditions.
	 *	Attention: The returned number may be inaccurat, but this is much faster.
	 *	@access		public
	 *	@param		array			$conditions		Map of conditions
	 *	@return		integer			Number of entries
	 */
	public function countFast( array $conditions ): int
	{
		return $this->table->countFast( $conditions );
	}

	/**
	 *	Modifies data of single row by ID.
	 *	@access		public
	 *	@param		integer			$id				ID to focus on
	 *	@param		array			$data			Data to edit
	 *	@param		boolean			$stripTags		Flag: strip HTML Tags from values, default: yes
	 *	@return		integer			Number of changed rows
	 */
	public function edit( $id, $data, bool $stripTags = TRUE )
	{
		$this->table->focusPrimary( $id );
		$result	= 0;
		if( count( $this->table->get( FALSE ) ) )
			$result	= $this->table->update( $data, $stripTags );
		$this->table->defocus();
		$this->cache->remove( $this->cacheKey.$id );
		return $result;
	}

	/**
	 *	Modifies data of several rows by indices.
	 *	@access		public
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@param		array			$data			Data to edit
	 *	@param		boolean			$stripTags		Flag: strip HTML Tags from values, default: yes
	 *	@return		integer			Number of changed rows
	 */
	public function editByIndices( array $indices, $data, bool $stripTags = TRUE )
	{
		$indices	= $this->checkIndices( $indices, TRUE, TRUE );
		return $this->table->updateByConditions( $data, $indices, $stripTags );
	}

	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		integer			$id				ID to focus on
	 *	@param		string			$field			Single Field to return
	 *	@return		mixed
	 */
	public function get( $id, $field = '' )
	{
		$field	= $this->checkField( $field, FALSE, TRUE );
		$data	= $this->cache->get( $this->cacheKey.$id );
		if( !$data ){
			$this->table->focusPrimary( $id );
			$data	= $this->table->get( TRUE );
			$this->table->defocus();
			$this->cache->set( $this->cacheKey.$id, $data );
		}
		if( strlen( trim( $field ) ) )
			return $this->getFieldsFromResult( $data, array( $field ) );
		return $data;
	}

	/**
	 *	Returns Data of all Lines.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			Map of Limits to include in SQL Query
	 *	@param		array			$fields			Map of Columns to include in SQL Query
	 *	@param		array			$groupings		List of columns to group by
	 *	@param		array			$havings		List of conditions to apply after grouping
	 *	@param		boolean			$strict			Flag: throw exception if result is empty and fields are selected (default: FALSE)
	 *	@return		array
	 */
	public function getAll( array $conditions = array(), array $orders = array(), array $limits = array(), array $fields = array(), array $groupings = array(), array $havings = array(), bool $strict = FALSE ): array
	{
		$data	= $this->table->find( $fields, $conditions, $orders, $limits, $groupings, $havings );
		if( $fields )
			foreach( $data as $nr => $set )
				$data[$nr]	= $this->getFieldsFromResult( $set, $fields, $strict );
		return $data;
	}

	/**
	 *	Returns Data of all Lines selected by Index.
	 *	@access		public
	 *	@param		string			$key			Key of Index
	 *	@param		string			$value			Value of Index
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			List of Limits to include in SQL Query
	 *	@param		array			$fields			List of fields or one field to return from result
	 *	@param		boolean			$strict			Flag: throw exception if result is empty and fields are selected (default: FALSE)
	 *	@return		array
	 */
	public function getAllByIndex( string $key, $value, array $orders = array(), array $limits = array(), array $fields = array(), bool $strict = FALSE ): array
	{
		$this->table->focusIndex( $key, $value );
		$data	= $this->table->get( FALSE, $orders, $limits );
		$this->table->defocus();
		if( $fields )
			foreach( $data as $nr => $set )
				$data[$nr]	= $this->getFieldsFromResult( $set, $fields, $strict );
		return $data;
	}

	/**
	 *	Returns Data of all Lines selected by Indices.
	 *	@access		public
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			List of Limits to include in SQL Query
	 *	@param		array			$fields			List of fields or one field to return from result
	 *	@param		boolean			$strict			Flag: throw exception if result is empty and fields are selected (default: FALSE)
	 *	@return		array
	 */
	public function getAllByIndices( array $indices = array(), array $orders = array(), array $limits = array(), array $fields = array(), bool $strict = FALSE )
	{
		$indices	= $this->checkIndices( $indices, TRUE, TRUE );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );
		$data	= $this->table->get( FALSE, $orders, $limits );
		$this->table->defocus();
		if( $fields )
			foreach( $data as $nr => $set )
				$data[$nr]	= $this->getFieldsFromResult( $set, $fields, $strict );
		return $data;
	}

	/**
	 *	Returns data of first entry selected by index.
	 *	@access		public
	 *	@param		string			$key			Key of Index
	 *	@param		string			$value			Value of Index
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$fields			List of fields or one field to return from result
	 *	@param		boolean			$strict			Flag: throw exception if result is empty (default: FALSE)
	 *	@return		mixed			Structure depending on fetch type, string if field selected, NULL if field selected and no entries
	 *	@todo		change argument order: move fields to end
	 *	@throws		InvalidArgumentException			If given fields list is neither a list nor a string
	 */
	public function getByIndex( string $key, $value, array $orders = array(), array $fields = array(), bool $strict = FALSE )
	{
		if( is_string( $fields ) )
			$fields	= strlen( trim( $fields ) ) ? array( trim( $fields ) ) : array();
		if( !is_array( $fields ) )
			throw new \InvalidArgumentException( 'Fields must be of array or string' );
		foreach( $fields as $field )
			$this->checkField( $field, FALSE, TRUE );
		$this->table->focusIndex( $key, $value );
		$data	= $this->table->get( TRUE, $orders );
		$this->table->defocus();
		return $this->getFieldsFromResult( $data, $fields, $strict );
	}

	/**
	 *	Returns data of single line selected by indices.
	 *	@access		public
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$fields			List of fields or one field to return from result
	 *	@param		boolean			$strict			Flag: throw exception if result is empty (default: FALSE)
	 *	@return		mixed			Structure depending on fetch type, string if field selected, NULL if field selected and no entries
	 *	@throws		InvalidArgumentException			If given fields list is neither a list nor a string
	 *	@todo  		change default value of argument 'strict' to TRUE
	 */
	public function getByIndices( array $indices, array $orders = array(), array $fields = array(), bool $strict = FALSE )
	{
		if( is_string( $fields ) )
			$fields	= strlen( trim( $fields ) ) ? array( trim( $fields ) ) : array();
		if( !is_array( $fields ) )
			throw new \InvalidArgumentException( 'Fields must be of array or string' );
		foreach( $fields as $field )
			$field	= $this->checkField( $field, FALSE, TRUE );
		$this->checkIndices( $indices, TRUE, TRUE );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );
		$result	= $this->table->get( TRUE, $orders );
		$this->table->defocus();
		return $this->getFieldsFromResult( $result, $fields, $strict );
	}

	/**
	 *	Returns list of table columns.
	 *	@access		public
	 *	@return		array
	 */
	public function getColumns(): array
	{
		return $this->table->getColumns();
	}

	/**
	 *	Returns list of distinct column values.
	 *	@access		public
	 *	@param		string			$column			Column to get distinct values for
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			List of Limits to include in SQL Query
	 *	@return		array			List of distinct column values
	 */
	public function getDistinct( string $column, array $conditions, array $orders = array(), array $limits = array() ): array
	{
		return $this->table->getDistinctColumnValues( $column, $conditions, $orders, $limits );
	}

	/**
	 *	Returns list of table index columns.
	 *	@access		public
	 *	@return		array
	 */
	public function getIndices(): array
	{
		return $this->table->getIndices();
	}

	public function getLastQuery()
	{
		return $this->table->getLastQuery();
	}

	/**
	 *	Returns table name with or without index.
	 *	@access		public
	 *	@param		boolean			$prefixed		Flag: return table name with prefix
	 *	@return		string			Table name with or without prefix
	 */
	public function getName( bool $prefixed = TRUE ): string
	{
		if( $prefixed )
			return $this->prefix.$this->name;
		return $this->name;
	}

	/**
	 *	Returns primary key columns name of table.
	 *	@access		public
	 *	@return		string			Primary key column name
	 */
	public function getPrimaryKey(): string
	{
		return $this->table->getPrimaryKey();
	}

	/**
	 *	Indicates whether a table row is existing by ID.
	 *	@param		integer			$id				ID to focus on
	 *	@return		boolean
	 */
	public function has( $id ): bool
	{
		if( $this->cache->has( $this->cacheKey.$id ) )
			return TRUE;
		return (bool) $this->get( $id );
	}

	/**
	 *	Indicates whether a table row is existing by index.
	 *	@access		public
	 *	@param		string			$key			Key of Index
	 *	@param		string			$value			Value of Index
	 *	@return		boolean
	 */
	public function hasByIndex( string $key, $value ): bool
	{
		return (bool) $this->getByIndex( $key, $value );
	}

	/**
	 *	Indicates whether a Table Row is existing by a Map of Indices.
	 *	@access		public
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@return		boolean
	 */
	public function hasByIndices( array $indices ): bool
	{
		return (bool) $this->getByIndices( $indices );
	}

	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		integer			$id				ID to focus on
	 *	@return		boolean
	 */
	public function remove( $id ): bool
	{
		$this->table->focusPrimary( $id );
		$result	= FALSE;
		if( count( $this->table->get( FALSE ) ) ){
			$this->table->delete();
			$result	= TRUE;
		}
		$this->table->defocus();
		$this->cache->remove( $this->cacheKey.$id );
		return $result;
	}

	/**
	 *	Removes entries selected by index.
	 *	@access		public
	 *	@param		string			$key			Key of Index
	 *	@param		string			$value			Value of Index
	 *	@return		boolean
	 */
	public function removeByIndex( string $key, $value ): bool
	{
		$this->table->focusIndex( $key, $value );
		$number	= 0;
		$rows	= $this->table->get( FALSE );
		if( count( $rows ) ){
			$number = $this->table->delete();
			foreach( $rows as $row ){
				switch( $this->fetchMode ){
					case \PDO::FETCH_CLASS:
					case \PDO::FETCH_OBJ:
						$id	= $row->{$this->primaryKey};
						break;
					default:
						$id	= $row[$this->primaryKey];
				}
				$this->cache->remove( $this->cacheKey.$id );
			}
			$result	= TRUE;
		}
		$this->table->defocus();
		return $number;
	}

	/**
	 *	Removes entries selected by index.
	 *	@access		public
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@return		integer			Number of removed entries
	 */
	public function removeByIndices( array $indices ): int
	{
		$indices	= $this->checkIndices( $indices, TRUE, TRUE );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );

		$number	= 0;
		$rows	= $this->table->get( FALSE );
		if( count( $rows ) ){
			$number	= $this->table->delete();
			foreach( $rows as $row ){
				switch( $this->fetchMode ){
					case \PDO::FETCH_CLASS:
					case \PDO::FETCH_OBJ:
						$id	= $row->{$this->primaryKey};
						break;
					default:
						$id	= $row[$this->primaryKey];
				}
				$this->cache->remove( $this->cacheKey.$id );
			}
		}
		$this->table->defocus();
		return $number;
	}

	/**
	 *	Removes all data and resets incremental counter.
	 *	Note: This method does not return the number of removed rows.
	 *	@access		public
	 *	@return		void
	 *	@see		http://dev.mysql.com/doc/refman/4.1/en/truncate.html
	 */
	public function truncate()
	{
		$this->table->truncate();
	}

	//  --  PROTECTED  --  //

	/**
	 *	Indicates whether a requested field is a table column.
	 *	Returns trimmed field key if found, otherwise FALSE if not a string or not a table column.
	 *	Returns FALSE if empty and mandatory, otherwise NULL.
	 *	In strict mode exceptions will be thrown if field is not a string, empty but mandatory or not a table column.
	 *	@access		protected
	 *	@param		string			$field			Table Column to check for existence
	 *	@param		boolean			$mandatory		Force a value, otherwise return NULL or throw exception in strict mode
	 *	@param		boolean			$strict			Strict mode (default): throw exception instead of returning FALSE or NULL
	 *	@return		string|NULL		Trimmed Field name if found, NULL otherwise or exception in strict mode
	 *	@throws		InvalidArgumentException		in strict mode if field is not a string and strict mode is on
	 *	@throws		InvalidArgumentException		in strict mode if field is empty but mandatory
	 *	@throws		InvalidArgumentException		in strict mode if field is not a table column
	 */
	protected function checkField( string $field, bool $mandatory = FALSE, bool $strict = TRUE )
	{
		if( !is_string( $field ) ){
			if( !$strict )
				return FALSE;
			throw new \InvalidArgumentException( 'Field must be a string' );
		}
		$field	= trim( $field );
		if( !strlen( $field ) ){
			if( $mandatory ){
				if( !$strict )
					return FALSE;
				throw new \InvalidArgumentException( 'Field must have a value' );
			}
			return NULL;
		}
		if( !in_array( $field, $this->columns ) ){
			if( !$strict )
				return FALSE;
			$message	= 'Field "%s" is not an existing column of table %s';
			throw new \InvalidArgumentException( sprintf( $message, $field, $this->getName() ) );
		}
		return $field;
	}

	/**
	 *	Indicates whether a given map of indices is valid.
	 *	Returns map if valid or FALSE if not an array or empty but mandatory.
	 *	In strict mode exceptions will be thrown if map is not an array or empty but mandatory.
	 *	FYI: The next logical check - if index keys are valid columns and noted indices - is done by used table reader class.
	 *	@access		protected
	 *	@param		array 			$indices		Map of Index Keys and Values
	 *	@param		boolean			$mandatory		Force atleast one pair, otherwise return FALSE or throw exception in strict mode
	 *	@param		boolean			$strict			Strict mode (default): throw exception instead of returning FALSE
	 *	@return		array|boolean	Map if valid, FALSE otherwise or exceptions in strict mode
	 *	@throws		InvalidArgumentException		in strict mode if field is empty but mandatory
	 *	@todo		refactor return type
	 */
	protected function checkIndices( array $indices, bool $mandatory = FALSE, bool $strict = TRUE )
	{
		if( count( $indices ) === 0 && $mandatory ){
			if( !$strict )
				return FALSE;
			throw new InvalidArgumentException( 'Index map must have atleast one pair' );
		}
		return $indices;
	}

	/**
	 *	Returns any fields or one field from a query result.
	 *	@access		protected
	 *	@param		mixed			$result			Query result as array or object
	 *	@param		array			$fields			List of fields or one field
	 *	@param		boolean			$strict			Flag: throw exception if result is empty
	 *	@return		string|array|object				Structure depending on result and field list length
	 *	@throws		InvalidArgumentException		If given fields list is neither a list nor a string
	 */
	protected function getFieldsFromResult( $result, array $fields = array(), bool $strict = TRUE )
	{
		if( is_string( $fields ) )
			$fields	= strlen( trim( $fields ) ) ? array( trim( $fields ) ) : array();
		if( !is_array( $fields ) )
			throw new \InvalidArgumentException( 'Fields must be of array or string' );
		if( !$result ){
			if( $strict )
				throw new \Exception( 'Result is empty' );
			if( count( $fields ) === 1 )
				return NULL;
			return array();
		}
		if( !count( $fields ) )
			return $result;
		foreach( $fields as $field )
			if( !in_array( $field, $this->columns ) )
				throw new \InvalidArgumentException( 'Field "'.$field.'" is not an existing column' );

		if( count( $fields ) === 1 ){
			switch( $this->fetchMode ){
				case \PDO::FETCH_CLASS:
				case \PDO::FETCH_OBJ:
					if( !property_exists( $result, $field ) )
						throw new \DomainException( 'Field "'.$field.'" is not an column of result set' );
					return $result->$field;
				default:
					if( !array_key_exists( $field, $result ) )
						throw new \DomainException( 'Field "'.$field.'" is not an column of result set' );
					return $result[$field];
			}
		}
		switch( $this->fetchMode ){
			case \PDO::FETCH_CLASS:
			case \PDO::FETCH_OBJ:
				$map	= (object) array();
				foreach( $fields as $field ){
					if( !property_exists( $result, $field ) )
						throw new \DomainException( 'Field "'.$field.'" is not an column of result set' );
					$map->$field	= $result->$field;
				}
				return $map;
			default:
				$list	= array();
				foreach( $fields as $field ){
					if( !array_key_exists( $field, $result ) )
						throw new \DomainException( 'Field "'.$field.'" is not an column of result set' );
					$list[$field]	= $result[$field];
				}
				return $list;
		}
	}

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment	$env			Application Environment Object
	 *	@return		self
	 *	@throws		RuntimeException			if no database resource is available in given environment
	 */
	protected function setEnv( CMF_Hydrogen_Environment $env ): self
	{
		$this->env		= $env;
		if( !$env->getDatabase() )
			throw new RuntimeException( 'Database resource needed for '.get_class( $this ) );
		$this->prefix	= $env->getDatabase()->getPrefix();
		return $this;
	}
}
