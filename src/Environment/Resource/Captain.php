<?php
/**
 *	Handler to call event for registered hooks.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\HydrogenFramework\Environment;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use RangeException;

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
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			code documentation
 */
class Captain
{
	const LEVEL_UNKNOWN		= 0;
	const LEVEL_TOP			= 1;
	const LEVEL_START		= 1;
	const LEVEL_HIGHEST		= 2;
	const LEVEL_HIGH		= 3;
	const LEVEL_HIGHER		= 4;
	const LEVEL_MID			= 5;
	const LEVEL_LOWER		= 6;
	const LEVEL_LOW			= 7;
	const LEVEL_LOWEST		= 8;
	const LEVEL_BOTTOM		= 9;
	const LEVEL_END			= 9;

	/**	@var		Environment			$env			Environment object */
	protected $env;

	/**	@var		array								$disabledHooks	List of disabled hooks */
	protected $disabledHooks	= [];

	/**	@var		boolean								$logCalls		Flag: log hook calls */
	protected $logCalls			= FALSE;

	/**	@var		array								$openHooks		List of hooks open right now */
	protected $openHooks		= [];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment			$env			Environment object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object		$context		Context object, will be available inside hook as $context
	 *	@param		array|NULL	$payload		Map of hook payload data, will be available inside hook as $payload and $data
	 *	@return		bool|NULL					TRUE if hook is chain-breaking, FALSE if hook is disabled or non-chain-breaking, NULL if no modules installed or no hooks defined
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			ig method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 *	@todo 		rename $data to $payload
	 */
	public function callHook( string $resource, string $event, object $context, ?array & $payload = [] ): ?bool
	{
		if( !$this->env->hasModules() )
			return NULL;
		if( array_key_exists( $resource."::".$event, $this->disabledHooks ) )
			return FALSE;
		if( array_key_exists( $resource.'::'.$event, $this->openHooks ) )
			return FALSE;

		$this->openHooks[$resource.'::'.$event]	= microtime( TRUE );

		if( $this->logCalls )
			error_log( microtime( TRUE ).' '.$resource.'>'.$event."\n", 3, 'logs/hook_calls.log' );

		$hooks	= [];
		for( $i=0; $i<10; $i++)
			$hooks[$i]	= [];

		$count			= 0;
		$result			= NULL;
		$regexMethod	= "/^([a-z0-9_]+)::([a-z0-9_]+)$/i";
		foreach( $this->env->getModules()->getAll() as $module ){
			if( empty( $module->hooks[$resource][$event] ) )
				continue;
			if( !is_array( $module->hooks[$resource][$event] ) )
				$module->hooks[$resource][$event]	= array( $module->hooks[$resource][$event] );

			foreach( $module->hooks[$resource][$event] as $hook ){
				$hooks[$hook->level][]	= (object) array(
					'module'	=> $module,
					'event'		=> $event,
					'resource'	=> $resource,
					'function'	=> $hook->hook,
				);
			}
		}
		foreach( $hooks as $level => $levelHooks ){
			foreach( $levelHooks as $hook ){
				if( 0 === strlen( $hook->function ) )
					continue;
				$module		= $hook->module;
				$resource	= $hook->resource;
				$event		= $hook->event;
				$function	= $hook->function;
				try{
					$callback	= explode("::", $function);
					if( !preg_match( $regexMethod, $function ) )
						throw new RuntimeException( 'Format of hook function is invalid or outdated' );
					if( !class_exists( $callback[0] ) )
						throw new RuntimeException( 'Hook handling class '.$callback[0].' is not existing' );
					if( !method_exists( $callback[0], $callback[1] ) )
						throw new RuntimeException( 'Hook handling function '.$function.' is not existing' );
					if( !is_callable( $callback[0], $callback[1] ) )
						throw new RuntimeException( 'Hook handling function '.$function.' is not callable' );

					$count++;
					ob_start();
					$args	= array( $this->env, &$context, $module, $payload );
					$result	= call_user_func_array( $callback, $args );
					$this->env->getRuntime()->reach( '<!--Resource_Module_Library_Local::call-->Hook: '.$event.'@'.$resource.': '.$module->id );
					$stdout	= ob_get_clean();
					if( strlen( trim( $stdout ) ) )
						if( $this->env->has( 'messenger' ) )
							$this->env->get( 'messenger' )->noteNotice( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' reported: '.$stdout );
						else
							throw new RuntimeException( $stdout );
				}
				catch( Exception $e ){
					if( $this->env->has( 'messenger' ) ){
						$this->env->get( 'messenger' )->noteFailure( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' failed: '.$e->getMessage() );
						$this->env->getLog()->logException( $e );
					}
					else
						throw new RuntimeException( 'Hook '.$module->id.'::'.$resource.'@'.$event.' failed: '.$e->getMessage(), 0, $e );
				}
				finally{
					unset( $this->openHooks[$resource.'::'.$event]);
				}
			}
			if( TRUE === $result )
				return TRUE;
		}
		return $result;
	}

	/**
	 *	Removed hook from disable list.
	 *	@access		public
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@return 	boolean
	 */
	public function enableHook( string $resource, string $event ): bool
	{
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
	public function disableHook( string $resource, string $event ): bool
	{
		$key	= $resource."::".$event;
		if( array_key_exists( $key, $this->disabledHooks ) )
			return FALSE;
		$this->disabledHooks[$key]	= TRUE;
		return TRUE;
	}

	/**
	 *	Try to understand given load level.
	 *	Matches given value into a scale between 0 and 9.
	 *	Contains fallback for older module versions using level as string (top,mid,end) or boolean.
	 *	Understands:
	 *	- integer (limited to [0-9])
	 *	- NULL or empty string as level 4 (mid).
	 *	- boolean TRUE as level 1 (top).
	 *	- boolean FALSE as level 4 (mid).
	 *	- string {top,head,start} as level 1.
	 *	- string {mid,center,normal,default} as level 4.
	 *	- string {end,tail,bottom} as level 8.
	 *	@static
	 *	@access		public
	 *	@param		mixed			$level 			Load level: 0-9 or {top(1),mid(4),end(8)} or {TRUE(1),FALSE(4)} or NULL(4)
	 *	@return		integer			Level as integer value between 0 and 9
	 *	@throws		InvalidArgumentException		if level is not if type NULL, boolean, integer or string
	 *	@throws		RangeException					if given string is not within {top,head,start,mid,center,normal,default,end,tail,bottom}
	 */
	static public function interpretLoadLevel( $level ): int
	{
		if( is_null( $level ) || !strlen( trim( $level ) ) )
			return 4;
		if( is_int( $level ) )
			return min( max( abs( $level ), 0 ), 9 );
		if( is_bool( $level ) )
			return $level ? 1 : 4;
		if( is_string( $level ) && preg_match( '/^[0-9]$/', trim( $level ) ) )
			return (int) $level;
		if( !is_string( $level ) )
			throw new InvalidArgumentException( 'Load level must be integer or string' );
		if( in_array( $level, array( 'top', 'head', 'start' ) ) )
			return 1;
		if( in_array( $level, array( 'mid', 'center', 'normal', 'default' ) ) )
			return 4;
		if( in_array( $level, array( 'end', 'tail', 'bottom' ) ) )
			return 8;
		throw new RangeException( 'Invalid load level: '.$level );
	}


	/**
	 *	Set activity of logging of hook calls.
	 *	@access		public
	 *	@param		boolean		$log		Flag: Activate logging of hook calls (disabled by default)
	 *	@return		self
	 */
	public function setLogCalls( bool $log = TRUE ): self
	{
		$this->logCalls	= $log;
		return $this;
	}
}
