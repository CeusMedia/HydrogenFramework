<?php
class CMF_Hydrogen_Environment_Resource_CacheDummy{

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		boolean		Always true
	 */
	public function flush(){
		return TRUE;
	}

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		NULL		Always null
	 */
	public function get( $key ){
		return NULL;
	}

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		boolean		Always true.
	 */
	public function has( $key ){
		return FALSE;
	}

	/**
	 *	Simulate to store cache.
	 *	@access		public
	 *	@return		boolean		Always false
	 */
	public function set( $key, $value ){
		return FALSE;
	}
}
?>
