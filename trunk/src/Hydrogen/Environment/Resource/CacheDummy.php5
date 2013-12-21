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
	 *	@param		string		$key			Cache key
	 *	@return		NULL		Always null
	 */
	public function get( $key ){
		return NULL;
	}

	/**
	 *	Simulate to flush cache.
	 *	@access		public
	 *	@return		boolean		Always false.
	 */
	public function has( $key ){
		return FALSE;
	}

	/**
	 *	Simulate to store cache.
	 *	@access		public
	 *	@param		string		$key			Cache key
	 *	@param		mixed		$value			Value to store
	 *	@return		boolean		Always true
	 */
	public function set( $key, $value ){
		return TRUE;
	}

	/**
	 *	Simulate to store cache.
	 *	@access		public
	 *	@param		string		$content		Context within cache storage
	 *	@return		void
	 */
	public function setContext( $content ){
	}
}
?>
