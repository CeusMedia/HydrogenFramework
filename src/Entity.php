<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);

/**
 *	Base class for model entity classes.
 *	@category		Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Common
 */

namespace CeusMedia\HydrogenFramework;

use ArrayAccess;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Exception\Data\Missing as MissingException;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\Common\Exception\Runtime as RuntimeException;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 *	Base class for model entity classes.
 *	@category		Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Common
 *	@implements		ArrayAccess<string, mixed>
 */
class Entity implements ArrayAccess
{
	protected static array $mandatoryFields			= [];
	protected static array $presetValues			= [];
	protected static array $autoTypeConvertFields	= [];

	/**
	 *	@param		array		$data
	 *	@return		static
	 *	@throws		ReflectionException		if reflection of entity class property failed
	 *	@throws		NotSupportedException	entity property is typed as union or intersection
	 */
	public static function fromArray( array $data ): static
	{
		$className	= static::class;
		$instance	= new $className();
		$instance->importDataFromArray( $data );
		return $instance;
	}

	/**
	 *	@param		Dictionary		$dictionary
	 *	@return		static
	 *	@throws		ReflectionException		if reflection of entity class property failed
	 *	@throws		NotSupportedException	entity property is typed as union or intersection
	 */
	public static function fromDictionary( Dictionary $dictionary ): static
	{
		/** @var array $data */
		$data	= $dictionary->getAll();
		return self::fromArray( $data );
	}

	/**
	 *	Construction of a new entity.
	 *	There are two ways:
	 *	1. by PDO fetch
	 *	2. by construction arguments, as dictionary or array map
	 *	On both ways, default values will be set statically and dynamically.
	 *	Only on manual construction, given data will be checked against a list of
	 *	mandatory fields and sane values.
	 *	@param		Dictionary|array<string,string|int|float|NULL>		$data
	 *	@throws		ReflectionException		if reflection of entity class property failed
	 *	@throws		NotSupportedException	entity property is typed as union or intersection
	 */
 	public function __construct( Dictionary|array $data = [] )
	{
		$backtrace	= debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
		foreach( $backtrace as $trace )
			if( 'PDOStatement' === ( $trace['class'] ?? '' ) )
				return;

		/** @var array $array */
		$array	= $data instanceof Dictionary ? $data->getAll() : $data;
		$this->importDataFromArray( $array );
	}

	/**
	 *	@param		array		$array
	 *	@return		void
	 *	@throws		ReflectionException		if reflection of entity class property failed
	 *	@throws		NotSupportedException	entity property is typed as union or intersection
	 */
	protected function importDataFromArray( array $array = [] ): void
	{
		$array	= static::presetStaticValues( $array );
		$array	= static::presetDynamicValues( $array );

		static::checkMandatoryFields( $array );								//  check for mandatory fields
		static::convertTypes( $array );									//  convert types if defined and necessary
		static::checkValues( $array );										//  check for sane values

		/**
		 * @var string $key
		 * @var string|int|float|NULL $value
		 */
		foreach( $array as $key => $value )
			$this->set( $key, $value );
	}

	/**
	 *	@param		string		$key
	 *	@return		bool|int|float|string|array|object|NULL
	 */
	public function get( string $key ): bool|int|float|string|array|object|NULL
	{
		if( static::isPublicProperty( $key ) )
//			/** @not-phpstan-ignore-next-line */
			return $this->$key;
		return NULL;
	}

	/**
	 *	Indicates whether a field key is backed by a (public) property and its value has been set.
	 *	Attention: Returns FALSE if value is NULL, even if property exists and is public.
	 *	@param		string		$key
	 *	@param		bool		$allowNull		Flag: return positive if value is NULL, default: no
	 *	@return		bool
	 *	@todo		think about NULL-behaviour: Dictionary allows NULL-values, this entity not (by default)
	 */
	public function has( string $key, bool $allowNull = FALSE ): bool
	{
		if( !static::isPublicProperty( $key ) )
			return FALSE;

		if( !$allowNull )
//			/** @not-phpstan-ignore-next-line */
			return NULL !== $this->$key;
		return TRUE;
	}

	/**
	 *	Implements ArrayAccess interface.
	 *	@param		mixed		$offset		ArrayAccess offset string
	 *	@return		bool
	 */
	public function offsetExists( mixed $offset ): bool
	{
		return $this->has( $offset );
	}

	/**
	 *	Implements ArrayAccess interface.
	 *	@param		mixed		$offset		ArrayAccess offset string
	 *	@return		mixed
	 */
	public function offsetGet( mixed $offset ): mixed
	{
		return $this->get( $offset );
	}

	/**
	 *	Implements ArrayAccess interface.
	 *	@param		mixed		$offset		ArrayAccess offset string
	 *	@param		mixed		$value		Value to set
	 *	@return		void
	 */
	public function offsetSet( mixed $offset, mixed $value ): void
	{
		if( NULL === $offset )
			throw RuntimeException::create( 'Key must not be null' );
		$this->set( $offset, $value );
	}

