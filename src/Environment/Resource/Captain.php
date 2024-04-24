<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Handler to call event for registered hooks.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\Alg\Obj\Factory;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Hook;
use DomainException;
use Exception;
use ReflectionException;
use RuntimeException;

/**
 *	The Captain is giving orders, depending on the changes of the situation.
 *	On every reported or spotted change he is able to shout out some commands if suitable.
 *
 *	In technical terms:
 *	The Caption is the event handler on the ship.
 *	On every called event it will call hooks if attached to event.
 *
 *	A hook can be defined by a module configuration and relates to a hook resource and event.
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
 *		public static function ___onRegisterTab( Environment $env, object $context, $context, array % $payload ){...}
 *
 *	The module Manage:Customer calls this event in the render method of its view class View_Manage_Customer:
 *
 *		$env->getModules()->callHook( "CustomerManager", "registerTabs", $view, $payload );
 *
 *	So, the module Manage:Customer:Project is able to append its tab to the given view of module
 *  Manage:Customer, which is now able to include the projects tab while showing a customer.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			code documentation
 */
class Captain
{
	public const LEVEL_UNKNOWN		= 0;
	public const LEVEL_TOP			= 1;
	public const LEVEL_START		= 1;
	public const LEVEL_HIGHEST		= 2;
	public const LEVEL_HIGH			= 3;
	public const LEVEL_HIGHER		= 4;
	public const LEVEL_MID			= 5;
	public const LEVEL_LOWER		= 6;
	public const LEVEL_LOW			= 7;
	public const LEVEL_LOWEST		= 8;
	public const LEVEL_BOTTOM		= 9;
	public const LEVEL_END			= 9;

	/**	@var		Environment			$env			Environment object */
	protected Environment $env;

	/**	@var		array								$disabledHooks	List of disabled hooks */
	protected array $disabledHooks		= [];

	/**	@var		boolean								$logCalls		Flag: log hook calls */
	protected bool $logCalls			= FALSE;

