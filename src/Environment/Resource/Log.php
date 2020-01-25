<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2019 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2019 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Log {

	/**	@var	CMF_Hydrogen_Environment		$env		Environment instance */
	protected $env;

	protected $path;

	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env	= $env;
		$this->path	= $env->getConfig()->get( 'path.logs' );
	}

	/**
	 *	Logs message by registered hooks or local log file failback.
	 *	@access		public
	 *	@param		int|string			$type			Message type as string (debug,info,note,warn,error) or constant value (see Model_Log_Message::TYPE_*)
	 *	@param		mixed				$message		Message as string, array or data object
	 *	@param		string|object		$context		Context of message as object or string
	 *	@return		void
	 *	@trigger	Env::log			Calls hook for handling by installed modules
	 */
	public function log( $type, $message, $context = NULL/*, $file = NULL, $line = NULL*/ ){
		$data	= array(
			'type'		=> $type,
			'message'	=> $message,
//			'file'		=> $file,
//			'line'		=> $line,
		);
		if( $this->env->getCaptain()->callHook( 'Env', 'log', $context, $data ) )
			return TRUE;
		if( !is_string( $message ) && !is_numeric( $message ) )
			$message	= json_encode( $message );
		$entry	= array( microtime( TRUE ), '['.$type.']', $message );
		error_log( join( ' ', $entry )."\n", 3, $this->path.'app.log' );

//		$entry	= array( microtime( TRUE ), $message );
//		error_log( join( ' ', $entry )."\n", 3, $this->path.'app.'.$type.'.log' );
		return FALSE;
	}

	/**
	 *	Logs exception by registered hooks or local log file failback.
	 *	@access		public
	 *	@param		exception			$exception		Exception to log
	 *	@param		string|object		$context		Context of message as object or string
	 *	@return		boolean				TRUE if handled by called module hooks
	 *	@trigger	Env::logException	Calls hook for handling by installed modules
	 */
	public function logException( $exception, $context = NULL ){
		$data	= array( 'exception' => $exception );
		if( $this->env->getCaptain()->callHook( 'Env', 'logException', $context, $data ) )
			return TRUE;

		$entry	= array( microtime( TRUE ), '[exception]', $exception->getMessage() );
		error_log( join( ' ', $entry )."\n", 3, $this->path.'app.log' );

//		error_log( time().': '.$exception->getMessage()."\n", 3, $this->path.'exception.log' );
		return FALSE;
	}
}
