<?php
/**
 *	Handler to call event for registered hooks.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2014 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.7
 *	@version		$Id$
 */
/**
 *	The Captain is giving orders, depending on the changes of the situation.
 *	On every reported or spotted change he is able to shout out some commands if suitable.
 *
 *	In technical terms:
 *	The Caption is the event handler on the ship.
 *	On every called event it will call hooks if attached to event.
 *
 *	A hook can be defined by a module configuration and relates to an hook resource and event.
 *	A hook can be called by its resource and event name.
 *	To apply a hook within a context, a context object can be given.
 *
 *	Example:
 *	
 *	The module Manage:Customer:Project defines this hook:
 *
 *		<hook type="resource" resource="CustomerManager" event="registerTabs">
 *			View_Manage_Customer_Project::___onRegisterTab
 *		</hook>
 *
 *	and implements this hook in its view class View_Manage_Customer_Project:
 *
 *		public static function ___onRegisterTab( $env, $context, $context, $data ){...}
 *
 *	The module Manage:Customer calls this event in the render method of its view class View_Manage_Customer:
 *
 *		$env->getModules()->callHook( "CustomerManager", "registerTabs", $view, $data );
 *
 *	So, the module Manage:Customer:Project is able to append its tab to the given view of module
 *  Manage:Customer, which is now able to include the projects tab while showing a customer.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2014 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.7
 *	@version		$Id$
 *	@todo			code documentation
 */
class CMF_Hydrogen_Environment_Resource_Captain {

	/**	@var		CMF_Hydrogen_Environment_Abstract	$env		Environment object */
	protected $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$resource		...
	 *	@param		string		$event			...
	 *	@param		object		$context		...
	 *	@param		array		$arguments		...
	 *	@return		integer						Number of called hooks for event
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			ig method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 */
	public function callHook( $resource, $event, $context, $arguments = array() ){
//		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::callHook: '.$event.'@'.$resource.' start' );
		$count		= 0;
		$modules	= $this->env->hasModules() ? $this->env->getModules()->getAll() : array();
		foreach( $modules as $module ){
			if( empty( $module->hooks[$resource][$event] ) )
				continue;
			$function	= $module->hooks[$resource][$event];
			$pattern	= "/^([a-z0-9_]+)::([a-z0-9_]+)$/i";
			if( preg_match( $pattern, $function ) ){
				$className	= preg_replace( $pattern, "\\1", $function );
				$methodName	= preg_replace( $pattern, "\\2", $function );
				$function	= array( $className, $methodName );
				if( !method_exists( $className, $methodName ) )
					throw new RuntimeException( 'Method '.$className.'::'.$methodName.' is not existing' );
			}
			else{
				$function	= create_function( '$env, $context, $module, $arguments = array()', $function );
			}
			try{
				$count++;
				ob_start();
				$args	= array( $this->env, &$context, $module, $arguments );
				call_user_func_array( $function, $args );
				$this->env->clock->profiler->tick( '<!--Resource_Module_Library_Local::call-->Hook: '.$event.'@'.$resource.': '.$module->id );
				$stdout	= ob_get_clean();
				if( strlen( $stdout ) )
					if( $this->env->has( 'messenger' ) )
						$this->env->getMessenger()->noteNotice( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' reported: '.$stdout );
					else
						throw new RuntimeException( $stdout );
			}
			catch( Exception $e ){
				if( $this->env->has( 'messenger' ) )
					$this->env->getMessenger()->noteFailure( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' failed: '.$e->getMessage() );
				else
					throw new RuntimeException( 'Hook '.$module->id.'::'.$resource.'@'.$event.' failed: '.$e->getMessage(), 0, $e );
			}
		}
//		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::callHook: '.$event.'@'.$resource.' done' );
		return $count;
	}
}
?>
