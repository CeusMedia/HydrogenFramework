<?php

/**
 *	Base class for model entity classes.
 *	@category		Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Common
 */
namespace CeusMedia\HydrogenFramework;

class Entity
{
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
	 *	@param		array		$data
	 */
	public function __construct( array $data = [] )
	{
		foreach( $data as $key => $value )
			if( property_exists( $this, $key ) )
				$this->set( $key, $value );
	}

	/**
	 *	@param		string		$key
	 *	@return		int|float|string|array|object|NULL
	 */
	public function get( string $key ): int|float|string|array|object|NULL
	{
		if( property_exists( $this, $key ) )
			return $this->$key;
		return NULL;
	}

	/**
	 *	@param		string		$key
	 *	@return		bool
	 */
	public function has( string $key ): bool
	{
		return property_exists( $this, $key ) && NULL !== $this->$key;
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
}