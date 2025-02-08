<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\GetHasSet as GetHasSetFeature;
use DomainException;

trait ArrayAccess
{
	use GetHasSetFeature;
	/**
	 *	@param		mixed		$offset
	 *	@return		bool
	 */
	public function offsetExists( mixed $offset ): bool
	{
//		return property_exists( $this, $key );														//  PHP 5.3
		return isset( $this->{strval( $offset )} );																//  PHP 5.2
	}

	/**
	 *	@param		mixed		$offset
	 *	@return		object|NULL
	 *	@throws		DomainException
	 */
	public function offsetGet( mixed $offset ): object|NULL
	{
		return $this->get( strval( $offset ) );
	}

	/**
	 *	@param		mixed		$offset
	 *	@param		mixed		$value
	 *	@return		void
	 */
	public function offsetSet( mixed $offset, mixed $value ): void
	{
		$this->set( strval( $offset ), $value );
	}

	public function offsetUnset( mixed $offset ): void
	{
		$this->remove( strval( $offset ) );
	}
}