<?php
class Framework_Hydrogen_View_Helper_JsCollector {

	/**	@var		ADT_Singleton		$instance		Instance of Singleton */
	protected static $instance;
	protected $scripts	= array();

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

	public function addScript( $script, $onTop = FALSE ){
		$onTop ? array_unshift( $this->scripts, $script ) : array_push( $this->scripts, $script );
	}

	/**
	 *	Returns a single instance of this Singleton class.
	 *	This method is abtract and must be defined in inheriting clases.
	 *	@abstract
	 *	@static
	 *	@access		public
	 *	@return		ADT_Singleton	Single instance of this Singleton class
	 */
	public static function getInstance(){
		if( !self::$instance )
			self::$instance	= new Framework_Hydrogen_View_Helper_JsCollector();
		return self::$instance;
	}

	public function getScriptTag( $indentEndTag = FALSE ){
		array_unshift( $this->scripts, '' );
		array_push( $this->scripts, $indentEndTag ? "\t\t" : '' );
		$content	= implode( "\n", $this->scripts );
		$attributes	= array(
			'type'		=> 'text/javascript',
			'language'	=> 'JavaScript',
		);
		return UI_HTML_Tag::create( 'script', $content, $attributes );
	}
}
?>