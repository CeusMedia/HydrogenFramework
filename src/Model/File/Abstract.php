<?php
abstract class CMF_Hydrogen_Model_File_Abstract{

	protected $path;

	public function __construct( $path ){
		$this->path	= $path;
	}

	abstract public function create( $fileName, $content );

	abstract public function delete( $fileName );

	abstract public function exists( $fileName );

	abstract public function read( $fileName );

	abstract public function update( $fileName, $content );
}
