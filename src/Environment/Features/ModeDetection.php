<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\ConfigByIni as ConfigByIniFeature;
use RangeException;
use RuntimeException;

trait ModeDetection
{
	use ConfigByIniFeature;

	/**	@var	integer						$mode			Environment mode (dev,test,live,...) */
	protected int $mode						= 0;

	protected function detectMode(): static
	{
		/** @var array $modes */
		$modes	= preg_split( '/[_.:;>#@\/-]/', strtolower( $this->config->get( 'app.mode', 'production' ) ) );
		foreach( $modes as $mode ){
			switch( $mode ){
				case 'dev':
				case 'devel':
					$this->mode		|= self::MODE_DEV;
					break;
				case 'test':
				case 'testing':
					$this->mode		|= self::MODE_TEST;
					break;
				case 'stage':
				case 'staging':
					$this->mode		|= self::MODE_STAGE;
					break;
				case 'live':
				case 'production':
					$this->mode		|= self::MODE_LIVE;
					break;
			}
		}
		return $this;
	}

	/**
	 *	Returns mode of environment.
	 *	@access		public
	 *	@return		integer
	 */
	public function getMode(): int
	{
		return $this->mode;
	}

	public function isInDevMode(): bool
	{
		return self::MODE_DEV === ( $this->mode & self::MODE_DEV );
	}

	public function isInLiveMode(): bool
	{
		return self::MODE_LIVE === ( $this->mode & self::MODE_LIVE );
	}

	public function isInStageMode(): bool
	{
		return self::MODE_STAGE === ( $this->mode & self::MODE_STAGE );
	}

	public function isInTestMode(): bool
	{
		return self::MODE_TEST === ( $this->mode & self::MODE_TEST );
	}

	/**
	 *	Sets environment mode.
	 *	Disabled for productive environments aka environments in LIVE mode.
	 *	@param		int		$mode		One of ::MODES
	 *	@return		static
	 *	@throws		RuntimeException	if current environments is in LIVE mode
	 *	@throws		RangeException		if an invalid mode has been given
	 */
	public function setMode( int $mode ): static
	{
		if( $this->isInLiveMode() )
			throw new RuntimeException( 'Setting environment mode is disabled on productive environments' );
		if( !in_array( $mode, self::MODES, TRUE ) )
			throw new RangeException( 'Invalid mode' );
		$this->mode	= $mode;
		return $this;
	}
}
