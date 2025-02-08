<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use DomainException;
use InvalidArgumentException;

trait GetHasSet
{
	/**
	 *	@param		string		$key
	 *	@return		mixed|null
	 *	@throws		DomainException
	 */
	public function __get( string $key )
	{
		return $this->get( $key );
	}

	/**
	 *	@param		string		$key
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 *	@throws		DomainException			if no resource is registered by given key
	 */
	public function get( string $key, bool $strict = TRUE ): object|NULL
	{
		if( isset( $this->$key ) )
			return $this->$key;
		if( $strict ){
			$message	= 'No environment resource found for key "%1$s"';
			throw new DomainException( sprintf( $message, $key ) );
		}
		return NULL;
	}

	/**
	 *	Indicates whether a resource is an available object by its access method key.
	 *	@access		public
	 *	@param		string		$key		Resource access method key, i.e. session, language, request
	 *	@return		boolean
	 */
	public function has( string $key ): bool
	{
		$method		= 'get'.ucFirst( $key );
		$callable	= [$this, $method];
		if( is_callable( $callable ) )
			if( is_object( call_user_func( $callable ) ) )
				return TRUE;
		if( $this->$key ?? FALSE )
			return TRUE;
		return FALSE;
	}

	public function remove( string $key ): static
	{
		$this->$key	= NULL;
		return $this;
	}

	/**
	 *	@param		string		$key
	 *	@param		object		$object
	 *	@return		static
	 */
	public function set( string $key, object $object ): static
	{
		if( !is_object( $object ) ){
			$message	= 'Given resource "%1$s" is not an object';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		if( !preg_match( '/^\w+$/', $key ) ){
			$message	= 'Invalid resource key "%1$s"';
			throw new InvalidArgumentException( sprintf( $message, $key ) );
		}
		$this->$key	= $object;
		return $this;
	}
}