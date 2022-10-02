<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Component;

class File
{
	/** @var	string				$file */
	public string $file;

	/** @var	bool|string|NULL	$load */
	public $load;

	/** @var	int|string|NULL		$level */
	public $level;

	/** @var	string|null			$source */
	public ?string $source;

	/**
	 *	@param		string		$file
	 */
	public function __construct( string $file )
	{
		$this->file		= $file;
	}
}
