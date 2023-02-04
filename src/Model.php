<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Generic Model Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2023 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Database\PDO\Connection as PdoConnection;
use CeusMedia\Database\PDO\Table\Writer as DatabaseTableWriter;

use DomainException;
use InvalidArgumentException;
use PDO;
use RangeException;
use ReflectionException;
use RuntimeException;

/**
 *	Generic Model Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Model
{
	/**	@var	Environment				$env			Application Environment Object */
	protected Environment $env;

	/**	@var	string					$name			Name of Database Table without Prefix */
	protected string $name				= "";

	/**	@var	array					$columns		List of Database Table Columns */
	protected array $columns			= [];

	/**	@var	array					$name			List of foreign Keys of Database Table */
 	protected array $indices			= [];

	/**	@var	string					$primaryKey		Primary Key of Database Table */
	protected string $primaryKey		= "";

	/**	@var	DatabaseTableWriter		$table			Database Table Writer Object for reading from and writing to Database Table */
	protected DatabaseTableWriter $table;

	/**	@var	string					$prefix			Database Table Prefix */
 	protected string $prefix;

	/**	@var	Dictionary				$cache			Model data cache */
	protected $cache;

	/**	@var	integer					$fetchMode		PDO fetch mode */
	protected int $fetchMode;

	/**	@var	string					$cacheKey		Base key in cache */
	protected string $cacheKey;

	public static string $cacheClass	= Dictionary::class;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Application Environment Object
	 *	@param		string|NULL		$id				ID to focus on
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env, ?string $id = NULL )
	{
		$this->setEnv( $env );
		$connection	= $this->env->getDatabase();
		if( !$connection instanceof PdoConnection )
			throw new RuntimeException( 'Set up database is not a fitting PDO connection' );
		$this->table	= new DatabaseTableWriter(
			$connection,
			$this->prefix.$this->name,
			$this->columns,
			$this->primaryKey,
			$id ? (int) $id : NULL
		);
		if( $this->fetchMode )
			$this->table->setFetchMode( $this->fetchMode );
		$this->table->setIndices( $this->indices );
		$this->cache	= ObjectFactory::createObject( self::$cacheClass );
		$this->cacheKey	= 'db.'.$this->prefix.$this->name.'.';
	}

	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		array			$data			Data to add to Table
	 *	@param		boolean			$stripTags		Flag: strip HTML Tags from values, default: yes
	 *	@return		string
	 */
	public function add( array $data, bool $stripTags = TRUE ): string
	{
		$id	= (string) $this->table->insert( $data, $stripTags );
		$this->cache->set( $this->cacheKey.$id, $this->get( $id ) );
		return $id;
	}

	/**
	 *	Returns number of entries at all or for given conditions.
	 *	@access		public
	 *	@param		array			$conditions		Map of conditions
	 *	@return		integer			Number of entries
	 */
	public function count( array $conditions = [] ): int
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
	public function countByIndex( string $key, string $value ): int
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
	 *	Attention: The returned number may be inaccurate, but this is much faster.
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
	 *	@param		string			$id				ID to focus on
	 *	@param		array			$data			Data to edit
	 *	@param		boolean			$stripTags		Flag: strip HTML Tags from values, default: yes
	 *	@return		integer			Number of changed rows
	 */
	public function edit( string $id, array $data, bool $stripTags = TRUE ): int
	{
		$this->table->focusPrimary( (int) $id );
		$result	= 0;
		if( $this->table->get() !== NULL )
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
	public function editByIndices( array $indices, array $data, bool $stripTags = TRUE ): int
	{
		/** @var array $indices */
		$indices	= $this->checkIndices( $indices );
		return $this->table->updateByConditions( $data, $indices, $stripTags );
	}

	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		string			$id				ID to focus on
	 *	@param		string|NULL		$field			Single Field to return
	 *	@return		mixed
	 */
	public function get( string $id, ?string $field = NULL )
	{
		if( NULL !== $field )
			$field	= $this->checkField( $field );
		$data	= $this->cache->get( $this->cacheKey.$id );
		if( !$data ){
			$this->table->focusPrimary( (int) $id );
			$data	= $this->table->get();
			$this->table->defocus();
			$this->cache->set( $this->cacheKey.$id, $data );
		}
		if( NULL !== $field )
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
	public function getAll( array $conditions = [], array $orders = [], array $limits = [], array $fields = [], array $groupings = [], array $havings = [], bool $strict = FALSE ): array
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
	 *	@param		mixed			$value			Value of Index
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			List of Limits to include in SQL Query
	 *	@param		array			$fields			List of fields or one field to return from result
	 *	@param		boolean			$strict			Flag: throw exception if result is empty and fields are selected (default: FALSE)
	 *	@return		array
	 */
	public function getAllByIndex( string $key, $value, array $orders = [], array $limits = [], array $fields = [], bool $strict = FALSE ): array
	{
		$this->table->focusIndex( $key, $value );
		/** @var array $data */
		$data	= $this->table->get( FALSE, $orders, $limits );
		$this->table->defocus();
		if( count( $fields ) !== 0 )
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
	public function getAllByIndices( array $indices = [], array $orders = [], array $limits = [], array $fields = [], bool $strict = FALSE ): array
	{
		/** @var array $indices */
		$indices	= $this->checkIndices( $indices );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );
		/** @var array $data */
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
	 *	@param		mixed			$value			Value of Index
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$fields			List of fields or one field to return from result
	 *	@param		boolean			$strict			Flag: throw exception if result is empty (default: FALSE)
	 *	@return		object|array|string|NULL		Structure depending on fetch type, string if field selected, NULL if field selected and no entries
	 *	@todo		change argument order: move fields to end
	 *	@throws		InvalidArgumentException			If given fields list is neither a list nor a string
	 */
	public function getByIndex( string $key, $value, array $orders = [], array $fields = [], bool $strict = FALSE )
	{
		foreach( $fields as $field )
			$this->checkField( $field );
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
	 *	@return		object|array|string|NULL		Structure depending on fetch type, string if field selected, NULL if field selected and no entries
	 *	@throws		InvalidArgumentException			If given fields list is neither a list nor a string
	 *	@todo  		change default value of argument 'strict' to TRUE
	 */
	public function getByIndices( array $indices, array $orders = [], array $fields = [], bool $strict = FALSE )
	{
		foreach( $fields as $field )
			$this->checkField( $field );
		$this->checkIndices( $indices );
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
	public function getDistinct( string $column, array $conditions, array $orders = [], array $limits = [] ): array
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

	public function getLastQuery(): ?string
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
	 *	@param		string			$id				ID to focus on
	 *	@return		boolean
	 */
	public function has( string $id ): bool
	{
		if( $this->cache->has( $this->cacheKey.$id ) )
			return TRUE;
		return (bool) $this->get( $id );
	}

	/**
	 *	Indicates whether a table row is existing by index.
	 *	@access		public
	 *	@param		string			$key			Key of Index
	 *	@param		mixed			$value			Value of Index
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
	 *	@param		string			$id				ID to focus on
	 *	@return		boolean
	 */
	public function remove( string $id ): bool
	{
		$this->table->focusPrimary( (int) $id );
		$result	= FALSE;
		if( $this->table->get() !== NULL ){
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
	 *	@param		mixed			$value			Value of Index
	 *	@return		int				Number of removed entries
	 */
	public function removeByIndex( string $key, $value ): int
	{
		$this->table->focusIndex( $key, $value );
		$number	= $this->removeIndexed();
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
		if( 0 === count( $indices ) )
			throw new RangeException( 'Indices cannot be empty' );
		/** @var array $indices */
		$indices	= $this->checkIndices( $indices );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );
		$number		= $this->removeIndexed();
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
	 *	@param		string				$field		Table Column to check for existence
	 *	@param		boolean				$strict		Strict mode (default): throw exception instead of returning FALSE or NULL
	 *	@return		string|FALSE|NULL	Trimmed Field name if found, otherwise NULL if not mandatory, otherwise NULL or exception in strict mode
	 *	@throws		InvalidArgumentException		if field is empty (in strict mode, only)
	 *	@throws		InvalidArgumentException		if field is not a valid column (in strict mode, only)
	 */
	protected function checkField( string $field, bool $strict = TRUE )
	{
		$field	= trim( $field );
		if( 0 === strlen( $field ) )
			throw new InvalidArgumentException( 'Field key cannot be empty' );
		if( in_array( $field, $this->columns ) )
			return $field;
		if( !$strict )
			return FALSE;
		$message	= sprintf( 'Field "%s" is not an existing column of table %s', $field, $this->getName() );
		throw new InvalidArgumentException( $message );
	}

	/**
	 *	Indicates whether a given map of indices is valid.
	 *	Returns map if valid indices or FALSE at least one requested index is invalid.
	 *	In strict mode, an exception will be thrown on false indices.
	 *	@access		protected
	 *	@param		array 			$indices		Map of Index Keys and Values
	 *	@param		boolean			$strict			Strict mode (default): throw exception instead of returning FALSE
	 *	@return		array|boolean	Map if valid, FALSE otherwise or exceptions in strict mode
	 *	@throws		InvalidArgumentException		if at least one requested index is invalid (in strict mode, only)
	 */
	protected function checkIndices( array $indices, bool $strict = TRUE )
	{
		if( 0 === count( $indices ) )
			throw new InvalidArgumentException( 'Index map must have at least one pair' );
		$diff	= array_diff_key( array_keys( $indices ), $this->indices );
		if( 0 === count( $diff ) )
			return $indices;
		if( !$strict )
			return FALSE;
		throw new InvalidArgumentException( 'Invalid indices: '.join( ', ', $diff ) );
	}

	/**
	 *	Returns any fields or one field from a query result.
	 *	@access		protected
	 *	@param		mixed			$result			Query result as array or object
	 *	@param		array			$fields			List of fields or one field
	 *	@param		boolean			$strict			Flag: throw exception if result is empty
	 *	@return		string|array|object|NULL		Structure depending on result and field list length
	 *	@throws		RuntimeException				if given result set is empty (in strict mode, only)
	 *	@throws		InvalidArgumentException		if at least one given field is not a table column
	 *	@throws		DomainException					if one of the fields is not within result
	 */
	protected function getFieldsFromResult( $result, array $fields = [], bool $strict = TRUE )
	{
		if( 0 === count( $fields ) )
			return $result;
		if( !$result ){
			if( $strict )
				throw new RuntimeException( 'Result is empty' );
			if( count( $fields ) === 1 )
				return NULL;
			return [];
		}
		$invalidFields	= array_diff( $fields, $this->columns );
		if( 0 !== count( $invalidFields ) )
			throw new InvalidArgumentException( 'Invalid fields: '.join( ', ', $invalidFields ) );

		switch( $this->fetchMode ){
			case PDO::FETCH_CLASS:
			case PDO::FETCH_OBJ:
				$map	= (object) [];
				foreach( $fields as $field ){
					if( !property_exists( $result, $field ) )
						throw new DomainException( 'Field "'.$field.'" is not an column of result set' );
					$map->$field	= $result->$field;
				}
				/** @noinspection PhpUndefinedVariableInspection */
				return 1 === count( $fields ) ? $map->$field : $map;
			default:
				$list	= [];
				foreach( $fields as $field ){
					if( !array_key_exists( $field, $result ) )
						throw new DomainException( 'Field "'.$field.'" is not an column of result set' );
					$list[$field]	= $result[$field];
				}
				/** @noinspection PhpUndefinedVariableInspection */
				return 1 === count( $fields ) ? $list[$field] : $list;
		}
	}

	/**
	 *	Removes entries currently in focus.
	 *	@return		int
	 *	@throws		RuntimeException		if no focus has been set before
	 */
	protected function removeIndexed(): int
	{
		if( 0 === count( $this->table->getFocus() ) )
			throw new RuntimeException( 'Not focus set' );
		$number	= 0;
		/** @var array $rows */
		$rows	= $this->table->get( FALSE );
		if( count( $rows ) ){
			$number	= $this->table->delete();
			foreach( $rows as $row ){
				switch( $this->fetchMode ){
					case PDO::FETCH_CLASS:
					case PDO::FETCH_OBJ:
						$id	= $row->{$this->primaryKey};
						break;
					default:
						$id	= $row[$this->primaryKey];
				}
				$this->cache->remove( $this->cacheKey.$id );
			}
		}
		return $number;
	}

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		Environment			$env			Application Environment Object
	 *	@return		self
	 *	@throws		RuntimeException			if no database resource is available in given environment
	 */
	protected function setEnv( Environment $env ): self
	{
		$this->env		= $env;
		if( !$env->getDatabase() )
			throw new RuntimeException( 'Database resource needed for '.get_class( $this ) );
		$this->prefix	= $env->getDatabase()->getPrefix();
		return $this;
	}
}
