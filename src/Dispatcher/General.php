<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Generic Action Dispatcher Class of Framework Hydrogen
 *
 *	Copyright (c) 2007-2024 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Dispatcher
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Dispatcher;

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Obj\MethodFactory as MethodFactory;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Controller;

use ReflectionException;
use RuntimeException;
use ReflectionMethod;

use function ucfirst;

/**
 *	Generic Main Class of Framework Hydrogen
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Dispatcher
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class General
{
	public static string $prefixController	= "Controller_";

	public string $defaultController		= 'index';

	public string $defaultAction			= 'index';

	public array $defaultArguments			= [];

	public bool $checkClassActionArguments	= TRUE;


	protected WebEnvironment $env;

	protected HttpRequest $request;

	protected array $history				= [];

	/**
	 *	Generate a list of possible class names.
	 *	@param		string		$path		Requested path
	 *	@return		array<string>
	 */

	public static function getClassNameVariationsByPath( string $path ): array
	{
		$list   = [];
		$parts  = explode( '/', $path );
		if( 1 === count( $parts ) )
			return [ucfirst( $path ), strtoupper( $path ), $path];

		$prefix = array_shift( $parts );
		$vs     = self::getClassNameVariationsByPath( join( '/', $parts ) );
		foreach( $vs as $v )
			$list[] = ucfirst( $prefix ).'_'.$v;
		foreach( $vs as $v )
			$list[] = strtoupper( $prefix ).'_'.$v;
		foreach( $vs as $v )
			$list[] = $prefix.'_'.$v;
		return $list;
	}

	/**
	 *	@param		Environment		$env
	 *	@param		string			$prefix
	 *	@param		string			$path
	 *	@return		object|string
	 *	@throws		ReflectionException
	 */
	public static function getPrefixedClassInstanceByPathOrFirstClassNameGuess( Environment $env, string $prefix, string $path ): object|string
	{
		$classNameVariations    = static::getClassNameVariationsByPath( $path );
		$firstGuess				= $classNameVariations[0];
		foreach( $classNameVariations as $className ){
			$instance	= static::getClassInstanceIfAvailable( $env, $prefix.$className );
			if( NULL !== $instance )
				return $instance;
		}
		return $firstGuess;
	}

	public function __construct( WebEnvironment $env )
	{
		$this->env		= $env;
		$this->request	= $env->getRequest();
	}

	/**
	 *	Checks ACL rights to controller action, given by URL.
	 *	@access		public
	 *	@param		string		$controller		Controller, part of request URL
	 *	@param		string		$action			Controller action, part of request URL
	 *	@return		bool
	 *	@throws		RuntimeException			if not rights set
	 */
	public function checkAccess( string $controller, string $action ): bool
	{
		$right1	= $this->env->getAcl()->has( $controller, $action );
		$right2	= $this->env->getAcl()->has( $controller.'_'.$action );
//		$right2	= $this->env->getAcl()->has( $controller );
//		$this->env->getMessenger()->noteNotice( "R1: ".$right1." | R2: ".$right2." | Controller: ".$controller." | Action: ".$action );
		if( !( $right1 || $right2 ) ){
			$message	= 'Access to '.$controller.'/'.$action.' denied.';
			throw new RuntimeException( $message, 403 );											// break with internal error
		}
		return TRUE;
	}

	/**
	 *	Tries to create controller instance and call controller action, given by request URL.
	 *	Returns rendering result of view action.
	 *	@access		public
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function dispatch(): string
	{
		$runtime	= $this->env->getRuntime();
		$runtime->reach( 'Dispatcher_General::dispatch' );
		do{
			$this->realizeCall();
			$this->checkForLoop();

			$controller	= trim( $this->request->get( '__controller' ) );
			$action		= trim( $this->request->get( '__action' ) );
			$arguments	= $this->request->get( '__arguments' );

			$controllerInstanceOrFirstGuess	= static::getPrefixedClassInstanceByPathOrFirstClassNameGuess(
				$this->env,
				self::$prefixController,
				$controller
			);
			$runtime->reach( 'Dispatcher_General::dispatch: check controller access' );
			$this->checkAccess( $controller, $action);

			$runtime->reach( 'Dispatcher_General::dispatch: load controller instance' );
			if( !is_object( $controllerInstanceOrFirstGuess ) ){
				$message	= 'Invalid Controller "'.$controllerInstanceOrFirstGuess.'"';
				throw new RuntimeException( $message, 201 );											// break with internal error
			}
			$instance	= $controllerInstanceOrFirstGuess;
			if( !$instance instanceof Controller )
				throw new RuntimeException(
					sprintf(
						'Controller class "%s" is not a Hydrogen controller',
						$instance::class
					), 301 );
			$runtime->reach( 'Dispatcher_General::dispatch: factorized controller' );

			$this->checkClassAction( $instance, $action );
			if( $this->checkClassActionArguments )
				$this->checkClassActionArguments( $instance, $action, $arguments );
			$runtime->reach( 'Dispatcher_General::dispatch: check@'.$controller.'/'.$action );

			$factory	= new MethodFactory( $instance );											// create method factory on controller instance
			$factory->callMethod( $action, $arguments );											// call action method in controller class with arguments
			$this->noteLastCall( $instance );
		}
		while( $instance->redirect );
		$runtime->reach( 'Dispatcher_General::dispatch: done' );
		$view	= $instance->renderView();
		$runtime->reach( 'Dispatcher_General::dispatch: view' );
		return $view;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		object		$instance
	 *	@param		string		$action
	 *	@return		bool
	 */
	protected function checkClassAction( object $instance, string $action ): bool
	{
		$denied = array( '__construct', '__destruct', 'getView', 'getData' );
		if( !method_exists( $instance, $action ) || in_array( $action, $denied ) ){					// no action method in controller instance
			$message	= 'Invalid Action "'.$instance::class.'::'.$action.'"';
			throw new RuntimeException( $message, 211 );											// break with internal error
		}
		return TRUE;
	}

	/**
	 *	@param		object		$instance
	 *	@param		string		$action
	 *	@param		array		$arguments
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	protected function checkClassActionArguments( object $instance, string $action, array $arguments = [] ): bool
	{
		$numberArgsAtLeast	= 0;
		$numberArgsTotal	= 0;
		$methodReflection	= new ReflectionMethod( $instance, $action );
		$methodArguments	= $methodReflection->getParameters();

		while( $methodArgument = array_shift( $methodArguments ) ){
			$numberArgsTotal++;
			if( !$methodArgument->isOptional() )
				$numberArgsAtLeast++;
		}
		if( count( $arguments ) < $numberArgsAtLeast ){
			$message	= 'Not enough arguments for action "'.$instance::class.'::'.$action.'"';
			throw new RuntimeException( $message, 212 );											// break with internal error
		}
		if( count( $arguments ) > $numberArgsTotal ){
			$message	= 'Too much arguments for action "'.$instance::class.'::'.$action.'"';
			throw new RuntimeException( $message, 212 );											// break with internal error
		}
		return TRUE;
	}

	protected function checkForLoop(): bool
	{
		$controller	= $this->request->get( '__controller' );
		$action		= $this->request->get( '__action' );
		if( empty( $this->history[$controller][$action] ) )
			$this->history[$controller][$action]	= 0;
		if( $this->history[$controller][$action] > 2 ){
			throw new RuntimeException( 'Too many redirects' );
#			$this->messenger->noteFailure( 'Too many redirects.' );
#			break;
		}
		$this->history[$controller][$action]++;
		return TRUE;
	}

	/**
	 *	@param		Controller		$instance
	 *	@return		void
	 */
	protected function noteLastCall( Controller $instance ): void
	{
		$session	= $this->env->getSession();
		if( $this->request->getMethod() != 'GET' )
			return;
		if( $instance->redirect )
			return;
		$session->set( 'lastController', $this->request->get( '__controller' ) );
		$session->set( 'lastAction', $this->request->get( '__action' ) );
	}

	/**
	 *	@return		void
	 */
	protected function realizeCall(): void
	{
		if( '' === trim( $this->request->get( '__controller', '' ) ) )
			$this->request->set( '__controller', $this->defaultController );
		if( '' === trim( $this->request->get( '__action', '' ) ) )
			$this->request->set( '__action', $this->defaultAction );
		if( [] === $this->request->get( '__arguments', [] ) )
			$this->request->set( '__arguments', $this->defaultArguments );
	}

	//  --  NEW DISPATCH STRATEGY  --  //

	/**
	 *	@param		Environment		$env
	 *	@param		string			$className
	 *	@return		?object
	 *	@throws		ReflectionException
	 */
	protected static function getClassInstanceIfAvailable( Environment $env, string $className ): ?object
	{
		if( !class_exists( $className ) )
			return NULL;
		return ObjectFactory::createObject( $className, [$env] );
	}
}
