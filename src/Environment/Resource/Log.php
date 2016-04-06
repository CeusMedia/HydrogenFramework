<?php
class CMF_Hydrogen_Environment_Resource_Log {

	protected $env;
	protected $path;

	public function __construct( $env ){
		$this->env	= $env;
		$this->path	= $env->getConfig()->get( 'path.logs' );
	}

	/**
	 *	@trigger		Env::log
	 */
	public function log( $type, $message, $file = NULL, $line = NULL ){
		$data	= array(
			'type'		=> $type,
			'message'	=> $message,
			'file'		=> $file,
			'line'		=> $line,
		);
		if( $this->env->getCaptain()->callHook( 'Env', 'log', $this, $data ) )
			return;
		error_log( time().': '.$message."\n", 3, $this->path.$type.'.log' );
	}

	/**
	 *	@trigger		Env::logException
	 */
	public function logException( $exception ){
		$data	= array( 'exception' => $exception );
		if( $this->env->getCaptain()->callHook( 'Env', 'logException', $this, $data ) )
			return;
		error_log( time().': '.$exception->getMessage()."\n", 3, $this->path.'exception.log' );
	}
}
?>
