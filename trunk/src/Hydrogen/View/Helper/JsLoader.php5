<?php
class Framework_Hydrogen_View_Helper_JsLoader
{
	/**	@var		ADT_Singleton		$instance		Instance of Singleton */
	protected static $instance;
	protected $pathCache	= "cache/";
	protected $revision;
	protected $scripts		= array();

	/**
	 *	Constructor is disabled from public context.
	 *	Use static call 'getInstance()' instead of 'new'.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __construct(){}

	/**
	 *	Cloning this object is not allowed.
	 *	@access		private
	 *	@return		void
	 */
	private function __clone(){}

	public function addUrl( $url, $onTop = FALSE ){
		$onTop ? array_unshift( $this->scripts, $url ) : array_push( $this->scripts, $url );
	}

	public function clearCache(){
		$index	= new File_RegexFilter( $this->pathCache, '/\.js$/' );
		foreach( $index as $file )
			unlink( $file->getPathname() );
	}

	/**
	 *	Returns a single instance of this Singleton class.
	 *	@static
	 *	@access		public
	 *	@return		ADT_Singleton	Single instance of this Singleton class
	 */
	public static function getInstance(){
		if( !self::$instance )
			self::$instance	= new Framework_Hydrogen_View_Helper_JsLoader();
		return self::$instance;
	}

	public function getHash() {
		$key	= implode( '_', $this->scripts );
		return md5( $this->revision.$key );
	}

	public function getCacheFileName(){
		$hash	= $this->getHash();
		return $this->pathCache.$hash.'.js';
	}

	public function getContent(){
		if( $revision )
			$content	= "/* @revision ".$revision." */\n";
		$fileJs	= $this->getCacheFileName();
		if( file_exists( $fileJs ) )
			return File_Reader::load( $fileJs );
		$contents	= array();
		foreach( $this->scripts as $url ){
			$content	= file_get_contents( $url );
			$contents[]	= $content;
		}
		$content	= implode( "\n\n", $contents );
		File_Writer::save( $fileJs, $content );
		return $content;
	}

	public function getFileName(){
		$fileJs	= $this->getCacheFileName();
		if( !file_exists( $fileJs ) ) {
			$contents	= array();
			foreach( $this->scripts as $url ){
				$content	= file_get_contents( $url );
				$contents[]	= $content;
			}
			$content	= implode( "\n\n", $contents );
			$attributes	= array(
				'type'		=> 'text/javascript',
				'language'	=> 'JavaScript'
			);
			File_Writer::save( $fileJs, $content );
		}
		return $fileJs;
	}

	public function getScriptTag(){
		$fileJs	= $this->getFileName();
		$attributes	= array(
			'type'		=> 'text/javascript',
			'language'	=> 'JavaScript',
			'src'		=> $fileJs
		);
		return UI_HTML_Tag::create( 'script', NULL, $attributes );
	}

	public function getUrlList(){
		return $this->scripts;
	}

	public function setRevision( $revision ) {
		$this->revision	= $revision;
	}

	public function setCachePath( $path ) {
		$this->pathCache = $path;
	}
}
?>