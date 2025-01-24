<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Text\CamelCase as CamelCase;
use CeusMedia\Common\Exception\Deprecation as DeprecationException;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Logic as LogicResource;
use CeusMedia\HydrogenFramework\Logic\Abstraction as LogicAbstraction;
use CeusMedia\HydrogenFramework\Logic\Capsuled as CapsuledLogic;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;
use DomainException;
use InvalidArgumentException;
use ReflectionException;
use RuntimeException;

/**
 *	...
 *	Implements Property overloading.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class LogicPool
{
	/**	@var	Environment				$env		Environment object */
	protected Environment $env;

	/**	@var	array<string|object>	$pool		Map of logic class names or instances */
	protected array $pool				= [];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		object
	 *	@throws		ReflectionException
	 */
	public function __get( string $key )
	{
		return $this->get( $key );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		boolean
	 */
	public function __isset( string $key ): bool
	{
		return $this->has( $key );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string			$key				Key of logic object to store in pool
	 *	@param		string|object	$logicClassOrObject	Logic instance or class name to store in pool
	 *	@return		void
	 */
	public function __set( string $key, string|object $logicClassOrObject )
	{
		$this->set( $key, $logicClassOrObject );
	}

	/**
	 *	Magic.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		void
	 */
	public function __unset( string $key )
	{
		$this->remove( $key );
	}

	/**
	 *	Stores a logic component in pool by class name or instance.
	 *	Alias for set with "override" disabled.
	 *	@access		public
	 *	@param		string			$key				Key of logic object in pool
	 *	@param		string|object	$logicClassOrObject	Name of logic class to add
	 *	@return		self
	 */
	public function add( string $key, string|object $logicClassOrObject ): self
	{
		$this->set( $key, $logicClassOrObject, FALSE );
		return $this;
	}

	/**
	 *	Returns a stored logic object by its pool key
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		CapsuledLogic|SharedLogic
	 *	@throws		RuntimeException				if no class or object has been added for given key
	 *	@throws		DomainException					if class for given key is not existing
	 *	@throws		ReflectionException
	 */
	public function get( string $key ): CapsuledLogic|SharedLogic|LogicResource
	{
		if( $this->isInstantiated( $key ) ){
			/** @var CapsuledLogic|SharedLogic $instance */
			$instance	= $this->pool[$key];
			return $instance;
		}

		$className = NULL;
		if( !$this->has( $key ) )
			$this->add( $key, $this->getClassNameFromKey( $key ) );

		/** @var string $className */
		$className	= $this->pool[$key];
		if( !class_exists( $className ) )
			throw new DomainException( 'No logic class found for "'.$className.'"' );
		$instance	= $this->createInstance( $className );
		if( $instance instanceof SharedLogic )
			$this->set( $key, $instance );
		return $instance;
	}

	/**
	 *	Returns logic pool key for a logic class name.
	 *	@access		public
	 *	@param		string			$className		Name of class to create instance for
	 *	@return		string
	 *	@throws		InvalidArgumentException		if given class name os empty
	 *	@throws		InvalidArgumentException		if given class name is not starting with Logic_
	 */
	public function getKeyFromClassName( string $className ): string
	{
		if( '' === trim( $className ) )
			throw new InvalidArgumentException( 'Class name cannot be empty' );
		$parts	= explode( ' ', str_replace( '_', ' ', $className ) );
		$prefix	= array_shift( $parts );
		if( 'Logic' !== ltrim( $prefix, '\\' ) )
			throw new InvalidArgumentException( 'Given class is not a logic class (needs to start with Logic_)' );
		return CamelCase::encode( implode( ' ', $parts ) );
	}

	/**
	 *	Indicates whether a logic object is stored by its pool key.
	 *	@access		public
	 *	@param		string			$key			Key of logic object
	 *	@return		boolean
	 */
	public function has( string $key ): bool
	{
		return array_key_exists( $key, $this->pool );
	}

	/**
	 *	Returns list of keys of stored logic classes or objects.
	 *	@access		public
	 *	@return		array			List of keys of stored logic classes or objects
	 */
	public function index(): array
	{
		return array_keys( $this->pool );
	}

	/**
	 *	Indicates whether a pool key points to a class or to an object.
	 *	Returns NULL if neither a class nor an object is registered for key.
	 *	@access		public
	 *	@param		string			$key			Key of logic object in pool
	 *	@return		boolean
	 */
	public function isInstantiated( string $key ): bool
	{
		if( !$this->has( $key ) )
			return FALSE;
		return is_object( $this->pool[$key] );
	}

	/**
	 *	Removes a stored logic object by its pool key.
	 *	@access		public
	 *	@param		string			$key			Key of logic object in pool
	 *	@return		void
	 *	@throws		RuntimeException				if no logic is stored for pool key
	 */
	public function remove( string $key ): void
	{
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
	public function set( string $key, string|object $logicClassOrObject, bool $override = TRUE ): void
	{
		if( $this->has( $key ) && !$override )
			throw new RuntimeException( 'Logic "'.$key.'" is already in logic pool' );
		if( is_object( $logicClassOrObject ) ){
			if( !$logicClassOrObject instanceof LogicAbstraction && !$logicClassOrObject instanceof LogicResource )
				throw new InvalidArgumentException( 'Given logic is not a framework logic (use shared or capsuled logic)' );
		}

		$this->pool[$key]	= $logicClassOrObject;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Creates instance of logic class.
	 *	@access		protected
	 *	@param		string			$className			Name of class to create instance for
	 *	@return		CapsuledLogic|SharedLogic|LogicResource
	 *	@throws		DeprecationException			if class is not a subclass of CeusMedia\HydrogenFramework\Logic\* or CeusMedia\HydrogenFramework\Environment\Resource\Logic
	 *	@throws		ReflectionException
	 */
	protected function createInstance( string $className ): CapsuledLogic|SharedLogic|LogicResource
	{
		//  using the newer logic classes structure with abstract class
		if( is_subclass_of( $className, LogicAbstraction::class ) ){
			/** @var CapsuledLogic|SharedLogic $instance */
			$instance	= ObjectFactory::createObject( $className, [$this->env] );
			return $instance;
		}

		//  using the older environment resource as base for logic classes
		if( is_subclass_of( $className, LogicResource::class ) ){
			/** @var LogicResource $instance */
			$instance	= ObjectFactory::createObject( $className, [$this->env] );
			return $instance;
		}

		throw DeprecationException::create( 'Logic class without inheritance is deprecated' )
			->setDescription( 'Given class "'.$className.'" is not extending a framework logic class or environment resource (is not a subclass of CeusMedia\HydrogenFramework\Logic\* or CeusMedia\HydrogenFramework\Environment\Resource\Logic)' )
			->setSuggestion( 'consider to extend shared or capsuled logic from framework in your logic class' );
	}

	/**
	 *	Returns class name of registered logic pool key.
	 *	Logic pool key can be either a shortened class name (without prefix Logic_)
	 *	or a camel-cased shortened class name.
	 *	For example, logic pool key for class Logic_Catalog_Bookstore can be
	 *	Catalog_Bookstore or catalogBookstore.
	 *	@access		protected
	 *	@param		string			$key			Key of logic object
	 *	@return		string
	 */
	protected function getClassNameFromKey( string $key ): string
	{
		if( preg_match( '/^[A-Z]/', $key ) )
			return 'Logic_'.$key;
		$classNameWords	= ucwords( CamelCase::decode( $key ) );
		return str_replace( ' ', '_', 'Logic '.$classNameWords );
	}
}
