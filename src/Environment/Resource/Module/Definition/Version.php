<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition;

class Version
{
	/**	@var		string			$current */
	public string $current;

	/**	@var		string|NULL		$installed */
	public ?string $installed;

	/**	@var		string|NULL		$available */
	public ?string $available;

	/**	@var		array			$log */
	public array $log;

	/**
	 *	@param		string			$current
	 */
	public function __construct( string $current )
	{
		$this->current	= $current;
	}}