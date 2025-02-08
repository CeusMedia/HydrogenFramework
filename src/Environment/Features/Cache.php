<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Cache\SimpleCacheInterface;
use CeusMedia\Cache\SimpleCacheFactory;
use CeusMedia\HydrogenFramework\Environment\Features\Modules as ModulesFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;
use ReflectionException;

trait Cache
{
	use ModulesFeature;
	use RuntimeFeature;

	/**	@var	SimpleCacheInterface		$cache			Instance of simple cache adapter */
	protected SimpleCacheInterface $cache;

	/**
	 *	@return		SimpleCacheInterface
	 */
	public function getCache(): SimpleCacheInterface
	{
		return $this->cache;
	}

	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function initCache(): static
	{
		$this->cache	= SimpleCacheFactory::createStorage('Noop' );
		$this->modules->callHook( 'Env', 'initCache', $this );						//  call related module event hooks
		$this->runtime->reach( 'env: initCache', 'Finished setup of cache' );
		return $this;
	}
}
