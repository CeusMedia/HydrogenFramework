<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Generic Action Dispatcher Class of Framework Hydrogen
 *
 *	Copyright (c) 2007-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Dispatcher;

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Obj\MethodFactory as MethodFactory;
use CeusMedia\Common\Alg\Text\CamelCase;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\OutputBuffer;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Controller\Abstraction as ControllerAbstraction;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

use ReflectionException;
use RuntimeException;
use ReflectionMethod;

use function ucfirst;

/**
 *	Generic Main Class of Framework Hydrogen
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Dispatcher
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class General
{
	public static string $prefixController	= "Controller_";

	/** @var array|string[] $skipWordsOnUppercase	List of path parts to skip on uppercase */
	public static array $skipWordsOnUppercase		= [
		'add', 'edit', 'index', 'remove',
		'admin', 'manage', 'work', 'shop',
		'info', 'file',
	];

	/** @var positive-int $uppercaseMaxLength		Max length of path part to apply uppercase */
	public static int $uppercaseMaxLength			= 4;

	/** @var non-empty-string $pathDivider */
	public static string $pathDivider				= '/';

	/** @var non-empty-string $pathDivider */
	public static string $camelcaseDivider			= '_';

	/** @var int $maxPathPartLength */
	public static int $maxPathPartLength			= 0;


	public string $defaultController		= 'index';
	public string $defaultAction			= 'index';
	public array $defaultArguments			= [];
	public bool $checkClassActionArguments	= TRUE;


	protected WebEnvironment $env;
	protected HttpRequest $request;
	protected array $history						= [];

	/**
	 *	Generate a list of possible class names.
	 *	@param		string		$path		Requested path
	 *	@return		array<string>
	 */

	/**
	 *	@param		string		$path				Path
	 *	@param		bool		$allowUppercase		Flag: allow uppercase variants, default: yes
	 *	@param		bool		$allowLowercase		Flag: allow lowercase variants, default: yes
	 *	@param		bool		$allowCamelCase		Flag: use camel case instead of ucfirst, default: yes
	 *	@param		bool		$allowNumbers		Flag: allow integer path parts or skip otherwise, default: no
	 *	@return		array		List if possible class names
	 */
	public static function getClassNameVariationsByPath( string $path, bool $allowUppercase = TRUE, bool $allowLowercase = TRUE, bool $allowCamelCase = TRUE, bool $allowNumbers = FALSE ): array
	{
		$parts  = explode( self::$pathDivider, trim( $path ) );
		if( 1 === count( $parts ) )
			return self::getClassNameVariationsByPathLevel1( $path, $allowUppercase, $allowLowercase, $allowCamelCase, $allowNumbers );
		return self::getClassNameVariationsByPathLevelX( $parts, $allowUppercase, $allowLowercase, $allowCamelCase, $allowNumbers );
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
		$firstGuess				= $classNameVariations[0] ?? '';
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
	 *	@param		?OutputBuffer		$devBuffer
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function dispatch( ?OutputBuffer $devBuffer = NULL ): string
	{
		$runtime	= $this->env->getRuntime();
		$runtime->reach( 'GeneralDispatcher::dispatch' );
		do{
			$this->setDefaults();
			$this->checkForLoop();

			$controller	= trim( $this->request->get( '__controller' ) );
			$action		= trim( $this->request->get( '__action' ) );
			$arguments	= $this->request->get( '__arguments', [] );

			$controllerInstanceOrFirstGuess	= static::getPrefixedClassInstanceByPathOrFirstClassNameGuess(
				$this->env,
				self::$prefixController,
				$controller
			);

			if( is_object( $controllerInstanceOrFirstGuess ) ){
				/** @var string $controller */
				$controller	= preg_replace( '/^Controller_/', '', $controllerInstanceOrFirstGuess::class );
				$controller	= str_replace( '_', '/', strtolower( $controller ) );
			}
			$runtime->reach( 'GeneralDispatcher::dispatch: check controller access' );
			$this->checkAccess( $controller, $action);

			$runtime->reach( 'GeneralDispatcher::dispatch: load controller instance' );
			if( !is_object( $controllerInstanceOrFirstGuess ) ){
				$message	= 'Invalid Controller "'.$controllerInstanceOrFirstGuess.'"';
				throw new RuntimeException( $message, 201 );											// break with internal error
			}
			$instance	= $controllerInstanceOrFirstGuess;
			if( !$instance instanceof Controller\Web &&
				!$instance instanceof Controller\Api &&
				!$instance instanceof Controller\Ajax )
				throw new RuntimeException( sprintf(
					'Controller class "%s" is not a Hydrogen controller',
					$instance::class
				), 301 );

			$runtime->reach( 'GeneralDispatcher::dispatch: factorized controller' );
			$this->checkClassAction( $instance, $action );
			if( $this->checkClassActionArguments )
				$this->checkClassActionArguments( $instance, $action, $arguments );
			$runtime->reach( 'GeneralDispatcher::dispatch: check@'.$controller.'/'.$action );

			if( $devBuffer )
				$instance->setDevBuffer( $devBuffer );
			$factory	= new MethodFactory( $instance );											// create method factory on controller instance
			$factory->callMethod( $action, $arguments );											// call action method in controller class with arguments
			$this->noteLastCall( $instance );
		}
		while( $instance->redirect ?? FALSE );
		$runtime->reach( 'GeneralDispatcher::dispatch: done' );

		if( $instance instanceof Controller\Web ){
			$view	= $instance->renderView();
			$runtime->reach( 'GeneralDispatcher::dispatch: view' );
			return $view;
		}
		return '';
	}


	//  --  PROTECTED  --  //


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

	/**
	 *	@param		string		$path				Path
	 *	@param		bool		$allowUppercase		Flag: allow uppercase variants, default: yes
	 *	@param		bool		$allowLowercase		Flag: allow lowercase variants, default: yes
	 *	@param		bool		$allowCamelCase		Flag: use camel case instead of ucfirst, default: yes
	 *	@param		bool		$allowNumbers		Flag: allow integer path parts or skip otherwise, default: no
	 *	@return		array		List if possible class names
	 */
	protected static function getClassNameVariationsByPathLevel1( string $path, bool $allowUppercase = TRUE, bool $allowLowercase = TRUE, bool $allowCamelCase = TRUE, bool $allowNumbers = FALSE ): array
	{
		if( '' === trim( $path ) )
			return [];
		if( !$allowNumbers && preg_match( '/^\d+$/', $path ) )
			return [];
		if( 0 !== self::$maxPathPartLength && strlen( $path ) > self::$maxPathPartLength )
			return [];

		$list	= [];
		if( $allowCamelCase && str_contains( $path, self::$camelcaseDivider ) )
			$list[]	= CamelCase::toPascalCase( str_replace( self::$camelcaseDivider, ' ', $path ) );
		else
			$list[]	= ucfirst( $path );
		if( $allowUppercase )
			if( strlen( $path ) <= self::$uppercaseMaxLength )
				if( !in_array( $path, self::$skipWordsOnUppercase ) )
					$list[]	= strtoupper( $path );
		if( $allowLowercase )
			$list[]	= $path;
		return array_unique( $list );
	}

	/**
	 *	@param		array		$parts				Path parts
	 *	@param		bool		$allowUppercase		Flag: allow uppercase variants, default: yes
	 *	@param		bool		$allowLowercase		Flag: allow lowercase variants, default: yes
	 *	@param		bool		$allowCamelCase		Flag: use camel case instead of ucfirst, default: yes
	 *	@param		bool		$allowNumbers		Flag: allow integer path parts or skip otherwise, default: no
	 *	@return		array		List if possible class names
	 */
	protected static function getClassNameVariationsByPathLevelX( array $parts, bool $allowUppercase = TRUE, bool $allowLowercase = TRUE, bool $allowCamelCase = TRUE, bool $allowNumbers = FALSE ): array
	{
		$prefix = trim( array_shift( $parts ) );
		if( !$allowNumbers && preg_match( '/^\d+$/', $prefix ) )
			return [];

		$prefixVariations	= self::getClassNameVariationsByPathLevel1(
			$prefix,
			$allowUppercase,
			$allowLowercase,
			$allowCamelCase,
			$allowNumbers
		);
		$classVariations    = self::getClassNameVariationsByPath(
			join( self::$pathDivider, $parts ),
			$allowUppercase,
			$allowLowercase,
			$allowCamelCase,
			$allowNumbers
		);
		if( [] === $classVariations )
			return $prefixVariations;

		$list	= [];
		foreach( $prefixVariations as $prefixVariation )
			foreach( $classVariations as $v )
				$list[] = $prefixVariation.'_'.$v;

		return $list;
	}

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
	 *	@param		ControllerAbstraction		$instance
	 *	@return		void
	 */
	protected function noteLastCall( ControllerAbstraction $instance ): void
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
	protected function setDefaults(): void
	{
		if( '' === trim( $this->request->get( '__controller', '' ) ) )
			$this->request->set( '__controller', $this->defaultController );
		if( '' === trim( $this->request->get( '__action', '' ) ) )
			$this->request->set( '__action', $this->defaultAction );
		if( [] === $this->request->get( '__arguments', [] ) )
			$this->request->set( '__arguments', $this->defaultArguments );
	}
}
