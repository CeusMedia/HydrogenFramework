<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2022 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\Alg\Time\Clock as Clock;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2022 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Profiler
{
	/**	@var	Clock|NULL		$clock		Inner stopwatch with lap support */
	protected $clock			= NULL;

	/**	@var	bool			$enabled	... */
	protected $enabled			= TRUE;

	public function __construct( bool $enabled = TRUE )
	{
		$this->enabled	= $enabled;
		if( $this->enabled )
			$this->clock	= new Clock();
	}

	public function tick( string $message, ?string $description = NULL ): float
	{
		if( $this->enabled )
			$this->clock->stopLap( 0, 0, $message, $description );
		return .0;
	}

	public function get(): array
	{
		if( $this->enabled )
			return $this->clock->getLaps();
		return [];
	}
}
