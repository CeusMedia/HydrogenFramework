<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2021 Ceus Media
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
 *	@copyright		2014-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Profiler
{
	/**	@var	Clock		$clock		Inner stopwatch with lap support */
	protected $clock	= NULL;

	protected $enabled	= TRUE;

	public function __construct( bool $enabled = TRUE )
	{
		$this->enabled	= (bool) $enabled;
		if( $this->enabled )
			$this->clock	= new Clock();
	}

	public function tick( string $message, string $description = NULL )
	{
		if( $this->enabled )
			$this->clock->stopLap( 0, 0, $message, $description );
	}

	public function get(): array
	{
		if( $this->enabled )
			return $this->clock->getLaps();
		return array();
	}
}
