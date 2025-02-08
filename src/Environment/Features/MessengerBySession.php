<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

trait MessengerBySession
{
	use RuntimeFeature;

	/** @var	MessengerResource|NULL		$messenger		Messenger Object */
	protected ?MessengerResource $messenger	= NULL;

	/**
	 *	Returns Messenger Object.
	 *	@access		public
	 *	@return		MessengerResource|NULL
	 */
	public function getMessenger(): ?MessengerResource
	{
		return $this->messenger;
	}

	/**
	 *	@param		bool|NULL		$enabled		NULL means "autodetect" and defaults to yes, if response shall be HTML
	 *	@return		static
	 */
	protected function initMessenger( ?bool $enabled = NULL ): static
	{
		if( NULL === $enabled ){																	//  auto detect mode
			$acceptHeader	= (string) getEnv( 'HTTP_ACCEPT' );
			$enabled		= str_contains( $acceptHeader, 'html' );								//  enabled if HTML is requested
		}
		$this->messenger	= new MessengerResource( $this, $enabled );
		$this->runtime->reach( 'env: messenger' );
		return $this;
	}
}
