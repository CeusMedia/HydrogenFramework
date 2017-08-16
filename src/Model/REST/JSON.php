<?php
abstract class CMF_Hydrogen_Model_REST_JSON extends CMF_Hydrogen_Model_Abstract{

	static public $resourceRouteBasePath	= '';
	static public $tokenSessionKey			= 'token';
	static public $clientEnvKey				= 'restClient';

	protected $basePath;

	protected function __onInit(){
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

	public function count( $conditions = array() ){
		$parameters	= array( 'filters' => $conditions, 'limit' => 1 );
		return $this->client->get( $this->basePath, $parameters )->data->range->total;
	}

	public function create( $data ){
		return $this->client->post( $this->basePath, $data );
	}

	public function delete( $id ){
		return $this->client->delete( $this->basePath.'/'.$id )->data;
	}

	public function index( $conditions = array(), $orders = array(), $limit = array() ){
		$parameters	= array( 'filters' => $conditions, 'orders' => $orders );
		return $this->client->get( $this->basePath, $parameters )->data->items;
	}

	public function read( $id ){
		return $this->client->get( $this->basePath.'/'.$id )->data;
	}

	public function update( $id, $data ){
		return $this->client->put( $this->basePath.'/'.$id, $data )->data;
	}
}
