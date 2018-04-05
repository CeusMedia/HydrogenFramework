<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
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
	public function log( $type, $message, $context = NULL, $file = NULL, $line = NULL ){
		$data	= array(
			'type'		=> $type,
			'message'	=> $message,
			'file'		=> $file,
			'line'		=> $line,
		);
		if( $this->env->getCaptain()->callHook( 'Env', 'log', $context, $data ) )
			return;
		error_log( time().': '.$message."\n", 3, $this->path.$type.'.log' );
	}

	/**
	 *	@trigger		Env::logException
	 */
	public function logException( $exception, $context = NULL ){
		$data	= array( 'exception' => $exception );
		if( $this->env->getCaptain()->callHook( 'Env', 'logException', $context, $data ) )
			return;
		error_log( time().': '.$exception->getMessage()."\n", 3, $this->path.'exception.log' );
	}
}
?>