	/**	@var		array								$openHooks		List of hooks open right now */
	protected array $openHooks			= [];

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
	 *	@param		array		$payload		Map of hook payload data, will be available inside hook as $payload
	 *	@return		bool|NULL					TRUE if hook is chain-breaking, FALSE if hook is disabled or non-chain-breaking, NULL if no modules installed or no hooks defined
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			if method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 */
	public function callHook( string $resource, string $event, object $context, array & $payload = [] ): ?bool
	{
		if( !$this->env->hasModules() )
			return NULL;
		if( array_key_exists( $resource."::".$event, $this->disabledHooks ) )					//  skip disabled hook 
			return FALSE;
		if( array_key_exists( $resource.'::'.$event, $this->openHooks ) )						//  avoid recursion
			return FALSE;

		$this->openHooks[$resource.'::'.$event]	= microtime( TRUE );
		if( $this->logCalls )
			error_log( microtime( TRUE ).' '.$resource.'>'.$event."\n", 3, 'logs/hook_calls.log' );

		$hooks	= $this->collectHooks( $resource, $event );
		return $this->fetchCollectedResourceEventHooks( $hooks, $context, $payload );
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

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$resource
	 *	@param		string		$event
	 *	@return		array<int,array<int,object{resource: string, event: string, moduleId: string, function: string}>>
	 */
	public function collectHooks( string $resource, string $event ): array
	{
		$hooks = array_fill( 0, 9, [] );											//  prepare hook list with levels (0-9)
		/** @var ModuleDefinition $module */
		foreach( $this->env->getModules()->getAll() as $module ){
			if( !isset( $module->hooks[$resource][$event] ) )
				continue;

			//  @todo is this still needed? does: convert hook list of this module to array, if string
			if( !is_array( $module->hooks[$resource][$event] ) )
				$module->hooks[$resource][$event] = [$module->hooks[$resource][$event]];

			foreach( $module->hooks[$resource][$event] as $hook ){
				$hooks[$hook->level][] = (object) [
					'moduleId'	=> $module->id,
					'event'		=> $event,
					'resource'	=> $resource,
					'function'	=> $hook->callback,
				];
			}
		}
		return array_filter( array_map( static function( $levelHooks ){
			return 0 !== count( $levelHooks ) ? $levelHooks : NULL;
		}, $hooks ) );
	}

	/**
	 *	@param		array<int,array<int,object{resource: string, event: string, moduleId: string, function: string}>>		$hooks
	 *	@param		object		$context
	 *	@param		array		$payload
	 *	@return		bool
	 *	@throws		RuntimeException		if given static class method is not existing
	 *	@throws		RuntimeException		if method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException		if method call is throwing an exception
	 *	@throws		ReflectionException
	 */
	protected function fetchCollectedResourceEventHooks( array $hooks, object $context, array & $payload): bool
	{
 		$result = NULL;
		$regexMethod = "/^([a-z0-9_]+)::([a-z0-9_]+)$/i";
		foreach( $hooks as $levelHooks ){
			foreach( $levelHooks as $hook ){
				if( 0 === strlen( $hook->function ) )
					continue;
				$module		= $this->env->getModules()->get( $hook->moduleId );
				$resource	= $hook->resource;
				$event		= $hook->event;
				$function	= $hook->function;
				try {
					$callback	= explode( "::", $function );
					if( !preg_match( $regexMethod, $function ) )
						throw new RuntimeException( 'Format of hook function is invalid or outdated' );
					if( !class_exists( $callback[0] ) )
						throw new RuntimeException( 'Hook handling class ' . $callback[0] . ' is not existing' );
					if( !method_exists( $callback[0], $callback[1] ) )
						throw new RuntimeException( 'Hook handling function ' . $function . ' is not existing' );
//					if( !is_callable( [$callback[0], $callback[1]] ) )
//						throw new RuntimeException( 'Hook handling function ' . $function . ' is not callable' );

					ob_start();

					/** @var Hook $hookObject */
					$hookObject	= Factory::createObject( $callback[0], [$this->env, $context] );
					$hookObject->setModule( $module )->setPayload( $payload );
					$result		= $hookObject->fetch( $callback[1] );

					$this->env->getRuntime()->reach( vsprintf(
						'<!--Resource_Module_Library_Local::call-->Hook: %s@%s: %s',
						[$event, $resource, $hook->moduleId]
					) );

					$stdout		= (string) ob_get_clean();
					$this->handleStdoutOfResourceEventHookCall( $stdout, $resource, $event, $module );
				} catch( Exception $e ){
					$messageParams	= [$module->id, $resource, $event, $e->getMessage()];
					$this->handleExceptionOfResourceEventHookCall( $e, $resource, $event, $module );
					if( $this->env->has( 'messenger' ) ){
						$this->env->getMessenger()?->noteFailure( vsprintf(
							'Call on event %s@%s hooked by module %s failed: %s',
							$messageParams
						) );
						$this->env->getLog()?->logException( $e );
					} else
						throw new RuntimeException( vsprintf( 'Hook %s::%s@%s failed: %s', $messageParams ), 0, $e );
				} finally {
					unset( $this->openHooks[$resource.'::'.$event] );
				}
			}
			if( TRUE === $result )
				return TRUE;
		}
		return FALSE;
	}

	/**
	 *	@param		Exception			$e
	 *	@param		string				$resource
	 *	@param		string				$event
	 *	@param		ModuleDefinition	$module
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function handleExceptionOfResourceEventHookCall( Exception $e, string $resource, string $event, ModuleDefinition $module ): void
	{
		$this->env->getLog()?->logException( $e );

//		$message	= 'Hook %1$s::%2$s@%3$s failed: %4$s';
//		$message	= 'Call on resource event hook %3$s@%2$s, hooked by module %1$s, failed: %4$s';
		$message	= 'Fetching resource event hook %1$s>>%2$s>>%3$s failed: %4$s';
		$message	= sprintf( $message, $module->id, $resource, $event, $e->getMessage() );
		if( !$this->env->has( 'messenger' ) )
			throw new RuntimeException( $message, 0, $e );
		$this->env->getMessenger()?->noteFailure( $message );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		string				$stdout
	 *	@param		string				$resource
	 *	@param		string				$event
	 *	@param		ModuleDefinition	$module
	 *	@return		void
	 *	@throws		RuntimeException	if environment has no messenger
	 *	@throws		ReflectionException
	 */
	protected function handleStdoutOfResourceEventHookCall( string $stdout, string $resource, string $event, ModuleDefinition $module ): void
	{
		if( 0 === strlen( trim( $stdout ) ) )
			return;
		$this->env->getLog()?->log( 'notice', $stdout, (object) [
			'resource'	=> $resource,
			'event'		=> $event,
			'module'	=> $module
		] );
		if( !$this->env->has( 'messenger' ) )
			throw new RuntimeException( $stdout );
		$this->env->getMessenger()?->noteNotice( vsprintf(
			'Call on event %2$s@%1$s hooked by module %3$s reported: <xmp>%4$s</xmp>',
			[$resource, $event, $module->id, $stdout]
		) );
	}
}
