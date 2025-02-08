<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Console\Messenger as ConsoleMessenger;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

trait MessengerByCli
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

	protected function initMessenger(): self
	{
		$this->messenger	= new ConsoleMessenger( $this );
		return $this;
	}
}
