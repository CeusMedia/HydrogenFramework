<?php
class CMF_Hydrogen_Environment_Resource_Log {
	
	protected $env;
	protected $path;
	
	public function __construct( $env ){
		$this->env	= $env;
		$this->path	= $env->getConfig()->get( 'path.logs' );
	}

	public function record( $category, $message, $file, $line ){
		error_log( time().': '.$message."\n", 3, $this->path.$category.'.log' );
		
	}
}
?>