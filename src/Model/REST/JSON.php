<?php
abstract class CMF_Hydrogen_Model_REST_JSON extends CMF_Hydrogen_Model_Abstract
{
	public static $resourceRouteBasePath	= '';
	public static $tokenSessionKey			= 'token';
	public static $clientEnvKey				= 'restClient';

	protected $basePath;
	protected $client;

	public function count( array $conditions = array() ): int
	{
		$parameters	= array( 'filters' => $conditions, 'limit' => 1 );
		return $this->client->get( $this->basePath, $parameters )->data->range->total;
	}

	public function create( $data )
	{
		return $this->client->post( $this->basePath, $data );
	}

	public function delete( string $id )
	{
		return $this->client->delete( $this->basePath.'/'.$id )->data;
	}

	public function index( array $conditions = array(), array $orders = array(), array $limit = array() ): array
	{
		$parameters	= array(
			'filters'	=> $conditions,
			'orders'	=> $orders
		);
		return $this->client->get( $this->basePath, $parameters )->data->items;
	}

	public function read( string $id )
	{
		return $this->client->get( $this->basePath.'/'.$id )->data;
	}

	public function update( string $id, $data )
	{
		return $this->client->put( $this->basePath.'/'.$id, $data )->data;
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		if( !strlen( trim( static::$resourceRouteBasePath ) ) ){
			$msg	= 'No resource route base path definied for model %s';
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
