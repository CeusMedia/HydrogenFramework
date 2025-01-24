<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Logic;

use CeusMedia\HydrogenFramework\Environment;
use ReflectionException;

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Shared extends Abstraction
{
	/**
	 *	@param		Environment			$env
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	public static function getInstance( Environment $env ): static
	{
		$logicPool	= $env->getLogic();
		$className	= static::class;
		$key		= $logicPool->getKeyFromClassName( $className );
		if( $logicPool->has( $key ) ){
			/** @var static $instance */
			$instance	= $logicPool->get( $key );
			return $instance;
		}
		$instance	= new $className( $env );
		$logicPool->add( $key, $instance );
		return $instance;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Cloning this logic is not allowed.
	 *	@return		void
	 *	@codeCoverageIgnore
	 */
	protected function __clone()
	{
	}
}
