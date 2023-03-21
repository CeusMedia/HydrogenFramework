<?php
namespace CeusMedia\HydrogenFramework\Model\REST;

use CeusMedia\HydrogenFramework\Model\Abstraction;
use Exception;

abstract class JSON extends Abstraction
{
	public static string $resourceRouteBasePath		= '';
	public static string $tokenSessionKey			= 'token';
	public static string $clientEnvKey				= 'restClient';

	protected string $basePath;

	/** @var object $client */
	protected object $client;

	public function count( array $conditions = [] ): int
	{
		$parameters	= array( 'filters' => $conditions, 'limit' => 1 );
        /** @phpstan-ignore-next-line */
		return $this->client->get( $this->basePath, $parameters )->data->range->total;
	}

	public function create( $data ): string
	{
        /** @phpstan-ignore-next-line */
		return $this->client->post( $this->basePath, $data );
	}

	public function delete( string $id ): bool
	{
        /** @phpstan-ignore-next-line */
		return $this->client->delete( $this->basePath.'/'.$id )->data;
	}

	public function index( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$parameters	= [
			'filters'	=> $conditions,
			'orders'	=> $orders
		];
        /** @phpstan-ignore-next-line */
		return $this->client->get( $this->basePath, $parameters )->data->items;
	}

	public function read( string $id )
	{
        /** @phpstan-ignore-next-line */
		return $this->client->get( $this->basePath.'/'.$id )->data;
	}

	public function update( string $id, $data ): bool
	{
        /** @phpstan-ignore-next-line */
		return $this->client->put( $this->basePath.'/'.$id, $data )->data;
	}

	//  --  PROTECTED  --  //

	/**
	 * @return void
	 * @throws Exception
	 */
	protected function __onInit(): void
    {
		if( !strlen( trim( static::$resourceRouteBasePath ) ) ){
			$msg	= 'No resource route base path defined for model %s';
			throw new Exception( sprintf( $msg, $this->className ) );
		}
		$this->client	= $this->env->get( static::$clientEnvKey );
		$this->basePath	= static::$resourceRouteBasePath;
		if( static::$tokenSessionKey ){
			$token	= $this->env->getSession()->get( static::$tokenSessionKey );
			$this->client->setAuthToken( $token );
		}
	}
}
