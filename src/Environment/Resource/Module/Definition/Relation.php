<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition;

class Relation
{
	/**	@var		string			$id */
	public string $id;

	/**	@var		string			$type */
	public string $type;

	/**	@var		string			$source */
	public string $source;

	/**	@var		string			$version */
	public string $version;

	/**	@var		string			$relation */
	public string $relation;

	/**
	 *	@param		string			$id
	 *	@param		string			$type
	 *	@param		string			$source
	 *	@param		string			$version
	 *	@param		string			$relation
	 */
	public function __construct( string $id, string $type, string $source, string $version, string $relation )
	{
		$this->relation	= $relation;
		$this->type		= $type;
		$this->id		= $id;
		$this->source	= $source;
		$this->version	= $version;
	}
}