<?php
abstract class CMF_Hydrogen_Model_Abstract
{
	protected $env;

	protected $idKey;

	protected $className;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
		$this->__onInit();
		$this->className	= get_class( $this );
	}

	/*  --  ABSTRACT CRUDIC METHODS  --  */

	abstract public function count( array $conditions = array() ): int;

	abstract public function create( $data );

	abstract public function delete( string $id );

	abstract public function index( array $conditions = array(), array $orders = array(), array $limits = array() ): array;

	abstract public function read( string $id );

	abstract public function update( string $id, $data );


	/*  --  ALIAS METHODS FOR COMFORT  --  */
/*	public function indexByIndex( $indexKey, $indexValue, $orders, $limits ){
		return $this->index( array( $indexKey => $indexValue ), $orders, $limits );
	}

	public function indexByIndices( $indices, $orders, $limits ){
		return $this->index( $indices, $orders, $limits );
	}*/

	/*  --  ALIAS METHODS FOR COMFORT/COMPAT  --  */

	public function add( /*$id, */$data ){
		$this->create( /*$id, */$data );
	}

	public function edit( string $id, $data ){
		$this->update( $id, $data );
	}

	public function get( string $id )
	{
		return $this->read( $id );
	}

	public function getAll( array $conditions = array(), array $orders = array(), array $limits = array() ): array
	{
		return $this->index( $conditions, $orders, $limits );
	}

	public function getAllByIndex( string $indexKey, $indexValue, array $orders = array(), array $limits = array() ): array
	{
		return $this->index( array( $indexKey => $indexValue ), $orders, $limits );
	}

	public function getAllByIndices( array $indices, array $orders = array(), array $limits = array() ): array
	{
		return $this->index( $indices, $orders, $limits );
	}

	public function has( string $id ): bool
	{
		try{
			@$this->get( $id );
			return TRUE;
		}
		catch( Exception $e ){}
		return FALSE;
	}

	public function remove( string $id )
	{
		return $this->delete( $id );
	}

	public function removeByIndex( string $indexKey, $indexValue ): int
	{
		$items	= $this->index( array( $indexKey => $indexValue ) );
		foreach( $items as $item ){
			$item	= (array) $item;
			if( !isset( $item[$this->idKey] ) ){
				$msg	= 'No value set for ID key %s for modul %s';
				throw new DomainException( sprintf( $msg, $this->idKey, $this->className ) );
			}
			$this->delete( $item[$this->idKey] );
		}
		return count( $items );
	}

	public function removeByIndices( array $indices ): int
	{
		$items	= $this->index( $indices );
		foreach( $items as $item ){
			$item	= (array) $item;
			if( !isset( $item[$this->idKey] ) ){
				$msg	= 'No value set for ID key %s for modul %s';
				throw new DomainException( sprintf( $msg, $this->idKey, $this->className ) );
			}
			$this->delete( $item[$this->idKey] );
		}
		return count( $items );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		if( !$this->idKey )
			throw new Exception( sprintf( 'No ID key set for model %s', $this->className ) );
	}
}
