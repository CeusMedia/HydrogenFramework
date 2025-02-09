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
abstract class Ajax extends Abstraction
{
	public const RESPONSE_STRATEGY_DEFAULT		= 0;
	public const RESPONSE_STRATEGY_CALLBACK	= 1;

	public const RESPONSE_FORMAT_XML			= 0;
	public const RESPONSE_FORMAT_JSON			= 1;

	public static array $supportedCompressions	= ['gzip', 'deflate'];

	public static array $responseCallback		= [];

	public static int $responseStrategy			= self::RESPONSE_STRATEGY_DEFAULT;

	public static int $responseFormat			= self::RESPONSE_FORMAT_JSON;

	protected WebEnvironment $env;

	protected HttpRequest $request;

	protected HttpResponse $response;

	protected PartitionSession $session;

	protected string $defaultResponseMimeType	= 'text/json';

	protected bool $exitAfterwards				= TRUE;

	protected bool $sendLengthHeader			= FALSE;

	public static function setResponseStrategy( int $strategy, mixed $callback = NULL ): void
	{
		static::$responseStrategy	= $strategy;
		if( static::RESPONSE_STRATEGY_CALLBACK === $strategy )
			static::$responseCallback	= $callback;
	}

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

		if( $this->env->isInLiveMode() && !$this->request->isAjax() )
			$this->respondError( 400000, 'Access denied for non-AJAX requests', 406 );

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
	 *	@codeCoverageIgnore
	 */
	protected function __onInit()
	{
	}

	protected function renderResponseBody( array $response ): string
	{
		return match( self::$responseFormat ){
			static::RESPONSE_FORMAT_JSON	=> json_encode( $response, JSON_THROW_ON_ERROR ),
			default							=> $this->transformRecursiveArrayToXmlString( $response ),
		};
	}

	/**
	 *	Sends prepared response string.
	 *	Exits afterward, if enabled (default: yes).
	 *
	 *	ATTENTION:
	 *	This is the general method which is called by all other respond methods.
	 *	Please try to use respondData, respondError and respondException instead.
	 *
	 *	@access		protected
	 *	@param		string			$content		Stringified/serialized content to send
	 *	@param		integer|NULL	$statusCode		HTTP status code of response
	 *	@param		string|NULL		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer			Number of sent bytes, if exitAfterwards is disabled (default: no)
	 */
	protected function respond( string $content, int $statusCode = NULL, string $mimeType = NULL ): int
	{
		$statusCode	??= 200;
		$mimeType	??= match( self::$responseFormat ){
			static::RESPONSE_FORMAT_JSON	=> 'application/json',//'text/json',
			default							=> 'application/xml',
		};

		$response	= clone( $this->response );
		$response->setHeader( 'Content-Type', $mimeType );
		$response->setBody( $content )->setStatus( $statusCode );

		if( NULL !== $this->devBuffer && $this->devBuffer->isOpen() ){
			if( $this->devBuffer->has() )
				$response->addHeaderPair( 'X-Ajax-Dev', base64_encode( trim( $this->devBuffer->get() ) ) );
			$this->devBuffer->close();
		}

		/** @var callable $callback */
		$callback	= static::$responseCallback;
		return match( static::$responseStrategy ){
			static::RESPONSE_STRATEGY_CALLBACK	=> call_user_func( $callback, $response, self::$responseFormat ),
			default								=> $this->sendResponseWithDefaultHttpResponseSender( $response ),
		};
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
		if( NULL !== $this->devBuffer && $this->devBuffer->isOpen() && $this->devBuffer->has() )
			$response['dev']	= trim( $this->devBuffer->get() );

		return $this->respond( $this->renderResponseBody( $response ), $statusCode, $mimeType );
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
		if( NULL !== $this->devBuffer && $this->devBuffer->isOpen() && $this->devBuffer->has() )
			$response['dev']	= trim( $this->devBuffer->get() );

		return $this->respond( $this->renderResponseBody( $response ), $statusCode, $mimeType );
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
		if( NULL !== $this->devBuffer && $this->devBuffer->isOpen() && $this->devBuffer->has() )
			$response['dev']	= trim( $this->devBuffer->get() );

		return $this->respond( $this->renderResponseBody( $response ), $statusCode, $mimeType );
	}

	protected function transformRecursiveArrayToXmlString( array $data ): string
	{
		$xml = new \SimpleXMLElement( '<root/>' );
		array_walk_recursive( $data, function( $value, $key ) use ( $xml ){
			if( !is_array( $xml->{$key} ) )
				$xml->{$key}	= $value;
			else
				$xml->{$key}[]	= $value;
		} );

		/** @var string $string */
		$string	= $xml->asXML();
		return $string;
	}

	/**
	 *	@param		HttpResponse		$response
	 *	@return		int
	 *	@codeCoverageIgnore
	 */
	private function sendResponseWithDefaultHttpResponseSender( HttpResponse $response ): int
	{
		//  New Code: static call, compact and readable
		HttpResponseSender::$supportedCompressions	= self::$supportedCompressions;
		return HttpResponseSender::sendResponseForRequest(
			$response,
			$this->request,
			$this->sendLengthHeader,
			$this->exitAfterwards && !$this->env->isInTestMode()
		)->getBodyLength();

/*		//  Original Code: dynamic call, okay but not cool
		HttpResponseSender::$supportedCompressions	= self::$supportedCompressions;
		$sender			= new HttpResponseSender( $response, $this->request );
		$exitAfterwards	= $this->exitAfterwards && !( $this->env->getMode() & Environment::MODE_TEST );
		$response		= $sender->send( $this->sendLengthHeader, $exitAfterwards );
		return $response->getBodyLength();*/
	}
}
