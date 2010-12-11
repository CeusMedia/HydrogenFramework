<?php
class DummyCache{

	function has(){}
	function get(){}
	function set(){}
	function remove(){}
	function getAll(){}
}
/**
 *	Generic Model Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
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
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Generic Model Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@uses			Database_PDO_TableWriter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Model
{
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object */
	protected $env;
	/**	@var		string							$name			Name of Database Table without Prefix */
	protected $name									= "";
	/**	@var		array							$fields			List of Database Table Fields */
	protected $fields								= array();
	/**	@var		array							$name			Array of foreign Keys of Database Table */
 	protected $indices								= array();
	/**	@var		string							$primaryKey		Primary Key of Database Table */
	protected $primaryKey							= "";
	/**	@var		Database_PDO_TableWriter		$table			Database Table Writer Object for reading from and writing to Database Table */
	protected $table;
	/**	@var		string							$prefix			Database Table Prefix */
 	protected $prefix;
	/**	@var		ADT_List_Dictionary				$cache			Model data cache */
	protected $cache;
	/**	@var		int								$fetchMode		PDO fetch mode */
	protected $fetchMode;

	public static $cacheClass						= 'ADT_List_Dictionary';


	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object
	 *	@param		int								$id				ID to focus on
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env, $id = NULL )
	{
		$this->setEnv( $env );
		$this->table	= new Database_PDO_TableWriter(
			$env->getDatabase(),
			$this->prefix.$this->name,
			$this->fields,
			$this->primaryKey,
			$id
		);
		if( $this->fetchMode )
			$this->table->setFetchMode( $this->fetchMode );
		$this->table->setIndices( $this->indices );
#		$this->cache	= new Net_Memory_Cache();
#		$this->cache	= new DummyCache();
#		$this->cache	= new File_Cache( 'cache' );
		$this->cache	= Alg_Object_Factory::createObject( self::$cacheClass );
		$this->cacheKey	= 'db.'.$this->prefix.$this->name.'.';
	}
	
	//  --  PUBLIC METHODS  --  // 
	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		array			$data			Data to add to Table
	 *	@param		bool		$stripTags		Flag: strip HTML Tags from values
	 *	@return		int
	 */
	public function add( $data, $stripTags = TRUE )
	{
		$id	= $this->table->insert( $data );
		$this->cache->set( $this->cacheKey.$id, $data );
		return $id;
	}

	public function count( $conditions = array() )
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
	public function countByIndex( $key, $value )
	{
		$conditions	= array( $index => $value );
		return $this->count( $conditions );
	}

	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		int				$id				ID to focus on
	 *	@param		array			$data			Data to edit
	 *	@return		int				Number of changed rows
	 */
	public function edit( $id, $data )
	{
		$this->table->focusPrimary( $id );
		$result	= 0;
		if( count( $this->table->get( FALSE ) ) )
			$result	= $this->table->update( $data );
		$this->table->defocus();
		$this->cache->remove( $this->cacheKey.$id );
		return $result;
	}

	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		int				$id				ID to focus on
	 *	@param		string			$field			Single Field to return
	 *	@return		mixed
	 */
	public function get( $id, $field = '' )
	{
		$data = $this->cache->get( $this->cacheKey.$id );
		if( !$data )
		{
			$this->table->focusPrimary( $id );
			$data	= $this->table->get( TRUE );
			$this->table->defocus();
			$this->cache->set( $this->cacheKey.$id, $data );
		}
		if( $field )
			return $data[$field];
		return $data;
	}
	
	/**
	 *	Returns Data of all Lines.
	 *	@access		public
	 *	@param		array			$conditions		Array of Conditions to include in SQL Query
	 *	@param		array			$orders			Array of Orders to include in SQL Query
	 *	@param		array			$limits			Array of Limits to include in SQL Query
	 *	@return		array
	 */
	public function getAll( $conditions = array(), $orders = array(), $limits = array(), $columns = array(), $groupings = array() )
	{
		$data	= $this->table->find( $columns, $conditions, $orders, $limits );
		return $data;
	}

	/**
	 *	Returns Data of all Lines selected by Index.
	 *	@access		public
	 *	@param		string			$key			Key of Index
	 *	@param		string			$value			Value of Index
	 *	@param		array			$orders			Array of Orders to include in SQL Query
	 *	@param		array			$limits			Array of Limits to include in SQL Query
	 *	@return		array
	 */
	public function getAllByIndex( $key, $value, $orders = array(), $limits = array() )
	{
		$this->table->focusIndex( $key, $value );
		$data	= $this->table->get( FALSE, $orders, $limits );
		$this->table->defocus();
		return $data;
	}

	/**
	 *	Returns Data of all Lines selected by Indices.
	 *	@access		public
	 *	@param		array			$indices		Array of Index Keys and Values
	 *	@param		array			$conditions		Array of Conditions to include in SQL Query
	 *	@param		array			$orders			Array of Orders to include in SQL Query
	 *	@param		array			$limits			Array of Limits to include in SQL Query
	 *	@return		array
	 */
	public function getAllByIndices( $indices = array(), $orders = array(), $limits = array() )
	{
		if( !is_array( $indices ) )
			throw new InvalidArgumentException( 'Index map must be an array' );
		if( !$indices )
			throw new InvalidArgumentException( 'Index map must have atleast one pair' );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );
		$data	= $this->table->get( FALSE, $orders, $limits );
		$this->table->defocus();
		return $data;
	}

	/**
	 *	Returns data of single line selected by index.
	 *	@access		public
	 *	@param		string			$key			Key of Index
	 *	@param		string			$value			Value of Index
	 *	@param		string			$field			Single Field to return
	 *	@return		mixed
	 */
	public function getByIndex( $key, $value, $field = "" )
	{
		$this->table->focusIndex( $key, $value );
		$data	= $this->table->get( TRUE );
		$this->table->defocus();
		if( $field )
			return $data[$field];
		return $data;
	}
	
	/**
	 *	Returns data of single line selected by indices.
	 *	@access		public
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@param		string			$field			Single field to return
	 *	@return		mixed
	 */
	public function getByIndices( $indices, $field = "" )
	{
		if( !is_array( $indices ) )
			throw new InvalidArgumentException( 'Index map must be an array' );
		if( !$indices )
			throw new InvalidArgumentException( 'Index map must have atleast one pair' );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );
		$data	= $this->table->get( TRUE );
		$this->table->defocus();
		if( $field )
			return $data[$field];
		return $data;
	}

	/**
	 *	Indicates whether a table row is existing by ID.
	 *	@param		int				$id				ID to focus on
	 *	@return		bool
	 */
	public function has( $id )
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
	 *	@return		bool
	 */
	public function hasByIndex( $key, $value )
	{
		return (bool) $this->getByIndex( $key, $value );
	}

	/**
	 *	Indicates whether a Table Row is existing by a Map of Indices.
	 *	@access		public
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@return		bool
	 */
	public function hasByIndices( $indices )
	{
		return (bool) $this->getByIndices( $indices );
	}
	
	/**
	 *	Returns Data of single Line by ID.
	 *	@access		public
	 *	@param		int				$id				ID to focus on
	 *	@return		bool
	 */
	public function remove( $id )
	{
		$this->table->focusPrimary( $id );
		$result	= FALSE;
		if( count( $this->table->get( FALSE ) ) )
		{
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
	 *	@return		bool
	 */
	public function removeByIndex( $key, $value )
	{
		$this->table->focusIndex( $key, $value );
		$result	= FALSE;
		if( count( $this->table->get( FALSE ) ) )
		{
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
	 *	@param		array			$indices		Map of Index Keys and Values
	 *	@return		integer			Number of removed entries
	 */
	public function removeByIndices( $indices )
	{
		if( !is_array( $indices ) )
			throw new InvalidArgumentException( 'Index map must be an array' );
		if( !$indices )
			throw new InvalidArgumentException( 'Index map cannot be empty' );
		foreach( $indices as $key => $value )
			$this->table->focusIndex( $key, $value );

		$number	= $this->table->delete();
		$this->table->defocus();
		$this->cache->remove( $this->cacheKey.$id );
		return $number;
	}

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object
	 *	@return		void
	 */
	protected function setEnv( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env			= $env;
		$this->prefix		= $env->getConfig()->get( 'database.prefix' );
	}
}
?>