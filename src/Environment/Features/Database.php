<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use ReflectionException;

trait Database
{
	/**	@var	object|NULL					$database		Database Connection Object */
	protected ?object $database				= NULL;

	/**
	 *	@return		object|NULL
	 */
	public function getDatabase(): ?object
	{
		return $this->database;
	}
	/**
	 *	Sets up database support.
	 *	Calls hook Env::initDatabase to get resource.
	 *	Calls hook Database::init if resource is available and retrieved
	 *	@access		protected
	 *	@return		static
	 *	@todo		implement database connection pool/manager
	 *	@throws		ReflectionException
	 */
	protected function initDatabase(): static
	{
		$data	= ['managers' => []];
		$this->captain->callHook( 'Env', 'initDatabase', $this, $data );									//  call events hooked to database init
		if( count( $data['managers'] ) ){
			$this->database	= current( $data['managers'] );
			$this->modules->callHook( 'Database', 'init', $this->database );									//  call events hooked to database init
		}
		$this->runtime->reach( 'env: database', 'Finished setup of database connection.' );
		return $this;
	}
}
