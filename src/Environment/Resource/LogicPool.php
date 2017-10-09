<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_LogicPool {

	/**	@var			CMF_Hydrogen_Environment_Abstract	$env		Environment object */
	protected $env;
	/**	@var			array								$pool		Map of logic class names or instances */
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
	 *	@param		string			$key			Key of logic object
	 *	@return		object
	 */
	public function __get( $key ){
		return $this->get( $key );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		boolean
	 */
	public function __isset( $key ){
		return $this->has( $key );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string			$key			Key of logic object to store in pool
	 *	@param		object			$logicObject	Logic object to store in pool
	 *	@return		void
	 */
	public function __set( $key, $logicObject ){
		return $this->set( $key, $logicObject );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		void
	 */
	public function __unset( $key ){
		return $this->remove( $key );
	}

	/**
	 *	Stores a logic component in pool by class name or instance.
	 *	Alias for set with "override" disabled.
	 *	@access		public
	 *	@param		string			$key				Key of logic object in pool
	 *	@param		string|object	$logicClassOrObject	Name of logic class to add
	 *	@return		void
	 */
	public function add( $key, $logicClassOrObject ){
		$this->set( $key, $logicClassOrObject, FALSE );
	}

	/**
	 *	Creates instance of logic class.
	 *	@access		protected
	 *	@param		string			$className			Name of class to create instance for
	 *	@return		object
	 *	@throws		InvalidArgumentException			if class is not a subclass of CMF_Hydrogen_Logic
	 */
	protected function createInstance( $className ){
		if( is_subclass_of( $className, 'CMF_Hydrogen_Logic' ) )
			return Alg_Object_Factory::createObject( $className, array( $this->env ) );

		// @todo activate this line after deprecation of old logic classes
//		throw new InvalidArgumentException( 'Given class "'.$className.'" is not a valid logic class' );
		$arguments	= array( $this->env );
		if( is_subclass_of( $className, 'CMF_Hydrogen_Logic_Singleton' ) )
			return call_user_func_array( array( $className, 'getInstance' ), $arguments );
		if( is_subclass_of( $className, 'CMF_Hydrogen_Logic_Multiple' ) )
			return Alg_Object_Factory::createObject( $className, $arguments );
		return Alg_Object_Factory::createObject( $className, $arguments );
	}

	/**
	 *	Returns a stored logic object by its pool key
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		object
	 *	@throws		RuntimeException				if no class or object has been added for given key
	 *	@throws		DomainException					if class for given key is not existing
	 */
	public function get( $key ){
		if( !$this->has( $key ) ){
			$className		= $this->getClassNameFromKey( $key );
			class_exists( $className ) ? $this->add( $key, $className ) : NULL;
		}
		if( !$this->has( $key ) )
			throw new RuntimeException( 'No logic class/object available for key "'.$key.'"' );

		if( !$this->isInstantiated( $key ) ){
			if( !class_exists( $this->pool[$key] ) )
				throw new DomainException( 'No logic class found for "'.$this->pool[$key].'"' );
			$this->set( $key, $this->createInstance( $this->pool[$key] ), TRUE );
		}
		return $this->pool[$key];
	}

	/**
	 *	Returns class name of registered logic pool key.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		string
	 */
	public function getClassNameFromKey( $key ){
		$classNameWords	= ucwords( Alg_Text_CamelCase::decode( $key ) );
		return str_replace( ' ', '_', 'Logic '.$classNameWords );
	}

	/**
	 *	Returns logic pool key for a logic class name.
	 *	@access		public
	 *	@param		string			$className		Name of class to create instance for
	 *	@return		string
	 *	@throws		InvalidArgumentException		if given class name os empty
	 *	@throws		InvalidArgumentException		if given class name is not starting with Logic_
	 */
	public function getKeyFromClassName( $className ){
		if( !strlen( trim( $className ) ) )
			throw new InvalidArgumentException( 'Class name cannot be empty' );
		$parts	= explode( ' ', str_replace( '_', ' ', $className ) );
		$prefix	= array_shift( $parts );
		if( $prefix !== 'Logic' )
			throw new InvalidArgumentException( 'Given class is not a logic class (needs to start with Logic_)' );
		return \Alg_Text_CamelCase::encode( implode( ' ', $parts ), TRUE );
	}

	/**
	 *	Indicates whether a logic object is stored by its pool key.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		boolean
	 */
	public function has( $key ){
		return array_key_exists( $key, $this->pool );
	}

	/**
	 *	Returns list of keys of stored logic classes or objects.
	 *	@access		public
	 *	@return		array			List of keys of stored logic classes or objects
	 */
	public function index(){
		return array_keys( $this->pool );
	}

	/**
	 *	Indicates whether a pool key points to a class or to an object.
	 *	Returns NULL if neither a class nor an object is registered for key.
	 *	@access		public
	 *	@param		string			$key			Key of logic object in pool
	 *	@return		boolean|NULL
	 */
	public function isInstantiated( $key ){
		if( !$this->has( $key ) )
			return NULL;
		return is_object( $this->pool[$key] );
	}

	/**
	 *	Removes a stored logic object by its pool key.
	 *	@access		public
	 *	@param		string			$key			Key of logic object in pool
	 *	@return		void
	 *	@throws		RuntimeException				if no logic is stored for pool key
	 */
	public function remove( $key ){
		if( !$this->has( $key ) )
			throw new RuntimeException( 'No logic "'.$key.'" available' );
		unset( $this->pool[$key] );
	}

	/**
	 *	Stores a logic component in pool by class name or instance, allowing overriding.
	 *	@access		public
	 *	@param		string			$key				Key of logic to store in pool
	 *	@param		string|object	$logicClassOrObject	Logic instance or class name to store in pool
	 *	@param		boolean			$override			Flag: overwrite existing key in pool
	 *	@return		void
	 *	@throws		RuntimeException					if key is already existing in pool and overriding disabled
	 *	@throws		InvalidArgumentException			if logic component is neither an instance nor a string
	 */
	public function set( $key, $logicClassOrObject, $override = TRUE ){
		if( $this->has( $key ) && !$override )
			throw new RuntimeException( 'Logic "'.$key.'" is already in logic pool' );
		if( !is_string( $logicClassOrObject ) && !is_object( $logicClassOrObject ) )
			throw new InvalidArgumentException( 'Given logic must be either a logic class or a logic object ('.gettype( $logicClassOrObject ).' given)' );
		$this->pool[$key]	= $logicClassOrObject;
	}
}
?>
