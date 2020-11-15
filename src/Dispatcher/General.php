<?php
/**
 *	Generic Action Dispatcher Class of Framework Hydrogen
 *
 *	Copyright (c) 2007-2020 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Dispatcher
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Generic Main Class of Framework Hydrogen
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Dispatcher
 *	@uses			RuntimeException
 *	@uses			ReflectionMethod
 *	@uses			Alg_Object_Factory
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class CMF_Hydrogen_Dispatcher_General
{
	protected $env;
	protected $request;

	public $defaultController			= 'index';

	public $defaultAction				= 'index';

	public $defaultArguments			= array();

	protected $history					= array();

	public $checkClassActionArguments	= TRUE;

	public static $prefixController		= "Controller_";

	public function __construct(CMF_Hydrogen_Environment $env ) {
		$this->env		= $env;
		$this->request	= $env->getRequest();
	}

	protected function checkClass( $className ){
		if( !class_exists( $className ) ){															// class is neither loaded nor loadable
			$message	= 'Invalid Controller "'.$className.'"';
			throw new RuntimeException( $message, 201 );											// break with internal error
		}
	}

	protected function checkClassAction( $className, $instance, $action ){
		$denied = array( '__construct', '__destruct', 'getView', 'getData' );
		if( !method_exists( $instance, $action ) || in_array( $action, $denied ) ){					// no action method in controller instance
			$message	= 'Invalid Action "'.ucfirst( $className ).'::'.$action.'"';
			throw new RuntimeException( $message, 211 );											// break with internal error
		}
	}

	protected function checkClassActionArguments( $className, $instance, $action, $arguments ){
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
			$message	= 'Not enough arguments for action "'.ucfirst( $className ).'::'.$action.'"';
			throw new RuntimeException( $message, 212 );											// break with internal error
		}
		if( count( $arguments ) > $numberArgsTotal ){
			$message	= 'Too much arguments for action "'.ucfirst( $className ).'::'.$action.'"';
			throw new RuntimeException( $message, 212 );											// break with internal error
		}

	}

	protected function checkForLoop(){
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
	}

	public function checkAccess( $controller, $action ){
		$right1	= $this->env->getAcl()->has( $controller, $action );
		$right2	= $this->env->getAcl()->has( $controller.'_'.$action );
//		$right2	= $this->env->getAcl()->has( $controller );
//		$this->env->getMessenger()->noteNotice( "R1: ".$right1." | R2: ".$right2." | Controller: ".$controller." | Action: ".$action );
		if( !( $right1 || $right2 ) ){
			$message	= 'Access to '.$controller.'/'.$action.' denied.';
			throw new RuntimeException( $message, 403 );											// break with internal error
		}
	}

	public function dispatch(){
		$this->env->clock->profiler->tick( 'Dispatcher_General::dispatch' );
		do{
			$this->realizeCall();
			$this->checkForLoop();

			$controller	= trim( $this->request->get( '__controller' ) );
			$action		= trim( $this->request->get( '__action' ) );
			$arguments	= $this->request->get( '__arguments' );

			$className	= self::getControllerClassFromPath( $controller );							// get controller class name from requested controller path
			$this->checkClass( $className );
			$this->env->clock->profiler->tick( 'Dispatcher_General::dispatch: check: controller' );
			$this->checkAccess( $controller, $action);
			$instance	= Alg_Object_Factory::createObject( $className, array( $this->env ) );		// build controller instance
			$this->env->clock->profiler->tick( 'Dispatcher_General::dispatch: factorized controller' );
			$this->checkClassAction( $className, $instance, $action );
			if( $this->checkClassActionArguments )
				$this->checkClassActionArguments( $className, $instance, $action, $arguments );
			$this->env->clock->profiler->tick( 'Dispatcher_General::dispatch: check@'.$controller.'/'.$action );
			Alg_Object_MethodFactory::callObjectMethod( $instance, $action, $arguments );			// call action method in controller class with arguments
			$this->noteLastCall( $instance );
		}
		while( $instance->redirect );
		$this->env->clock->profiler->tick( 'Dispatcher_General::dispatch: done' );
		$view	= $instance->renderView();
		$this->env->clock->profiler->tick( 'Dispatcher_General::dispatch: view' );
		return $view;
	}

	static protected function getControllerClassFromPath( $path ){
		$parts		= str_replace( '/', ' ', $path );												//  slice into parts
		$name		= str_replace( ' ', '_', ucwords( $parts ) );									//  glue together capitalized
		return self::$prefixController.$name;														//  return controller class name
	}

	protected function noteLastCall( CMF_Hydrogen_Controller $instance ){
		$session	= $this->env->getSession();
		if( !$session )
			return;
		if( $this->request->getMethod() != 'GET' )
			return;
		if( $instance->redirect )
			return;
		$session->set( 'lastController', $this->request->get( '__controller' ) );
		$session->set( 'lastAction', $this->request->get( '__action' ) );
	}

	protected function realizeCall(){
		if( !trim( $this->request->get( '__controller' ) ) )
			$this->request->set( '__controller', $this->defaultController );
		if( !trim( $this->request->get( '__action' ) ) )
			$this->request->set( '__action', $this->defaultAction );
		if( !$this->request->get( '__arguments' ) )
			$this->request->set( '__arguments', $this->defaultArguments );
	}
}
