<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Component;

class Config
{
	public const PROTECTED_NO		= 'no';
	public const PROTECTED_YES		= 'yes';
	public const PROTECTED_USER		= 'user';

	public string $key;

	/** @var	string|int|float	$value */
	public $value;

	/** @var	string|NULL			$type */
	public ?string $type;

	public ?array $values;

	/** @var	bool				$mandatory */
	public bool $mandatory			= FALSE;

	public ?string $protected;

	/** @var	string|NULL			$title */
	public ?string $title;

	/**
	 *	@param		string				$key
	 *	@param		string|int|float	$value
	 *	@param		string|NULL			$type
	 *	@param		string|NULL			$title
	 */
	public function __construct( string $key, $value, ?string $type = NULL, ?string $title = NULL )
	{
		$this->key		= $key;
		$this->value	= $value;
		$this->type		= $type;
		$this->title	= $title;
	}
}
