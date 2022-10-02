<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Component;

class File
{
	public string $file;

	public $load;

	public $level;

	public $source;

	public function __construct( $file )
	{
		$this->file		= $file;
	}
}
