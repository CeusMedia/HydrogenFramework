<?php /** @noinspection PhpUnused */

/**
 *	A logger factory and log channels depending on configuration.
 *	Uses several strategies to report to different or multiple log targets.
 *
 * 	Strategies defined:
 *		- APP_DEFAULT		= enqueue to file 'logs/app.log'
 *		- APP_TYPED			- enqueue to file 'logs/app.TYPE.log' for types as info, note, warn, error or exception
 *		- MODULE_HOOKS		- call default module hooks for specific handling
 *		- CUSTOM_HOOKS		- call custom module hooks for specific handling
 *		- CUSTOM_CALLBACK	- call injected method, for testing
 *		- MEMORY			- log in memory, for testing
 *
 * 	To each channel, you can send messages of these types:
 *		- DEBUG
 *		- INFO
 *		- NOTE
 *		- WARN
 *		- ERROR
 *
 *	To each channel, you can send exceptions as well.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\Renderable;
use CeusMedia\HydrogenFramework\Environment;
use DateTimeInterface;
use JsonException;
use ReflectionException;
use ReflectionMethod;
use Serializable;
use Stringable;
use Throwable;

/**
 *	A logger factory and log channels depending on configuration.
 *	Uses several strategies to report to different or multiple log targets.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Log
{
	public const string TYPE_DEBUG		= 'debug';
	public const string TYPE_INFO		= 'info';
	public const string TYPE_NOTE		= 'note';
	public const string TYPE_WARN		= 'warn';
	public const string TYPE_ERROR		= 'error';

	public const array TYPES			= [
		self::TYPE_DEBUG,
		self::TYPE_INFO,
		self::TYPE_NOTE,
		self::TYPE_WARN,
		self::TYPE_ERROR,
	];

	public const string STRATEGY_APP_DEFAULT		= 'app-default';		//  enqueue to file 'logs/app.log'
	public const string STRATEGY_APP_TYPED			= 'app-typed';			//  enqueue to file 'logs/app.TYPE.log' for types as info, note, warn, error or exception
	public const string STRATEGY_MODULE_HOOKS		= 'module-hooks';		//  call default module hooks for specific handling
	public const string STRATEGY_CUSTOM_HOOKS		= 'custom-hooks';		//  call custom module hooks for specific handling
	public const string STRATEGY_CUSTOM_CALLBACK	= 'custom-callback';	//  call injected method, for testing
	public const string STRATEGY_MEMORY			= 'memory';				//  log in memory, for testing

	public const array STRATEGIES				= [
		self::STRATEGY_APP_DEFAULT,
		self::STRATEGY_APP_TYPED,
		self::STRATEGY_MODULE_HOOKS,
		self::STRATEGY_CUSTOM_HOOKS,
		self::STRATEGY_CUSTOM_CALLBACK,
		self::STRATEGY_MEMORY,
	];

	/** @var	array				$memoryLog			Public list of log entries of "memory" strategy, for dummy environments and testing  */
	public array $memoryLog						= [];

	/**	@var	Environment			$env				Environment instance */
	protected Environment $env;

	/**	@var	array				$lastStrategies		List of log strategies that end handling on positive result */
	protected array $lastStrategies	= [
		self::STRATEGY_APP_DEFAULT,
	];

	/**	@var	string				$path				Path to log files */
	protected string $path;

	/**	@var	array				$lastStrategies		List of log strategies to use */
	protected array $strategies		= [
		self::STRATEGY_MODULE_HOOKS,
		self::STRATEGY_APP_DEFAULT,
	];

	protected ?array $customExceptionCallback	= NULL;

	protected ?array $customLogCallback			= NULL;

	protected array $failedStrategies			= [];

	/**
	 *	Constructor.
	 *	Receives link to environment.
	 *	@access		public
	 *	@return		void
	 *	@todo 		add path fallback using Null-Coalesce
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->path	= $env->uri.$env->getPath( 'logs' );
	}

	/**
	 *	Returns current list of strategies to be applied on logging.
	 *	@access		public
	 *	@return		array<string>
	 */
	public function getStrategies(): array
	{
		return $this->strategies;
	}

	/**
	 *	Logs message by registered hooks or local log file fallback.
	 *	@access		public
	 *	@param		string				$type			Message type as string (debug,info,note,warn,error), @see ::TYPE_*
	 *	@param		string|object|NULL	$message		Message as string, array or data object
	 *	@param		string|object|NULL	$context		Context of message as object or string
	 *	@return		bool
	 *	@trigger	Env::log			Calls hook for handling by installed modules
	 *	@trigger	Env:Custom::log		Calls hook for handling by custom module hooks
	 */
	public function log( string $type, string|object $message = NULL, string|object $context = NULL ): bool
	{
		$context	??= (object) [];
		$context	= is_string( $context ) ? (object) ['context' => $context] : $context;
		$message	= $this->flattenMessage( $message );
		$data		= $this->collectLogData( $type, $message, $context );
		return $this->applyStrategyOnCollectedData( $data, $context );
	}

	/**
	 *	Logs exception by registered hooks or local log file fallback.
	 *	@access		public
	 *	@param		Throwable			$exception		Exception to log
	 *	@param		string|object|NULL	$context		Context of message as object or string
	 *	@return		boolean				TRUE if handled by called module hooks
	 *	@trigger	Env::logException	Calls hook for handling by installed modules
	 */
	public function logException( Throwable $exception, string|object $context = NULL ): bool
	{
		$context	??= (object) [];
		$context	= is_string( $context ) ? (object) ['context' => $context] : $context;
		$data		= $this->collectLogExceptionData( $exception, $context );
		return $this->applyStrategyOnCollectedExceptionData( $data, $context );
	}

	/**
	 *	Sets callback for custom logging using strategy STRATEGY_CUSTOM_CALLBACK.
	 *	@access		public
	 *	@param		array		$callback		Callback, like [class, method] or [object, method]
	 *	@return		self
	 */
	public function setCustomerLogCallback( array $callback ): self
	{
		$this->customLogCallback	= $callback;
		return $this;
	}

	/**
	 *	Sets callback for custom exception logging using strategy STRATEGY_CUSTOM_CALLBACK.
	 *	@access		public
	 *	@param		array		$callback		Callback, like [class, method] or [object, method]
	 *	@return		self
	 */
	public function setCustomerExceptionCallback( array $callback ): self
	{
		$this->customExceptionCallback	= $callback;
		return $this;
	}

	/**
	 *	Sets list of strategies that end handling on positive result.
	 *	Default is: [STRATEGY_APP_DEFAULT]
	 *	@access		public
	 *	@param		array		$strategies		List of log strategies that end handling on positive result
	 *	@return		self
	 */
	public function setLastStrategies( array $strategies ): self
	{
		$this->lastStrategies	= $strategies;
		return $this;
	}

	/**
	 *	Sets which log strategies should be used on what order.
	 *	Default is: [STRATEGY_MODULE_HOOKS, STRATEGY_APP_DEFAULT]
	 *	Possible others: STRATEGY_APP_TYPED, STRATEGY_CUSTOM_HOOKS (not implemented)
	 *	@access		public
	 *	@param		array		$strategies		List of log strategies to use
	 *	@return		self
	 */
	public function setStrategies( array $strategies ): self
	{
		$this->strategies	= $strategies;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@param		object		$context
	 *	@return		bool
	 */
	protected function applyStrategyOnCollectedData( array $data, object $context ): bool
	{
		$isHandled	= FALSE;
		foreach( $this->strategies as $strategy ){
			switch( $strategy ){
				case self::STRATEGY_MODULE_HOOKS:
					$isHandled	= $this->handleLogWithModuleHooks( $data, $context );
					break;
				case self::STRATEGY_CUSTOM_HOOKS:
					$isHandled	= $this->handleLogWithCustomHooks( $data, $context );
					break;
				case self::STRATEGY_CUSTOM_CALLBACK:
					$isHandled	= $this->handleLogWithCustomCallback( $data, $context );
					break;
				case self::STRATEGY_APP_TYPED:
					$isHandled	= $this->handleLogWithAppTyped( $data );
					break;
				case self::STRATEGY_APP_DEFAULT:
					$isHandled	= $this->handleLogWithAppDefault( $data );
					break;
				case self::STRATEGY_MEMORY:
					$isHandled	= $this->handleLogWithMemory( $data );
					break;
			}
			if( $isHandled && in_array( $strategy, $this->lastStrategies ) )
				break;
		}
		return $isHandled;
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@param		object		$context
	 *	@return		bool
	 */
	protected function applyStrategyOnCollectedExceptionData( array $data, object $context ): bool
	{
		$isHandled	= FALSE;
		foreach( $this->strategies as $strategy ){
			if( in_array( $strategy, $this->failedStrategies ) )
				continue;
			switch( $strategy ){
				case self::STRATEGY_MODULE_HOOKS:
					$isHandled	= $this->handleExceptionWithModuleHooks( $data, $context );
					break;
				case self::STRATEGY_CUSTOM_HOOKS:
					$isHandled	= $this->handleExceptionWithCustomHooks( $data, $context );
					break;
				case self::STRATEGY_CUSTOM_CALLBACK:
					$isHandled	= $this->handleExceptionWithCustomCallback( $data, $context );
					break;
				case self::STRATEGY_APP_TYPED:
					$isHandled	= $this->handleExceptionWithAppTyped( $data );
					break;
				case self::STRATEGY_APP_DEFAULT:
					$isHandled	= $this->handleExceptionWithAppDefault( $data );
					break;
			}
			if( $isHandled && in_array( $strategy, $this->lastStrategies ) )
				break;
		}
		$this->failedStrategies	= [];
		return $isHandled;
	}

	protected function collectLogData( string $type, string $message, object $context ): array
	{
		return [
			'type'			=> $type,
			'message'		=> $message,
			'context'		=> $context,
			'env'			=> $this->env,
			'datetime'		=> date( DateTimeInterface::RFC3339_EXTENDED ),
			'microtime'		=> microtime( TRUE ),
		];
	}

	protected function collectLogExceptionData( Throwable $exception, object $context ): array
	{
		return [
			'exception'		=> $exception,
			'context'		=> $context,
			'env'			=> $this->env,
			'datetime'		=> date( DateTimeInterface::RFC3339_EXTENDED ),
			'microtime'		=> microtime( TRUE ),
		];
	}

	/**
	 *	@param		string|object|NULL $message
	 *	@return		string
	 */
	protected function flattenMessage( string|object $message = NULL ): string
	{
		if( NULL === $message )
			return '';
		if( is_string( $message ) )
			return $message;
		if( $message instanceof Renderable )
			return $message->render();
		if( $message instanceof Serializable ){
			try{
				return $message->serialize();
			}
			catch( Throwable $e ) {
				return $e->getMessage();
			}
		}
		if( $message instanceof Stringable )
			return (string) $message;
		try{
			return json_encode( $message, JSON_THROW_ON_ERROR );
		}
		catch( JsonException ){
			return json_last_error_msg();
		}
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@return		bool
	 */
	protected function handleExceptionWithAppDefault( array $data ): bool
	{
		$entry	= vsprintf( '%s THROW %s %s'.PHP_EOL.'%s'.PHP_EOL, [
			$data['datetime'],
			$data['exception']::class,
			$data['exception']->getMessage(),
			$data['exception']->getTraceAsString(),
		] );
		return error_log( $entry, 3, $this->path.'app.log' );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@return		bool
	 */
	protected function handleExceptionWithAppTyped( array $data ): bool
	{
		$entry	= join( PHP_EOL, [
			'Datetime:   '.$data['datetime'],
			'Microtime:  '.$data['microtime'],
			'Type:       '.$data['exception']::class,
			'Message:    '.$data['exception']->getMessage(),
			'Trace:',
			$data['exception']->getTraceAsString(),
		] ).PHP_EOL.PHP_EOL;
		return error_log( $entry, 3, $this->path.'app.exception.log' );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@param		object		$context		Context data object
	 *	@return		bool
	 */
	protected function handleExceptionWithCustomCallback( array $data, object $context ): bool
	{
		if( NULL !== $this->customExceptionCallback ){
			$callable	= $this->customExceptionCallback;
			if( is_object( $callable[0] ) && is_callable( $callable, TRUE ) ){
				try{
					$reflection	= new ReflectionMethod( $callable[0]::class, $callable[1] );
					$reflection->invokeArgs( $callable[0], [$data] );
					return TRUE;
				}
				catch( ReflectionException $e ){
					$this->failedStrategies[]	= self::STRATEGY_CUSTOM_CALLBACK;
					$this->logException( $e );
//					$this->handleExceptionWithAppTyped( $this->collectLogExceptionData( $e, $context ) );		//  log invocation error
//					$this->handleExceptionWithAppDefault( $data );												//  log former error
					return FALSE;
				}
			}
			if( class_exists( $callable[0] ) && is_callable( $callable, TRUE ) )
				return (bool) call_user_func_array( $callable, [$data] );
		}
		return FALSE;
	}

	/**
	 *	@param		array		$data
	 *	@param		object		$context		Context data object
	 *	@return		bool
	 */
	protected function handleExceptionWithCustomHooks( array $data, object $context ): bool
	{
		try{
			return $this->env->getCaptain()
				->callHook( 'Env:Custom', 'logException', $context, $data ) ?? FALSE;
		}
		catch( Throwable $e ){
			$this->handleExceptionWithAppTyped( ['type' => 'exception', 'exception' => $e] );
			return FALSE;
		}
	}

	/**
	 *	@param		array		$data
	 *	@param		object		$context		Context data object
	 *	@return		bool
	 */
	protected function handleExceptionWithModuleHooks( array $data, object $context ): bool
	{
		try{
			return $this->env->getCaptain()
				->callHook( 'Env', 'logException', $context, $data ) ?? FALSE;
		}
		catch( Throwable $t ){
			$this->failedStrategies[]	= self::STRATEGY_MODULE_HOOKS;
			$this->logException( $t );
//			$this->handleExceptionWithAppTyped( ['type' => 'exception', 'exception' => $t] );
			return FALSE;
		}
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@return		bool
	 */
	protected function handleLogWithAppDefault( array $data ): bool
	{
		$message	= $data['message'];
		$type		= strtoupper( $data['type'] );
		if( !is_string( $message ) && !is_numeric( $message ) )
			$message	= json_encode( $message );
		$entry		= $data['datetime'].' '.$type.' '.$message.PHP_EOL;
		return error_log( $entry, 3, $this->path.'app.log' );
	}
	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@return		bool
	 */
	protected function handleLogWithMemory( array $data ): bool
	{
		$this->memoryLog[]	= $data;
		return TRUE;
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@return		bool
	 */
	protected function handleLogWithAppTyped( array $data ): bool
	{
		$message	= $data['message'];
		$type		= strtolower( $data['type'] );
		if( !is_string( $message ) && !is_numeric( $message ) )
			$message	= json_encode( $message );
		$entry		= $data['datetime'].' '.$message.PHP_EOL;
		$logFile	= $this->path.'app.'.$type.'.log';
		return error_log( $entry, 3, $logFile );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@param		object		$context		Context data object
	 *	@return		bool
	 */
	protected function handleLogWithCustomCallback( array $data, object $context ): bool
	{
		if( NULL !== $this->customLogCallback ){
			$callable	= $this->customLogCallback;
			if( is_object( $callable[0] ) && is_callable( $callable, TRUE ) ){
				try{
					$reflection	= new ReflectionMethod( $callable[0]::class, $callable[1] );
					$reflection->invokeArgs( $callable[0], [$data] );
					return TRUE;
				}
				catch( Throwable $e ){
					$this->failedStrategies[]	= self::STRATEGY_CUSTOM_CALLBACK;
					$this->logException( $e );
//					$this->handleExceptionWithAppTyped( $this->collectLogExceptionData( $e, $context ) );		//  log invocation error
					return FALSE;
				}
			}
			if( class_exists( $callable[0] ) && is_callable( $callable, TRUE ) )
				return (bool) call_user_func_array( $callable, [$data] );
		}
		return FALSE;
	}

	/**
	 *	@param		array		$data
	 *	@param		object		$context		Context data object
	 *	@return		bool
	 */
	protected function handleLogWithCustomHooks( array $data, object $context ): bool
	{
		try{
			return $this->env->getCaptain()
				->callHook( 'Env:Custom', 'log', $context, $data ) ?? FALSE;
		}
		catch( Throwable $e ){
			$this->failedStrategies[]	= self::STRATEGY_CUSTOM_HOOKS;
			$this->logException( $e );
//			$this->handleExceptionWithAppTyped( $this->collectLogExceptionData( $e, $context ) );
		}
		return FALSE;
	}

	/**
	 *	@param		array		$data
	 *	@param		object		$context		Context data object
	 *	@return		bool
	 */
	protected function handleLogWithModuleHooks( array $data, object $context ): bool
	{
		try{
			return $this->env->getCaptain()
				->callHook( 'Env', 'log', $context, $data ) ?? FALSE;
		}
		catch( Throwable $e ){
			$this->failedStrategies[]	= self::STRATEGY_MODULE_HOOKS;
			$this->logException( $e );
//			$this->handleLogWithAppTyped( $this->collectLogExceptionData( $e, $context ) );
		}
		return FALSE;
	}
}
