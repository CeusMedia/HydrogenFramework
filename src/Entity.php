<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

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
 * @implements ArrayAccess<string, mixed>
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
		$array	= array_merge( self::$presetValues, $array );

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
	 *	@return		int|float|string|array|object|NULL
	 */
	public function get( string $key ): int|float|string|array|object|NULL
	{
		return self::isPublicProperty( $key ) ? $this->$key : NULL;
	}

	/**
	 *	@param		string		$key
	 *	@return		bool
	 */
	public function has( string $key ): bool
	{
		return self::isPublicProperty( $key ) && NULL !== $this->$key;
	}

	/**
	 *	@param		string								$key
	 *	@param		int|float|string|array|object|NULL	$value
	 *	@return		self
	 */
	public function set( string $key, int|float|string|array|object|null $value ): self
	{
		if( property_exists( $this, $key ) )
			$this->$key	= $value;
		return $this;
	}

	/**
	 *	@return		array
	 */
	public function toArray(): array
	{
		return array_map( function( $value ){
			return $value;
		}, get_object_vars( $this ) );
	}


	//  --  PROTECTED  --  //


	protected static function checkMandatoryFields( array $data ): void
	{
		foreach( self::$mandatoryFields as $key )
			if( !array_key_exists( $key, $data ) )
				throw MissingException::create( 'Missing data for key "'.$key.'"' );
	}

	protected static function checkValues( array $data ): void
	{
	}

	protected static function isPublicProperty( string $key ): bool
	{
		if( !property_exists( static::class, $key ) )
			return FALSE;
		$reflection	= new ReflectionProperty( static::class, $key );
		return $reflection->isPublic();
	}

	public function offsetExists( mixed $offset ): bool
	{
		return $this->has( $offset );
	}

	public function offsetGet( mixed $offset ): mixed
	{
		return $this->get( $offset );
	}

	public function offsetSet( mixed $offset, mixed $value ): void
	{
		if( NULL === $offset )
			throw Runtime::create( 'Key must not be null' );
		$this->set( $offset, $value );
	}

	public function offsetUnset( mixed $offset ): void
	{
		$this->set( $offset, NULL );
	}
}