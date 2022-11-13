<?php /** @noinspection PhpUnused */

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2022 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\HydrogenFramework\Environment;
use DateTimeInterface;
use DomainException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2022 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Log
{

	public const TYPE_DEBUG		= 'debug';
	public const TYPE_INFO		= 'info';
	public const TYPE_NOTE		= 'note';
	public const TYPE_WARN		= 'warn';
	public const TYPE_ERROR		= 'error';

	public const TYPES			= [
		self::TYPE_DEBUG,
		self::TYPE_INFO,
		self::TYPE_NOTE,
		self::TYPE_WARN,
		self::TYPE_ERROR,
	];

	public const STRATEGY_APP_DEFAULT		= 'app-default';
	public const STRATEGY_APP_TYPED			= 'app-typed';
	public const STRATEGY_MODULE_HOOKS		= 'module-hooks';
	public const STRATEGY_CUSTOM_HOOKS		= 'custom-hooks';
	public const STRATEGY_CUSTOM_CALLBACK	= 'custom-callback';

	public const STRATEGIES				= [
		self::STRATEGY_APP_DEFAULT,
		self::STRATEGY_APP_TYPED,
		self::STRATEGY_MODULE_HOOKS,
		self::STRATEGY_CUSTOM_HOOKS,
		self::STRATEGY_CUSTOM_CALLBACK,
	];

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

	protected ?array $customExceptionCallback;

	protected ?array $customLogCallback;

	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 *	@todo 		add path fallback using Null-Coalesce
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->path	= $env->getConfig()->get( 'path.logs' );
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
	 *	@param		mixed				$message		Message as string, array or data object
	 *	@param		string|object|NULL	$context		Context of message as object or string
	 *	@return		bool
	 *	@trigger	Env::log			Calls hook for handling by installed modules
	 *	@trigger	Env:Custom::log		Calls hook for handling by custom module hooks
	 *	@throws		ReflectionException
	 */
	public function log( string $type, $message, $context = NULL ): bool
	{
		$context	= NULL === $context ? (object) [] : $context;
		$context	= is_string( $context ) ? (object) ['context' => $context] : $context;
		$data		= $this->collectLogData( $type, (string) $message, $context );
		return $this->applyStrategyOnCollectedData( $data, $context );
	}

	/**
	 *	Logs exception by registered hooks or local log file fallback.
	 *	@access		public
	 *	@param		Throwable			$exception		Exception to log
	 *	@param		string|object		$context		Context of message as object or string
	 *	@return		boolean				TRUE if handled by called module hooks
	 *	@trigger	Env::logException	Calls hook for handling by installed modules
	 *	@throws		ReflectionException
	 */
	public function logException( Throwable $exception, $context = NULL ): bool
	{
		$context	= NULL === $context ? (object) ['context' => NULL] : $context;
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
	 *	@throws		ReflectionException
	 */
	protected function applyStrategyOnCollectedData( array $data, object $context ): bool
	{
		$isHandled	= FALSE;
		foreach( $this->strategies as $strategy ){
			switch( $strategy ){
				case self::STRATEGY_MODULE_HOOKS:
					$captain	= $this->env->getCaptain();
					$isHandled	= $captain->callHook( 'Env', 'log', $context, $data );
					break;
				case self::STRATEGY_CUSTOM_HOOKS:
					$captain	= $this->env->getCaptain();
					$isHandled	= $captain->callHook( 'Env:Custom', 'log', $context, $data );
					break;
				case self::STRATEGY_CUSTOM_CALLBACK:
					$isHandled	= $this->handleLogWithCustomCallback( $data );
					break;
				case self::STRATEGY_APP_TYPED:
					$isHandled	= $this->handleLogWithAppTyped( $data );
					break;
				case self::STRATEGY_APP_DEFAULT:
					$isHandled	= $this->handleLogWithAppDefault( $data );
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
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 *	@throws		RuntimeException
	 */
	protected function applyStrategyOnCollectedExceptionData( array $data, object $context ): bool
	{
		$isHandled	= FALSE;
		foreach( $this->strategies as $strategy ){
			switch( $strategy ){
				case self::STRATEGY_MODULE_HOOKS:
					$captain	= $this->env->getCaptain();
					$isHandled	= $captain->callHook( 'Env', 'logException', $context, $data );
					break;
				case self::STRATEGY_CUSTOM_HOOKS:
					$captain	= $this->env->getCaptain();
					$isHandled	= $captain->callHook( 'Env:Custom', 'logException', $context, $data );
					break;
				case self::STRATEGY_CUSTOM_CALLBACK:
					$isHandled	= $this->handleExceptionWithCustomCallback( $data );
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
	 *	...
	 *	@access		protected
	 *	@param		array		$data
	 *	@return		bool
	 */
	protected function handleExceptionWithAppDefault( array $data ): bool
	{
		$entry	= vsprintf( '%s THROW %s %s'.PHP_EOL.'%s'.PHP_EOL, [
			$data['datetime'],
			get_class( $data['exception'] ),
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
			'Type:       '.get_class( $data['exception'] ),
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
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	protected function handleExceptionWithCustomCallback( array $data ): bool
	{
		if( $this->customExceptionCallback ){
			$callable	= $this->customExceptionCallback;
			if( is_object( $callable[0] ) && is_callable( $callable, TRUE ) ){
				$className	= get_class( $callable[0] );
				$reflection	= new ReflectionMethod( $className, $callable[1] );
				return $reflection->invokeArgs( $callable[0], [$data] );
			}
			if( class_exists( $callable[0] ) && is_callable( $callable, TRUE ) )
				return (bool) call_user_func_array( $callable, [$data] );
		}
		return FALSE;
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
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	protected function handleLogWithCustomCallback( array $data ): bool
	{
		if( $this->customLogCallback ){
			$callable	= $this->customLogCallback;
			if( is_object( $callable[0] ) && is_callable( $callable, TRUE ) ){
				$className	= get_class( $callable[0] );
				$reflection	= new ReflectionMethod( $className, $callable[1] );
				return $reflection->invokeArgs( $callable[0], [$data] );
			}
			if( class_exists( $callable[0] ) && is_callable( $callable, TRUE ) )
				return (bool) call_user_func_array( $callable, [$data] );
		}
		return FALSE;
	}
}
