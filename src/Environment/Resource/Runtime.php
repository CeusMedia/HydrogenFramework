<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Runtime\Profiler as Profiler;

use Alg_Time_Clock as Clock;

class Runtime
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
		$this->markDeprecation( 'stop' );
		return $this->get( $base, $round );
	}

	public function stopLap( $base = 3, $round = 3, $label = NULL, $description = NULL )
	{
		$this->markDeprecation( 'stopLap' );
		return $this->reach( $base, $round );
	}

	public function sleep( $seconds )
	{
		$this->markDeprecation( 'sleep' );
		$this->clock->sleep( $seconds );
	}

	public function speed( $seconds )
	{
		$this->markDeprecation( 'speed' );
		$this->clock->speed( $seconds );
	}

	public function usleep( $microseconds )
	{
		$this->markDeprecation( 'usleep' );
		$this->clock->usleep( $microseconds );
	}

	public function uspeed( $microseconds )
	{
		$this->markDeprecation( 'uspeed' );
		$this->clock->uspeed( $microseconds );
	}

	protected function markDeprecation( string $type = NULL )
	{
		switch( $type ){
			case 'sleep':
				$message	= 'Environment clock $env->getClock()->sleep() is deprecated.';
				break;
			case 'speed':
				$message	= 'Environment clock $env->getClock()->speed() is deprecated.';
				break;
			case 'usleep':
				$message	= 'Environment clock $env->getClock()->usleep() is deprecated.';
				break;
			case 'uspeed':
				$message	= 'Environment clock $env->getClock()->uspeed() is deprecated.';
				break;
			case 'stop':
				$message	= 'Environment clock $env->getClock()->stop() is deprecated. Use module $env->getRuntime()->get() instead';
				break;
			case 'stopLap':
				$message	= 'Environment clock $env->getClock()->stopLap() is deprecated. Use module $env->getRuntime()->reach() instead';
				break;
			default:
				$message	= 'Environment clock $env->getClock() is deprecated. Use module $env->getRuntime() instead';
		}

		Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( $message );
	}
}
