<?php
/**
 *	Abstract Basic Action Handler.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@extends		Framework_Krypton_Core_Component
 *	@uses			Framework_Krypton_Core_Registry
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			21.02.2007
 *	@version		0.6
 */
import( 'de.ceus-media.framework.krypton.core.Component' );
import( 'de.ceus-media.framework.krypton.core.Registry' );
/**
 *	Abstract Basic Action Handler.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@abstract
 *	@extends		Framework_Krypton_Core_Component
 *	@uses			Framework_Krypton_Core_Registry
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			21.02.2007
 *	@version		0.6
 */
abstract class Framework_Krypton_Core_Action extends Framework_Krypton_Core_Component
{
	/**	@var	array			$registeredActions		Map of Action Keys and Methods */
	protected $registeredActions	= array();

	/**
	 *	Method for manually called Actions in inheriting Action Classes.
	 *	@access		public
	 *	@return		void
	 *	@deprecated	use performAfterRegisteredActions() instead
	 *	@deprecated	to be removed in 0.6.7
	 */
	public function act()
	{
		$this->performBeforeRegisteredActions();	
	}
	
	/**
	 *	Adds an Action by an event name and a method name.
	 *	@access		protected
	 *	@param		string		$event			Event name of Action
	 *	@param		string		$action			Method of Action if different from Event name
	 *	@return		void
	 *	@deprecated use registerAction since 0.6.6
	 *	@deprecated to be removed in 0.6.7
	 */
	protected function addAction( $event, $action = NULL )
	{
		return $this->registerAction( $event, $action );
	}

	/**
	 *	Indicates whether an Action is registered by an Event.
	 *	@access		public
	 *	@param		string		$event			Event name of Action
	 *	@return		bool
	 *	@deprecated use isRegisteredAction since 0.6.6
	 *	@deprecated to be removed in 0.6.7
	 */
	public function hasAction( $actionKey )
	{
		return isset( $this->registeredActions[$actionKey]);
	}

	/**
	 *	Indicates whether an Action is registered by an Event.
	 *	@access		public
	 *	@param		string		$actionKey		Key of Action
	 *	@return		bool
	 */
	public function isRegisteredAction( $actionKey )
	{
		return isset( $this->registeredActions[$actionKey] );
	}

	/**
	 *	Calls Actions by checking calls in Request. Also collects and returns results of all Action Calls as Array.
	 *	@access		public
	 *	@return		array
	 *	@deprecated	use performRegisteredActions()
	 *	@deprecated	to be removed in 0.6.7
	 */
	public function performActions()
	{
		return $this->performRegisteredActions();
	}

	/**
	 *	This Method can be overwritten to handle stuff after the registered Actions where called.
	 *	@access		public
	 *	@return		void
	 */
	public function performAfterRegisteredActions()
	{
	}
	
	/**
	 *	This Method can be overwritten to handle stuff before the registered Actions will be called.
	 *	@access		public
	 *	@return		void
	 */
	public function performBeforeRegisteredActions()
	{
	}
	
	/**
	 *	Calls Actions by checking calls in Request. Also collects and returns results of all Action Calls as Array.
	 *	@access		public
	 *	@return		array
	 */
	public function performRegisteredActions()
	{
		$results	= array();
		foreach( $this->registeredActions as $actionKey => $actionMethod )
		{
			if( !$this->request->has( $actionKey ) )
				continue;
			if( !method_exists( $this, $actionMethod ) )
				throw new BadMethodCallException( 'Action "'.get_class( $this ).'::'.$actionMethod.'()" is not existing.' );
			$results[$actionKey]	= $this->$actionMethod( $this->request->get( $actionKey ) );
		}
		return $results;
	}

	/**
	 *	Adds an Action by an event name and a method name to the internal Action Register, excuted by performActions().
	 *	@access		protected
	 *	@param		string		$actionKey			Key of Action
	 *	@param		string		$actionMethod		Method of Action  if different from Key
	 *	@return		void
	 */
	protected function registerAction( $actionKey, $actionMethod = NULL )
	{
		$actionMethod	= $actionMethod ? $actionMethod	: $actionKey;
		$this->registeredActions[$actionKey]	= $actionMethod;
	}

	/**
	 *	Removes a registered Action.
	 *	@access		public
	 *	@param		string		$actionKey			Key of Action
	 *	@return		void
	 */
	public function unregisterAction( $actionKey )
	{
		if( $this->isRegisteredAction( $actionKey ) )
			unset( $this->registeredActions[$actionKey] );
	}

	/**
	 *	Restart application with a Request URL.
	 *	@access		protected
	 *	@param		string		$request			Request URL with Query String
	 *	@return		void
	 *	@deprecated	use redirect instead
	 *	@deprecated	to be removed in 0.6.7
	 */
	protected function restart( $request )
	{
		$session	= Framework_Krypton_Core_Registry::getStatic( 'session' );
		$session->__destruct();
		header( "Location: ./".$request );
		exit;
	}

	/**
	 *	Restart application with a Request URL.
	 *	@access		protected
	 *	@param		string		$request			Request URL with Query String
	 *	@param		string		$message			Message to display
	 *	@param		int			$messageType		Message Type (0-Failure|1-Error|2-Notice|3-Success)
	 *	@param		string		$arg1				Argument to be set into Message
	 *	@param		string		$arg2				Argument to be set into Message
	 *	@return		void
	 */
	protected function redirect( $url, $message = NULL, $messageType = 2, $arg1 = NULL, $arg2 = NULL )
	{
		if( $message )
			$this->messenger->note( $messageType, $message, $arg1, $arg2 );

		$session	= Framework_Krypton_Core_Registry::getStatic( 'session' );
		$session->__destruct();
		header( "Location: ./".$url );
		exit;
	}
}
?>