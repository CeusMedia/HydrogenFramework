<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Exception\Deprecation as DeprecationException;
use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Disclosure as DisclosureResource;

trait Disclosure
{
	use RuntimeFeature;

	/**	@var	array						$disclosure		Map of classes ready to reflect */
	protected array $disclosure				= [];

	/**
	 *	@return		array|null
	 *	@throws		DeprecationException
	 *	@deprecated	use module Resource_Disclosure instead
	 */
	public function getDisclosure(): ?array
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.8' )
			->setExceptionVersion( '0.9' )
			->message( 'Environment::getDisclosure is deprecated. Use module Resource_Disclosure instead' );
		return $this->disclosure;
	}

	/**
	 *	@access		protected
	 *	@return		static
	 *	@todo		why not keep the resource object instead of reflected class list? would need refactoring of resource and related modules, thou...
	 *	@todo		extract to resource module: question is where to store the resource? in env again?
	 *	@todo		to be deprecated in 0.9: please use module Resource_Disclosure instead
	 */
	protected function initDisclosure(): static
	{
		$disclosure			= new DisclosureResource( [] );
		$this->disclosure	= $disclosure->reflect( 'classes/Controller/', ['classPrefix' => 'Controller_'] );
		$this->runtime->reach( 'env: disclosure', 'Finished setup of self disclosure handler.' );
		return $this;
	}
}
