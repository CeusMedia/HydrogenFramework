<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 *	General (and therefore abstract) AJAX controller.
 */

namespace CeusMedia\HydrogenFramework\Controller;

use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use Exception;
use JsonException;
use Throwable;

/**
 *	General (and therefore abstract) AJAX controller.
 */
abstract class Ajax
{
	protected WebEnvironment $env;

	protected HttpRequest $request;

	protected HttpResponse $response;

	protected PartitionSession $session;

	protected string $defaultResponseMimeType	= 'text/json';

	protected string $compressionMethod			= 'gzip';

	protected bool $exitAfterwards				= TRUE;

	protected bool $sendLengthHeader			= FALSE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment		$env		Environment object
	 *	@return		void
	 *	@throws		JsonException
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
			$this->respondException( $e );
		}
		if( $this->env->getMode() & Environment::MODE_LIVE ){
			if( !method_exists( $this->request, 'isAjax' ) || !$this->request->isAjax() )
				$this->respondError( 400000, 'Access denied for non-AJAX requests', 406 );
		}
		try{
			$this->__onInit();
		}
		catch( Exception $e ){
			$this->respondException( $e );
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
	 *	@param		mixed			$data			Data to be responded, will be stringified/serialized
	 *	@param		integer			$statusCode		HTTP status code of response
	 *	@param		string|NULL		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer			Number of sent bytes, if exitAfterwards is disabled (default: no)
	 *	@todo		support other serializations, too
	 *	@throws		JsonException
	 */
	protected function respondData( mixed $data, int $statusCode = 200, ?string $mimeType = NULL ): int
	{
		$response	= [
			'status'	=> 'data',
			'data'		=> $data,
		];
		$dev	= (string) ob_get_clean();
		if( ob_get_level() && strlen( trim( $dev ) ) )
			$response['dev']	= $dev;
		$json	= json_encode( $response, JSON_THROW_ON_ERROR );
		return $this->respond( $json, $statusCode, $mimeType );
	}

	/**
	 *	Sends error message.
	 *	Exits afterwards, if enabled (default: yes).
	 *	@access		protected
	 *	@param		string|int		$code			Error code to send
	 *	@param		string|NULL		$message		Error message to send
	 *	@param		integer			$statusCode		HTTP status code of response
	 *	@param		string|NULL		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer			Number of sent bytes, if exitAfterwards is disabled (default: no)
	 *	@throws		JsonException
	 */
	protected function respondError( string|int $code, ?string $message = NULL, int $statusCode = 412, ?string $mimeType = NULL ): int
	{
		$response	= [
			'status'	=> 'error',
			'code'		=> $code,
			'message'	=> $message,
		];
		$dev	= (string) ob_get_clean();
		if( ob_get_level() && strlen( trim( $dev ) ) )
			$response['dev']	= $dev;
		$json	= json_encode( $response,JSON_THROW_ON_ERROR );
		return $this->respond( $json, $statusCode, $mimeType );
	}

	/**
	 *	Sends caught exception.
	 *	Exits afterward, if enabled (default: yes).
	 *	@access		protected
	 *	@param		Throwable		$exception		Caught exception
	 *	@param		integer			$statusCode		HTTP status code of response
	 *	@param		string|NULL		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer			Number of sent bytes, if exitAfterwards is disabled (default: no)
	 *	@throws		JsonException
	 */
	protected function respondException( Throwable $exception, int $statusCode = 500, ?string $mimeType = NULL ): int
	{
		$response	= [
			'status'	=> 'exception',
			'code'		=> $exception->getCode(),
			'message'	=> $exception->getMessage(),
			'file'		=> $exception->getFile(),
			'line'		=> $exception->getLine(),
		];
		$dev	= (string) ob_get_clean();
		if( ob_get_level() && strlen( trim( $dev ) ) )
			$response['dev']	= $dev;
		$json	= json_encode( $response, JSON_THROW_ON_ERROR );
		return $this->respond( $json, $statusCode, $mimeType );
	}

	/**
	 *	Sends prepared response string.
	 *	Exits afterward, if enabled (default: yes).
	 *	@access		protected
	 *	@param		string			$content		Stringified/serialized content to send
	 *	@param		integer|NULL	$statusCode		HTTP status code of response
	 *	@param		string|NULL		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer			Number of sent bytes, if exitAfterwards is disabled (default: no)
	 */
	protected function respond( string $content, int $statusCode = NULL, string $mimeType = NULL ): int
	{
		$mimeType	= $mimeType ?: $this->defaultResponseMimeType;
		$statusCode	= $statusCode ?: 200;

		$this->response->addHeaderPair( 'Content-Type', $mimeType );
		$this->response->setBody( $content );
		$this->response->setStatus( $statusCode );

		$dev	= (string) ob_get_clean();
		if( ob_get_level() && strlen( trim( $dev ) ) )
			$this->response->addHeaderPair( 'X-Ajax-Dev', base64_encode( $dev ) );

		$sender	= new HttpResponseSender( $this->response );
		$sender->setCompression( $this->compressionMethod );
		return $sender->send( $this->sendLengthHeader, $this->exitAfterwards );
	}
}
