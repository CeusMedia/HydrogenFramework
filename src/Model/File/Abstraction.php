<?php
namespace CeusMedia\HydrogenFramework\Model\File;

//use CeusMedia\HydrogenFramework\Environment;

abstract class Abstraction
{
	protected $path;

	/**
	 *	@todo		enable env to be first argument, after thinking twice
	 */
	public function __construct( /*Environment $env,*/ string $path )
	{
		$this->path	= $path;
	}

	abstract public function create( string $fileName, $content );

	abstract public function delete( string $fileName );

	abstract public function exists( string $fileName );

	abstract public function read( string $fileName );

	abstract public function update( string $fileName, $content );
}
