<?php
/**
 *	...
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
/**
 *	...
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_LogicPool {

	/**	@var		CMF_Hydrogen_Environment_Abstract	$env		Environment object */
	protected $env;
	/**	@var		array								$ppol		Map of logic class instances */
	protected $pool		= array();

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment object
	 *	@return		void
	 */
	public function  __construct( CMF_Hydrogen_Environment_Abstract $env ) {
		$this->env		= $env;
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string		$key			Key of logic object
	 *	@return		object
	 */
	public function __get( $key ){
		return $this->get( $key );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string		$key			Key of logic object
	 *	@return		boolean
	 */
	public function __isset( $key ){
		return $this->has( $key );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string		$key			Key of logic object to store in pool
	 *	@param		object		$logicObject	Logic object to store in pool
	 *	@return		void
	 */
	public function __set( $key, $logicObject ){
		return $this->set( $key, $logicObject );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string		$key			Key of logic object
	 *	@return		void
	 */
	public function __unset( $key ){
		return $this->remove( $key );
	}
	
	/**
	 *	Stores a logic object in pool by its pool key and class name.
	 *	Please use register() to add logic classes with lazy construction.
	 *	@access		public
	 *	@param		string		$key			Key of logic object in pool
	 *	@param		string		$logicClass		Name of logic class to register
	 *	@param		boolean		$override		Flag: overwrite existing keys in pool
	 *	@return		void
	 */
	public function add( $key, $logicClass, $override = FALSE){
		$object		= Alg_Object_Factory::createObject( $logicClass, array( $this->env ) );
		$this->set( $key, $object, $override );
	}

	/**
	 *	Returns a stored logic object by its pool key
	 *	@access		public
	 *	@param		string		$key			Key of logic object
	 *	@return		object
	 */
	public function get( $key ){
		if( !$this->has( $key ) )
			throw new RuntimeException( 'No logic "'.$key.'" available' );
		if( is_string( $this->pool[$key] ) ){
			$object	= Alg_Object_Factory::createObject( $this->pool[$key], array( $this->env ) );
			$this->set( $key, $object, TRUE );
		}
		return $this->pool[$key];
		
	}

	/**
	 *	Indicates whether a logic object is stored by its pool key.
	 *	@access		public
	 *	@return		boolean
	 */
	public function has( $key ){
		return array_key_exists( $key, $this->pool );
	}

	/**
	 *	Returns list of keys of stored logic objects.
	 *	@access		public
	 *	@return		array		List of keys of stored logic objects
	 */
	public function index(){
		return array_keys( $this->pool );
	}

	/**
	 *	Register a logic class (without instance) in pool.
	 *	The related logic object will be created on first call.
	 *	@access		public
	 *	@param		string		$key			Key of logic object in pool
	 *	@param		string		$logicClass		Name of logic class to register
	 *	@param		boolean		$override		Flag: overwrite existing keys in pool
	 *	@return		void
	 *	@throws		RuntimeException			if key is already existing in pool and no override
	 *	@throws		InvalidArgumentException	if given className is not a string
	 */
	public function register( $key, $logicClass, $override = FALSE ){
		if( $this->has( $key ) && !$override )
			throw new RuntimeException( 'Logic "'.$key.'" is already in pool' );
		if( !is_string( $logicClass ) )
			throw new InvalidArgumentException( 'Logic class must be an string' );
		$this->pool[$key]	= $logicClass;
	}

	/**
	 *	Removes a stored logic object by its pool key.
	 *	@access		public
	 *	@param		string		$key			Key of logic object in pool
	 *	@return		void
	 *	@throws		RuntimeException			if no logic is stored for pool key
	 */
	public function remove( $key ){
		if( !$this->has( $key ) )
			throw new RuntimeException( 'No logic "'.$key.'" available' );
		unset( $this->pool[$key] );
	}
	
	/**
	 *	Stores a logic object in pool by its pool key.
	 *	Please use add() to all logic classes or use register() to add logic classes with lazy construction.
	 *	@access		public
	 *	@param		string		$key			Key of logic object to store in pool
	 *	@param		object		$logicObject	Logic object to store in pool
	 *	@param		boolean		$override		Flag: overwrite existing keys in pool
	 *	@return		void
	 *	@throws		RuntimeException			if key is already existing in pool and no override
	 *	@throws		InvalidArgumentException	if given className is not a string
	 */
	public function set( $key, $logicObject, $override = FALSE ){
		if( $this->has( $key ) && !$override )
			throw new RuntimeException( 'Logic "'.$key.'" is already in pool' );
		if( !is_object( $logicObject ) )
			throw new InvalidArgumentException( 'Logic instance must be an object' );
		$this->pool[$key]	= $logicObject;
	}
}
?>
