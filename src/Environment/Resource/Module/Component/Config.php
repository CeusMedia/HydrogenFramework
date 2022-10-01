<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Component;

class Config
{
	public string $key;

	public $value;

	public ?string $type;

	public $values;

	public ?bool $mandatory;

	public ?string $protected;

	public ?string $title;

	public function __construct( $key, $value, $type = NULL, $title = NULL )
	{
		$this->key		= $key;
		$this->value	= $value;
		$this->type		= $type;
		$this->title	= $title;
	}
}
