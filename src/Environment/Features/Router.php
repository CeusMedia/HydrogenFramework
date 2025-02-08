<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Router\Abstraction as AbstractRouter;
use CeusMedia\HydrogenFramework\Environment\Router\Single as SingleRouter;
use ReflectionException;

trait Router
{
	use RuntimeFeature;

	public static string $classRouter		= SingleRouter::class;

	/**	@var	AbstractRouter			$router		Router Object */
	protected AbstractRouter $router;

	/**
	 *	Returns Router Object.
	 *	@access		public
	 *	@return		AbstractRouter
	 */
	public function getRouter(): AbstractRouter
	{
		return $this->router;
	}

	/**
	 *	@param		string|NULL		$routerClass
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function initRouter( string $routerClass = NULL ): static
	{
		$classRouter	= $routerClass ?: self::$classRouter;
		/** @var AbstractRouter $router */
		$router			= ObjectFactory::createObject( $classRouter, array( $this ) );
		$this->router	= $router;
		$this->runtime->reach( 'env: router' );
		return $this;
	}
}