	/**
	 *	Implements ArrayAccess interface.
	 *	@param		mixed		$offset		ArrayAccess offset string
	 *	@return		void
	 */
	public function offsetUnset( mixed $offset ): void
	{
		$this->set( $offset );
	}

	/**
	 *	@param		string									$key
	 *	@param		bool|int|float|string|array|object|NULL	$value
	 *	@return		static
	 */
	public function set( string $key, bool|int|float|string|array|object $value = NULL ): static
	{
		if( static::isPublicProperty( $key ) )
//			/** @not-phpstan-ignore-next-line */
			$this->$key	= $value;
		return $this;
	}

	/**
	 *	Returns map of fields (public properties) as array.
	 *	@return		array
	 */
	public function toArray(): array
	{
		return array_filter( get_object_vars( $this ), function ($key){
			return static::isPublicProperty( $key );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 *	Returns map of fields (public properties) as dictionary.
	 *	@return		Dictionary
	 */
	public function toDictionary(): Dictionary
	{
		return new Dictionary( $this->toArray() );
	}


	//  --  PROTECTED  --  //


	protected static function checkMandatoryFields( array $data ): void
	{
		foreach( static::$mandatoryFields as $key )
			if( !array_key_exists( $key, $data ) )
				throw MissingException::create( 'Missing data for key "'.$key.'"' );
	}

	/**
	 *	Checks sanity of values.
	 *	Method is empty by default, can be extended for custom handling on your entities.
	 *	Throw explanatory exceptions on failure.
	 *	@param		array		$data		Reference to data array to work on
	 *	@return		void
	 */
	protected static function checkValues( array $data ): void
	{
	}

	/**
	 *	Apply changes directly to the given array reference.
	 *	@param		array $array
	 *	@return		void
	 *	@throws		ReflectionException		if reflection of entity class property failed
	 *	@throws		NotSupportedException	entity property is typed as union or intersection
	 */
	protected static function convertTypes( array & $array ): void
	{
		if( [] === static::$autoTypeConvertFields )
			return;

		$reflectedClass	= new ReflectionClass( static::class );
		foreach( static::$autoTypeConvertFields as $key ){
			if( !isset( $array[$key] ) )
				continue;

			$reflectedProperty	= $reflectedClass->getProperty( $key );
			if( !$reflectedProperty->hasType() )
				continue;
			$array[$key]	= self::convertTypeAccordingToReflection( $array[$key], $reflectedProperty );
		}
	}

	/**
	 *	Indicates whether a field (or column) name leads to a public member / property.
	 *	Is used by several methods to enable reading from and writing to entity property.
	 *	@param		string		$key
	 *	@return		bool
	 */
	protected static function isPublicProperty( string $key ): bool
	{
		if( !property_exists( static::class, $key ) )
			return FALSE;
		$reflection	= new ReflectionProperty( static::class, $key );
		return $reflection->isPublic();
	}

	/**
	 *	Applies preset values dynamically created on manual construction.
	 *	Method is empty by default, can be extended for custom handling on your entities.
	 *	@param		array		$array		Data array to work on
	 *	@return		array
	 */
	protected static function presetDynamicValues( array $array ): array
	{
		return $array;
	}

	/**
	 *	Applies preset fixed values on manual construction.
	 *	Method extends given array by statically defined preset values.
	 *	Sets fields only, if not set in given array.
	 *	Method can be extended for custom handling on your entities.
	 *	@param		array		$array		Data array to work on
	 *	@return		array
	 */
	protected static function presetStaticValues( array $array ): array
	{
		return array_merge( static::$presetValues, $array );
	}


	//  --  PRIVATE  --  //

	/**
	 *	@param		mixed				$value
	 *	@param		ReflectionProperty	$reflectedProperty
	 *	@return		object|mixed
	 *	@throws		ReflectionException		if reflection of entity class property failed
	 *	@throws		NotSupportedException	entity property is typed as union or intersection
	 */
	private static function convertTypeAccordingToReflection( mixed $value, ReflectionProperty $reflectedProperty ): mixed
	{
		$reflectedType		= $reflectedProperty->getType();
		if( NULL === $reflectedType )
			return $value;

		if( ReflectionUnionType::class === $reflectedType::class ){
			throw NotSupportedException::create( 'Union typed auto convert is not supported, yet' );
/*			foreach( $reflectedType->getTypes() as $tt )
				if( $tt->getName() === $value )
					continue 2;
			foreach( $reflectedType->getTypes() as $tt )
				...*/
		}

		if( ReflectionIntersectionType::class === $reflectedType::class ){
			throw NotSupportedException::create( 'Intersection typed auto convert is not supported, yet' );
		}

		if( ReflectionNamedType::class === $reflectedType::class ){
			if( $reflectedType->isBuiltin() ){
				settype( $value, $reflectedType->getName() );
				return $value;
			}
			$reflectedTypeClass	= $reflectedType->getName();
			if( class_exists( $reflectedTypeClass ) ){
				return ObjectFactory::createObject( $reflectedTypeClass, [$value] );
			}
		}

		return $value;
	}
}
