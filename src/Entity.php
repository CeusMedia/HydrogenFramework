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
use CeusMedia\Common\Exception\Data\Missing as MissingException;
use CeusMedia\Common\Exception\Runtime;
use ReflectionProperty;

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
	protected static array $mandatoryFields		= [];
	protected static array $presetValues		= [];

	/**
	 *	@param		array		$data
	 *	@return		static
	 */
	public static function fromArray( array $data ): static
	{
		$className	= static::class;
		return new $className( $data );
	}

	/**
	 *	@param		Dictionary		$dictionary
	 *	@return		static
	 */
	public static function fromDictionary( Dictionary $dictionary ): static
	{
		$className	= static::class;
		return new $className( $dictionary );
	}

	/**
	 *	@param		Dictionary|array<string,string|int|float|NULL>		$data
	 */
	public function __construct( Dictionary|array $data = [] )
	{
		/** @var array $array */
		$array	= ( $data instanceof Dictionary ) ? $data->getAll() : $data;

		self::presetStaticValues( $array );
		self::presetDynamicValues( $array );
		self::checkMandatoryFields( $array );
		self::checkValues( $array );

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
		if( self::isPublicProperty( $key ) )
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
		if( !self::isPublicProperty( $key ) )
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
			throw Runtime::create( 'Key must not be null' );
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
	 *	@return		self
	 */
	public function set( string $key, bool|int|float|string|array|object $value = NULL ): self
	{
		if( self::isPublicProperty( $key ) )
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
		foreach( self::$mandatoryFields as $key )
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
	 *	Applies preset values dynamically created on construction.
	 *	Method is empty by default, can be extended for custom handling on your entities.
	 *	Apply your changes directly to the given array reference.
	 *	@param		array		$array		Reference to data array to work on
	 *	@return		void
	 */
	protected static function presetDynamicValues( array & $array ): void
	{
	}

	/**
	 *	Applies preset fixed values on construction.
	 *	Method extends given array by statically defined preset values.
	 *	Sets fields only, if not set in given array.
	 *	Method can be extended for custom handling on your entities.
	 *	Apply your changes directly to the given array reference.
	 *	@param		array		$array		Reference to data array to work on
	 *	@return		void
	 */
	protected static function presetStaticValues( array & $array ): void
	{
		$array	= array_merge( self::$presetValues, $array );
	}
}