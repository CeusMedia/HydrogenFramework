<?php
/**
 *	General (and therefore abstract) AJAX controller.
 */
namespace CeusMedia\HydrogenFramework\Controller;

use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use Net_HTTP_Response as HttpResponse;
use Net_HTTP_Response_Sender as HttpResponseSender;
use Exception;
use Throwable;

/**
 *	General (and therefore abstract) AJAX controller.
 */
abstract class Ajax
{
	protected $env;

	protected $request;

	protected $response;

	protected $session;

	protected $defaultResponseMimeType	= 'text/json';

	protected $compressionMethod		= 'gzip';

	protected $exitAfterwards			= TRUE;

	protected $sendLengthHeader			= FALSE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment		$env		Environment object
	 *	@return		void
	 */
	public function __construct( WebEnvironment $env )
	{
		$this->env	= $env;
		try{
			$this->request		= $this->env->getRequest();
			$this->session		= $this->env->getSession();
			$this->response		= new HttpResponse();
		}
		catch( Exception $e ){
			$this->respondException( $e, 500 );
		}
		if( $this->env->getMode() & Environment::MODE_LIVE ){
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

	//  --  PROTECTED  --  //

	/**
	 *	Use this method to extend construction in you inherited AJAX controller.
	 *	Please remember to declare any new members as protected!
	 *	Please note any throwable exception!
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit()
	{
	}

	/**
	 *	Sends response data.
	 *	Exits afterwards, if enabled (default: yes).
	 *	@access		public
	 *	@param		mixed		$data			Data to be responded, will be stringified/serialized
	 *	@param		integer		$statusCode		HTTP status code of response
	 *	@param		string		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer		Number of sent bytes, if exitAfterwards is disabled (default: no)
	 *	@todo		support other serializations, too
	 */
	protected function respondData( $data, int $statusCode = 200, string $mimeType = NULL ): int
	{
		$response	= array(
			'status'	=> 'data',
			'data'		=> $data,
		);
		if( ob_get_level() && strlen( trim( $dev = ob_get_clean() ) ) )
			$response['dev']	= $dev;
		return $this->respond( json_encode( $response ), $statusCode, $mimeType );
	}

	/**
	 *	Sends error message.
	 *	Exits afterwards, if enabled (default: yes).
	 *	@access		protected
	 *	@param		string|int	$code			Error code to send
	 *	@param		string		$message		Error message to send
	 *	@param		integer		$statusCode		HTTP status code of response
	 *	@param		string		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer		Number of sent bytes, if exitAfterwards is disabled (default: no)
	 */
	protected function respondError( $code, string $message = NULL, int $statusCode = 412, string $mimeType = NULL ): int
	{
		$response	= array(
			'status'	=> 'error',
			'code'		=> $code,
			'message'	=> $message,
		);
		if( ob_get_level() && strlen( trim( $dev = ob_get_clean() ) ) )
			$response['dev']	= $dev;
		return $this->respond( json_encode( $response ), $statusCode, $mimeType );
	}

	/**
	 *	Sends caught exception.
	 *	Exits afterwards, if enabled (default: yes).
	 *	@access		protected
	 *	@param		Throwable	$exception		Caught exception
	 *	@param		integer		$statusCode		HTTP status code of response
	 *	@param		string		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer		Number of sent bytes, if exitAfterwards is disabled (default: no)
	 */
	protected function respondException( Throwable $exception, int $statusCode = 500, string $mimeType = NULL ): int
	{
		$response	= array(
			'status'	=> 'exception',
			'code'		=> $exception->getCode(),
			'message'	=> $exception->getMessage(),
			'file'		=> $exception->getFile(),
			'line'		=> $exception->getLine(),
		);
		if( ob_get_level() && strlen( trim( $dev = ob_get_clean() ) ) )
			$response['dev']	= $dev;
		return $this->respond( json_encode( $response ), $statusCode, $mimeType );
	}

	/**
	 *	Sends prepared response string.
	 *	Exits afterwards, if enabled (default: yes).
	 *	@access		protected
	 *	@param		string		$content		Stringified/serialized content to send
	 *	@param		integer		$statusCode		HTTP status code of response
	 *	@param		string		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer		Number of sent bytes, if exitAfterwards is disabled (default: no)
	 */
	protected function respond( string $content, int $statusCode = NULL, string $mimeType = NULL ): int
	{
		$mimeType	= $mimeType ? $mimeType : $this->defaultResponseMimeType;
		$statusCode	= $statusCode ? $statusCode : 200;

		$this->response->addHeaderPair( 'Content-Type', $mimeType );
		$this->response->setBody( $content );
		$this->response->setStatus( $statusCode );

		if( ob_get_level() && strlen( trim( $dev = ob_get_clean() ) ) )
			$this->response->addHeaderPair( 'X-Ajax-Dev', base64_encode( $dev ) );

		$sender	= new HttpResponseSender( $this->response );
	    $sender->setCompression( $this->compressionMethod );
	    return $sender->send( $this->sendLengthHeader, $this->exitAfterwards );
	}
}
