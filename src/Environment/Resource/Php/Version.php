<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Php;

use RuntimeException;

class Version
{
	protected string $version	= '0';

	public function __construct()
	{
		if( FALSE === phpversion() )
			throw new RuntimeException( 'No PHP version information available' );
		$this->version	= phpversion();
	}

	public function equalsTo( string $version ): bool
	{
		return $this->compare( $version, '==' );
	}

	public function get(): string
	{
		return $this->version;
	}

	public function has( string $version ): bool
	{
		return $this->isAtLeast( $version );
	}

	public function isAtLeast( string $version ): bool
	{
		return $this->compare( $version, '>=' );
	}

	public function isAtMost( string $version ): bool
	{
		return $this->compare( $version, '<=' );
	}

	public function isGreaterThan( string $version ): bool
	{
		return $this->compare( $version, '>' );
	}

	public function isLowerThan( string $version ): bool
	{
		return $this->compare( $version, '<' );
	}

	//  --  PROTECTED  --  //

	/**
	 *	...
	 *	@access		protected
	 *	@param		string			$version		PHP version to compare current version to
	 *	@param		string			$comparator		Comparison operator (<,>,<=,>=)
	 *	@return		boolean
	 */
	protected function compare( string $version, string $comparator ): bool
	{
		return version_compare( $this->version, $version, $comparator );
	}
}
