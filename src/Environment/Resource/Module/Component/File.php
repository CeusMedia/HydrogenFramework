<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Component;

class File
{
	public $file;

	public $load;

	public $level;

	public $source;

	public function __construct( $file )
	{
		$this->file		= $file;
	}
}
