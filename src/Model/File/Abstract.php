<?php
abstract class CMF_Hydrogen_Model_File_Abstract
{
	protected $path;

	/**
	 *	@todo		enable env to be first argument, after thinking twice
	 */
	public function __construct( /*CMF_Hydrogen_Environment $env,*/ string $path )
	{
		$this->path	= $path;
	}

	abstract public function create( string $fileName, $content );

	abstract public function delete( string $fileName );

	abstract public function exists( string $fileName );

	abstract public function read( string $fileName );

	abstract public function update( string $fileName, $content );
}
