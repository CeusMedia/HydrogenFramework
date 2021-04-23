<?php

use Alg_Time_Clock as Clock;
use CMF_Hydrogen_Environment as Environment;
use CMF_Hydrogen_Environment_Resource_Runtime_Profiler as Profiler;
use CMF_Hydrogen_Environment_Resource_Runtime as Runtime;

class CMF_Hydrogen_Environment_Resource_Runtime
{
	public $profiler;

	/**	@var	Clock			$clock */
	protected $clock;

	/**	@var	Environment		$env */
	protected $env;

	/**
	 *	Constructur.
	 *	@access		public
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->clock	= new Clock();
		$this->profiler	= new Profiler( $this, TRUE );
	}

	public function get( int $base = 3, int $precision = 3 ): float
	{
		return $this->clock->stop( $base, $precision );
	}

	/**
	 *	...
	 *	@param		string			$goal			Identifier of goal
	 *	@param		string|NULL		$description	Optional description of goal
	 *	@return		integer			Number of goals reached at this point
	 */
	public function reach( string $goal, ?string $description = NULL ): int
	{
		$this->clock->stopLap( 0, 0, $goal, $description );
		return count( $this->clock->getLaps() );
	}

	public function getGoals( int $base = 3, int $precision = 3 ): array
	{
		$list	= [];
		foreach( $this->clock->getLaps() as $lap ){
			$list[]	= (object) [
				'time'			=> round( $lap['timeMicro'] * pow( 10, $base - 6 ), $precision ),
				'timeMicro'		=> $lap['timeMicro'],
				'total'			=> round( $lap['totalMicro'] * pow( 10, $base - 6 ), $precision ),
				'totalMicro'	=> $lap['totalMicro'],
				'label'			=> $lap['label'],
				'description'	=> $lap['description'],
			];
		}
		return $list;
	}

	//  --  LEGACY  --  //

	public function stop( $base = 3, $round = 3 )
	{
		$this->markDeprecation();
		return $this->get( $base, $round );
	}

	public function stopLap( $base = 3, $round = 3, $label = NULL, $description = NULL )
	{
		$this->markDeprecation();
		return $this->reach( $base, $round );
	}

	public function sleep( $seconds )
	{
		$this->markDeprecation();
		$this->clock->sleep( $seconds );
	}

	public function speed( $seconds )
	{
		$this->markDeprecation();
		$this->clock->speed( $seconds );
	}

	public function usleep( $microseconds )
	{
		$this->markDeprecation();
		$this->clock->usleep( $microseconds );
	}

	public function uspeed( $microseconds )
	{
		$this->markDeprecation();
		$this->clock->uspeed( $microseconds );
	}

	protected function markDeprecation()
	{
		CMF_Hydrogen_Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Use module $[this->]env->getRuntime() instead' );
	}
}

class CMF_Hydrogen_Environment_Resource_Runtime_Profiler
{
	protected $enabled;
	protected $runtime;

	public function __construct( Runtime $runtime, bool $enabled = FALSE )
	{
		$this->runtime	= $runtime;
		$this->enabled	= $enabled;
	}

	public function tick( string $message, string $description = NULL )
	{
		$this->markDeprecation();
		if( $this->enabled )
			$this->runtime->reach( $message, $description );
	}

	public function get(): array
	{
		$this->markDeprecation();
		$list	= [];
		if( $this->enabled ){
			foreach( $this->runtime->getGoals() as $goal )
				$list[]	= (array) $goal;
		}
		return $list;
	}

	protected function markDeprecation()
	{
		CMF_Hydrogen_Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Use $[this->]env->getClock()->reach() instead' );
	}
}
