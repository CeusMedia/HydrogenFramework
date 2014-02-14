<?php
class CMF_Hydrogen_Environment_Console extends CMF_Hydrogen_Environment_Abstract{

	/**	@var	Console_RequestReceiver		$request	Console Request Object */
	protected $request;
	protected $messenger;
	protected $pathConfig	= "";

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $options = array() )
	{
		ob_start();
		try
		{
			parent::__construct( $options );
			$this->initMessenger();																	//  setup user interface messenger
			$this->initRequest();																	//  setup HTTP request handler
#			$this->initResponse();																	//  setup HTTP response handler
#			$this->initRouter();																	//  setup request router
	//		$this->initFieldDefinition();															//  --  FIELD DEFINITION SUPPORT  --  //
#			$this->initLanguage();																	//  setup language support
#			$this->initPage();																		//  
			$this->initAcl();
		}
		catch( Exception $e )
		{
			print( $e->getMessage() );
			die();
		}
	}

	public function getMessenger(){
		return $this->messenger;
	}

	public function getRequest(){
		return $this->request;
	}

	public function initConfiguration(){
		$this->config	= new ADT_List_Dictionary();
	}

	public function initMessenger(){
		$this->messenger	= new Messenger( $this );
	}

	public function initRequest(){
		$this->request	= new Console_RequestReceiver();
	}
}
class Messenger extends CMF_Hydrogen_Environment_Resource_Messenger{
	protected function noteMessage($type, $message) {
		remark( $message );
		flush();
	}
}
?>