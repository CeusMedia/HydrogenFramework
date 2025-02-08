<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Resource\Log as LogResource;

trait Log
{
	public static array $defaultLoggingStrategies	= [
		LogResource::STRATEGY_MODULE_HOOKS,													//  prepend hook based logging strategy
		LogResource::STRATEGY_APP_DEFAULT,													//  default app logging strategy
	];

	/**	@var	LogResource|NULL			$log			Log support object */
	protected ?LogResource $log				= NULL;

	/**
	 *	@return		LogResource|NULL
	 */
	public function getLog(): ?LogResource
	{
		return $this->log;
	}

	/**
	 *	Creates a logger factory and log channels depending on configuration.
	 *	Uses several strategies to report to different or multiple log targets.
	 * 	Strategies defined in framework environment resource:
	 *		- APP_DEFAULT		= enqueue to file 'logs/app.log'
	 *		- APP_TYPED			- enqueue to file 'logs/app.TYPE.log' for types as info, note, warn, error or exception
	 *		- MODULE_HOOKS		- call default module hooks for specific handling
	 *		- CUSTOM_HOOKS		- call custom module hooks for specific handling
	 *		- CUSTOM_CALLBACK	- call injected method, for testing
	 *		- MEMORY			- log in memory, for testing
	 *
	 *	@access		protected
	 *	@return		static
	 */
	protected function initLog(): static
	{
		$this->log	= new LogResource( $this );
		$this->log->setStrategies( static::$defaultLoggingStrategies );								//  set new logging strategies list
		return $this;
	}
}
