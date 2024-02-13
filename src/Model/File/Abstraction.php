<?php
namespace CeusMedia\HydrogenFramework\Model\File;

//use CeusMedia\HydrogenFramework\Environment;

abstract class Abstraction
{
	protected string $path;

	/**
	 *	@todo		enable env to be first argument, after thinking twice
	 */
	public function __construct( /*Environment $env,*/ string $path )
	{
		$this->path	= $path;
	}

	/**
	 *	@param		string		$fileName
	 *	@param		mixed		$content
	 *	@return		int|FALSE
	 */
	abstract public function create( string $fileName, mixed $content ): int|FALSE;

	/**
	 *	@param		string		$fileName
	 *	@return		bool
	 */
	abstract public function delete( string $fileName ): bool;

	/**
	 *	@param		string		$fileName
	 *	@return		bool
	 */
	abstract public function exists( string $fileName ): bool;

	/**
	 *	@param		string		$fileName
	 *	@return		mixed
	 */
	abstract public function read( string $fileName );

	/**
	 *	@param		string		$fileName
	 *	@param		mixed		$content
	 *	@return		int|FALSE
	 */
	abstract public function update( string $fileName, mixed $content ): int|FALSE;
}
