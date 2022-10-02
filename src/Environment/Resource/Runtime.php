<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\Alg\Time\Clock as Clock;
use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Runtime\Profiler as Profiler;

class Runtime
{
	public Profiler $profiler;

	/**	@var	Clock			$clock */
	protected Clock $clock;

	/**	@var	Environment		$env */
	protected Environment $env;

	/**
	 *	Constructor.
	 *	@access		public
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->clock	= new Clock();
		$this->profiler	= new Profiler( $this, TRUE );
	}

	/**
	 *	Returns the current time passed since start.
	 *	@param		int			$base			0:seconds, 3:milliseconds: 6: microseconds
	 *	@param		int			$precision		Number of decimal places
	 *	@return		float
	 */
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

	/**
	 *	Returns list of reached goals.
	 *	@param		int			$base			0:seconds, 3:milliseconds: 6: microseconds
	 *	@param		int			$precision		Number of decimal places
	 *	@return		array
	 */
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

	/**
	 *	@param		int		$base
	 *	@param		int		$round
	 *	@return		float
	 */
	public function stop( int $base = 3, int $round = 3 ): float
	{
		$this->markDeprecation( 'stop' );
		return $this->get( $base, $round );
	}

	/**
	 *	@param		int				$base
	 *	@param		int				$round
	 *	@param		string|NULL		$label
	 *	@param		string|NULL		$description
	 *	@return		int
	 */
	public function stopLap( int $base = 3, int $round = 3, ?string $label = NULL, ?string $description = NULL ): int
	{
		$this->markDeprecation( 'stopLap' );
		return $this->reach( $label, $description );
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
				$message	= 'Environment clock $env->getClock()->stop() is deprecated. Use $env->getRuntime()->get() instead';
				break;
			case 'stopLap':
				$message	= 'Environment clock $env->getClock()->stopLap() is deprecated. Use $env->getRuntime()->reach() instead';
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
