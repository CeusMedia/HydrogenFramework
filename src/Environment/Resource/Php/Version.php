<?php
class CMF_Hydrogen_Environment_Resource_Php_Version
{
	protected $version;

	public function __construct()
	{
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
