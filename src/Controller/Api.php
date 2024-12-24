<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace CeusMedia\HydrogenFramework\Controller;

use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\Common\UI\DevOutput;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use Exception;
use JsonException;
use ReflectionException;
use Throwable;

abstract class Api
{
	public static array $supportedCompressions	= ['gzip', 'deflate'];

	protected WebEnvironment $env;

	protected HttpRequest $request;

	protected HttpResponse $response;

	protected PartitionSession $session;

	protected bool $exitAfterwards				= TRUE;

	protected bool $sendLengthHeader			= FALSE;

	protected string $defaultResponseMimeType	= 'application/json';

	protected array $supportedMimeTypes			= [
		'application/json',
		'application/x-php',
		'text/json',
	];

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
	 *	@param		array		$content
	 *	@param		string		$mimeType
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		JsonException
	 */
	protected function encodeResponseByMimeType( array $content, string $mimeType ): string
	{
		$payload	= [
			'mimeType'	=> $mimeType,
			'content'	=> $content
		];
		$result	= $this->env->getCaptain()->callHook(
			'ApiController',
			'onEncodeResponse',
			$this,
			$payload
		);
		if( TRUE === $result )
			return $payload['content'];

		DevOutput::$defaultChannel	= DevOutput::CHANNEL_CONSOLE;

		if( 'application/x-php' === $mimeType )
			return serialize( $content );

		if( in_array( $mimeType, ['application/json', 'text/json'], TRUE ) )
			return json_encode( $content, JSON_THROW_ON_ERROR );

		/** @var string $display */
		$display	= print_m( $content, NULL, NULL, TRUE );
		return $display;
	}

	/**
	 *	@param		?string		$mimeType
	 *	@return		string
	 */
	protected function evaluateMimeType( ?string $mimeType = NULL ): string
	{
		$mimeType	= $mimeType ?: $this->defaultResponseMimeType;
		if( in_array( $mimeType, $this->supportedMimeTypes, TRUE ) )
			return $mimeType;
		return $this->defaultResponseMimeType;
	}

	/**
	 *	Sends response data.
	 *	Exits afterwards, if enabled (default: yes).
	 *	@access		public
	 *	@param		mixed			$data			Data to be responded, will be stringified/serialized
	 *	@param		integer			$statusCode		HTTP status code of response
	 *	@param		string|NULL		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer			Number of sent bytes, if exitAfterwards is disabled (default: no)
	 *	@throws		JsonException
	 *	@throws		ReflectionException
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

		$mimeType	= $this->evaluateMimeType( $mimeType );
		$response	= $this->encodeResponseByMimeType( $response, $mimeType );

		return $this->respond( $response, $statusCode, $mimeType );
	}

	/**
	 *	Sends error message.
	 *	Exits afterwards, if enabled (default: yes).
	 *	@access		protected
	 *	@param		string			$message		Error message to send
	 *	@param		string|int		$code			Error code to send
	 *	@param		integer			$statusCode		HTTP status code of response
	 *	@param		string|NULL		$mimeType		MIME type to send (default: defaultMimeType)
	 *	@return		integer			Number of sent bytes, if exitAfterwards is disabled (default: no)
	 */
	protected function respondError( string $message, string|int $code = 0, int $statusCode = 412, ?string $mimeType = NULL ): int
	{
		$response	= [
			'status'	=> 'error',
			'code'		=> $code,
			'message'	=> $message,
		];
		$dev	= (string) ob_get_clean();
		if( ob_get_level() && strlen( trim( $dev ) ) )
			$response['dev']	= $dev;
		$mimeType	= $this->evaluateMimeType( $mimeType );
		try{
			return $this->respond(
				$this->encodeResponseByMimeType( $response, $mimeType ),
				$statusCode,
				$mimeType
			);
		}
		catch( Exception ){
			return $this->respondError( $message, $code, 500, 'text/plain' );
		}
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
		if( ob_get_level() && '' !== trim( $dev ) )
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
			$this->response->addHeaderPair( 'X-API-Dev', base64_encode( $dev ) );

		HttpResponseSender::$supportedCompressions	= self::$supportedCompressions;
		$sender	= new HttpResponseSender( $this->response, $this->request );
		return $sender->send( $this->sendLengthHeader, $this->exitAfterwards )->getBodyLength();
	}
}
