<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Resource\Php as PhpResource;

trait Php
{
	/**	@var	PhpResource					$php			Instance of PHP environment collection */
	public PhpResource $php;

	/**
	 *	Returns PHP configuration and version management.
	 *	@access		public
	 *	@return		PhpResource
	 */
	public function getPhp(): PhpResource
	{
		/** @var PhpResource $resource */
		$resource	= $this->get( 'php' );
		return $resource;
	}

	protected function initPhp(): static
	{
		$this->php	= new PhpResource( $this );
		return $this;
	}
}
