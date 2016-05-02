<?php
/**
 *	Handler to call event for registered hooks.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			code documentation
 */
class CMF_Hydrogen_Environment_Resource_Captain {

	/**	@var		CMF_Hydrogen_Environment_Abstract	$env		Environment object */
	protected $env;

	/**	@var		array								$disabledHooks	List of disabled hooks */
	protected $disabledHooks	= array();

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
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object		$context		Context object, will be available inside hook as $context
	 *	@param		array		$payload		Map of hook payload data, will be available inside hook as $payload and $data
	 *	@return		integer						Number of called hooks for event
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			ig method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 *	@todo 		rename $data to $payload
	 */
	public function callHook( $resource, $event, $context, $payload = array() ){
		if( !$this->env->hasModules() )
			return NULL;
		if( array_key_exists( $resource."::".$event, $this->disabledHooks ) )
			return FALSE;
//		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::callHook: '.$event.'@'.$resource.' start' );
		$count			= 0;
		$result			= NULL;
		$regexMethod	= "/^([a-z0-9_]+)::([a-z0-9_]+)$/i";
		foreach( $this->env->getModules()->getAll() as $module ){
			if( empty( $module->hooks[$resource][$event] ) )
				continue;
			if( !is_array( $module->hooks[$resource][$event] ) )
				$module->hooks[$resource][$event]	= array( $module->hooks[$resource][$event] );
			foreach( $module->hooks[$resource][$event] as $nr => $function ){
				if( preg_match( $regexMethod, $function ) ){
					$function	= preg_split( "/::/", $function );
					if( !method_exists( $function[0], $function[1] ) )
						throw new RuntimeException( 'Method '.$function[0].'::'.$function[1].' is not existing' );
/*	@deprecated		replaced by the 3 lines above
	@todo 			remove this old version of the 3 lines above after testing
					$className	= preg_replace( $regexMethod, "\\1", $function );
					$methodName	= preg_replace( $regexMethod, "\\2", $function );
					$function	= array( $className, $methodName );
					if( !method_exists( $className, $methodName ) )
						throw new RuntimeException( 'Method '.$className.'::'.$methodName.' is not existing' );
*/				}
				else{
					$function	= '$data = $payload;'.PHP_EOL.$function;
					$function	= create_function( '$env, $context, $module, $payload = array()', $function );
				}
				try{
					$count++;
					ob_start();
					$args	= array( $this->env, &$context, $module, $payload );
					$result	= call_user_func_array( $function, $args );
					$this->env->clock->profiler->tick( '<!--Resource_Module_Library_Local::call-->Hook: '.$event.'@'.$resource.': '.$module->id );
					$stdout	= ob_get_clean();
					if( strlen( $stdout ) )
						if( $this->env->has( 'messenger' ) )
							$this->env->getMessenger()->noteNotice( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' reported: '.$stdout );
						else
							throw new RuntimeException( $stdout );
				}
				catch( Exception $e ){
					if( $this->env->has( 'messenger' ) ){
						$this->env->getMessenger()->noteFailure( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' failed: '.$e->getMessage() );
						$this->env->getLog()->logException( $e );
					}
					else
						throw new RuntimeException( 'Hook '.$module->id.'::'.$resource.'@'.$event.' failed: '.$e->getMessage(), 0, $e );
				}
			}
			if( (bool) $result )
				return $result;
		}
//		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::callHook: '.$event.'@'.$resource.' done' );
		return $result;
	}

	/**
	 *	Removed hook from disable list.
	 *	@access		public
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@return 	boolean
	 */
	public function enableHook( $resource, $event ){
		$key	= $resource."::".$event;
		if( !array_key_exists( $key, $this->disabledHooks ) )
			return FALSE;
		unset( $this->disabledHooks[$key] );
		return TRUE;
	}

	/**
	 *	Adds hook from disable list.
	 *	@access		public
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@return 	boolean
	 */
	public function disableHook( $resource, $event ){
		$key	= $resource."::".$event;
		if( array_key_exists( $key, $this->disabledHooks ) )
			return FALSE;
		$this->disabledHooks[$key]	= TRUE;
		return TRUE;
	}
}
?>
