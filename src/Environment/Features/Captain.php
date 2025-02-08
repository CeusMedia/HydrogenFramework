<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;

trait Captain
{
	use RuntimeFeature;

	/**	@var	CaptainResource				$captain		Instance of captain */
	protected CaptainResource $captain;

	/**
	 *	@return		CaptainResource
	 */
	public function getCaptain(): CaptainResource
	{
		return $this->captain;
	}

	protected function initCaptain(): static
	{
		$this->captain	= new CaptainResource( $this );
		$this->runtime->reach( 'env: initCaptain', 'Finished setup of event handler.' );
		return $this;
	}
}
