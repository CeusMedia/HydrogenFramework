<?php
/**
 *	General (and therefore abstract) AJAX controller.
 */
/**
 *	General (and therefore abstract) AJAX controller.
 */
abstract class CMF_Hydrogen_Controller_Ajax{

	protected $request;
	protected $response;
	protected $session;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
		try{
			$this->request		= $this->env->getRequest();
			$this->session		= $this->env->getSession();
			$this->response		= new Net_HTTP_Response();
		}
		catch( Exception $e ){
			$this->respondException( $e, 500 );
		}
		if( $this->env->mode & CMF_Hydrogen_Environment::MODE_LIVE ){
			if( !method_exists( $this->request, 'isAjax' ) || !$this->request->isAjax() )
				$this->respondError( 400000, 'Access denied for non-AJAX requests', 406 );
		}
		try{
			$this->__onInit();
		}
		catch( Exception $e ){
			$this->respondException( $e, 500 );
		}
	}

	/**
	 *	Use this method to extend construction in you inherited AJAX controller.
	 *	Please remember to declare any new members as protected!
	 *	Please note any throwable exception!
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(){
	}


	protected function respondData( $data ){
		$response	= array(
			'status'	=> 'data',
			'data'		=> $data,
		);
		$this->respond( json_encode( $response ) );
	}

	protected function respondError( $code, $message = NULL, $httpCode = 412 ){
		$response	= array(
			'status'	=> 'error',
			'code'		=> $code,
			'message'	=> $message,
		);
		$this->respond( json_encode( $response ) );
	}

	protected function respondException( $exception, $httpCode = 500 ){
		$response	= array(
			'status'	=> 'exception',
			'code'		=> $exception->getCode(),
			'message'	=> $exception->getMessage(),
			'file'		=> $exception->getFile(),
			'line'		=> $exception->getLine(),
		);
		$this->respond( json_encode( $response ), $httpCode );
	}

	protected function respond( $string, $status = NULL ){
		$this->response->setBody( $string );
		if( $status )
			$this->response->setStatus( $status );
		$this->response->addHeaderPair( 'Content-Type', 'text/json' );
		Net_HTTP_Response_Sender::sendResponse( $this->response, 'gzip', TRUE, TRUE );
	}
}
?>
