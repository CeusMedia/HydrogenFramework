<?php
/**
 *	Generic Action Dispatcher Class of Framework Hydrogen
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
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
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Generic Main Class of Framework Hydrogen
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@uses			RuntimeException
 *	@uses			ReflectionMethod
 *	@uses			Alg_Object_Factory
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			Code Documentation
 */
class Framework_Hydrogen_Dispatcher
{
	public $defaultController			= 'index';
	public $defaultAction				= 'index';
	public $defaultArguments			= array();

	protected $history					= array();

	public $checkClassActionArguments	= TRUE;

	public $prefixController			= "Controller_";
//	public $prefixModel					= "Model_";
//	public $prefixView					= "View_";


	public function __construct( Framework_Hydrogen_Environment $env ) {
		$this->env	= $env;
	}

	protected function checkClass( $className )
	{
		if( !class_exists( $className ) )															// class is neither loaded nor loadable
		{
			$message	= 'Invalid Controller "'.ucfirst( $className ).'"';
			throw new RuntimeException( $message, 201 );											// break with internal error
		}
	}

	protected function checkClassAction( $className, $instance, $action )
	{
		if( !method_exists( $instance, $action ) )													// no action method in controller instance
		{
			$message	= 'Invalid Action "'.ucfirst( $className ).'::'.$action.'"';
			throw new RuntimeException( $message, 211 );											// break with internal error
		}
	}

	protected function checkClassActionArguments( $className, $instance )
	{
		$action		= $this->env->getRequest()->get( 'action' );
		$arguments	= $this->env->getRequest()->get( 'arguments' );
		$numberArgsAtLeast	= 0;
		$numberArgsTotal	= 0;
		$methodReflection	= new ReflectionMethod( $instance, $action );
		$methodArguments	= $methodReflection->getParameters();
		while( $methodArgument = array_shift( $methodArguments ) )
		{
			$numberArgsTotal++;
			if( !$methodArgument->isOptional() )
				$numberArgsAtLeast++;
		}
		if( count( $arguments ) < $numberArgsAtLeast )
		{
			$message	= 'No enough arguments for action "'.ucfirst( $className ).'::'.$action.'"';
			throw new RuntimeException( $message, 212 );											// break with internal error
		}
		if( count( $arguments ) > $numberArgsTotal )
		{
			$message	= 'Too much arguments for action "'.ucfirst( $className ).'::'.$action.'"';
			throw new RuntimeException( $message, 212 );											// break with internal error
		}

	}

	protected function checkForLoop()
	{
		$controller	= $this->env->getRequest()->get( 'controller' );
		$action		= $this->env->getRequest()->get( 'action' );
		if( empty( $this->history[$controller][$action] ) )
			$this->history[$controller][$action]	= 0;
		if( $this->history[$controller][$action] > 2 )
		{
			throw new RuntimeException( 'Too many redirects' );
#			$this->messenger->noteFailure( 'Too many redirects.' );
#			break;
		}
		$this->history[$controller][$action]++;

	}

	public function dispatch()
	{
		do
		{
			$this->realizeCall();
			$this->checkForLoop();

			$request	= $this->env->getRequest();
			$controller	= $request->get( 'controller' );
			$action		= $request->get( 'action' );
			$arguments	= $request->get( 'arguments' );

			$className	= $this->prefixController.ucfirst( $controller );							// get controller class name
			$this->checkClass( $className );
			$factory	= new Alg_Object_Factory();													// raise object factory
			$instance	= $factory->createObject( $className, array( &$this->env ) );				// build controller instance
			$this->checkClassAction( $className, $instance, $action );
			if( $this->checkClassActionArguments )
				$this->checkClassActionArguments( $className, $instance, $action );

			Alg_Object_MethodFactory::callObjectMethod( $instance, $action, $arguments );			// call action method in controller class with arguments

			$this->noteLastCall( $instance );
		}
		while( $instance->redirect );
		return $instance->getView();
	}

	protected function noteLastCall( $instance )
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( !$session )
			return;
		if( $request->getMethod() != 'GET' )
			return;
		if( $instance->redirect )
			return;
		$session->set( 'lastController', $request->get( 'controller' ) );
		$session->set( 'lastAction', $request->get( 'action' ) );
	}

	protected function realizeCall()
	{
		$request	= $this->env->getRequest();
		if( !$request->get( 'controller' ) )
			$request->set( 'controller', $this->defaultController );
		if( !$request->get( 'action' ) )
			$request->set( 'action', $this->defaultAction );
		if( !$request->get( 'arguments' ) )
			$request->set( 'arguments', $this->defaultArguments );
	}
}
?>
