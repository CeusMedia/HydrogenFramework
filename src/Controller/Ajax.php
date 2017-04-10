<?php
/**
 *	General (and therefore abstract) AJAX controller.
 */
/**
 *	General (and therefore abstract) AJAX controller.
 */
abstract class CMF_Hydrogen_Controller_Ajax{

	protected $request;
	protected $session;
//	protected $messenger;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
		try{
			$this->request		= $this->env->getRequest();
			$this->session		= $this->env->getSession();
			$this->response	= new Net_HTTP_Response();
		}
		catch( Exception $e ){
			$this->respondException( $e, 500 );
		}
		try{
			if( !method_exists( $this->request, 'isAjax' ) || !$this->request->isAjax() )
				throw new BadMethodCallException( 'Access denied for non-AJAX requests', 400000 );
		}
		catch( Exception $e ){
			$this->respondException( $e, 400 );
		}
	}

	protected function respondData( $data ){
		$response	= array(
			'status'	=> 'data',
			'data'		=> $data,
		);
		$this->respond( json_encode( $response ) );
	}

	protected function respondError( $code, $message = NULL ){
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
		$this->respond( json_encode( $response ) );
	}

	protected function respond( $string, $status = NULL ){
		$this->response->setBody( $string );
		if( $status )
			$this->response->setStatus( $status );
		$this->response->addHeaderPair( 'Content-Type', 'text/json' );
		Net_HTTP_Response_Sender::sendResponse( $this->response, 'gzip', TRUE );
	}
}
?>
