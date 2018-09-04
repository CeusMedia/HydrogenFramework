<?php
class CMF_Hydrogen_Environment_Resource_Php_Version{

	protected $version;

	public function __construct(){
		$this->version	= phpversion();
	}

	public function equalsTo( $version ){
		return $this->compare( $version, '==' );
	}

	public function get(){
		return $this->version;
	}

	public function has( $version ){
		return $this->isAtLeast( $version );
	}

	public function isAtLeast( $version ){
		return $this->compare( $version, '>=' );
	}

	public function isAtMost( $version ){
		return $this->compare( $version, '<=' );
	}

	public function isGreaterThan( $version ){
		return $this->compare( $version, '>' );
	}

	public function isLowerThan( $version ){
		return $this->compare( $version, '<' );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		string			$version		PHP version to compare current version to
	 *	@param		string			$comparator		Comparison operator (<,>,<=,>=)
	 *	@return		boolean
	 */
	protected function compare( $version, $comparator ){
		return version_compare( $this->version, $version, $comparator );
	}
}
