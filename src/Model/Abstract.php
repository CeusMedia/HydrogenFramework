<?php
abstract class CMF_Hydrogen_Model_Abstract{

	protected $env;
	protected $idKey;
	protected $className;

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env	= $env;
		$this->__onInit();
		$this->className	= get_class( $this );
	}

	protected function __onInit(){
		if( !$this->idKey )
			throw new Exception( sprintf( 'No ID key set for model %s', $this->className ) );
	}

	/*  --  ABSTRACT CRUDIC METHODS  --  */

	abstract public function count( $conditions = array() );
	abstract public function create( $data );
	abstract public function delete( $id );
	abstract public function index( $conditions = array(), $orders = array(), $limits = array() );
	abstract public function read( $id );
	abstract public function update( $id, $data );


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

	public function edit( $id, $data ){
		$this->update( $id, $data );
	}

	public function get( $id ){
		return $this->read( $id );
	}

	public function getAll( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->index( $conditions, $orders, $limits );
	}

	public function getAllByIndex( $indexKey, $indexValue, $orders = array(), $limits = array() ){
		return $this->index( array( $indexKey => $indexValue ), $orders, $limits );
	}

	public function getAllByIndices( $indices, $orders = array(), $limits = array() ){
		return $this->index( $indices, $orders, $limits );
	}

	public function has( $id ){
		try{
			@$this->get( $id );
			return TRUE;
		}
		catch( Exception $e ){}
		return FALSE;
	}

	public function remove( $id ){
		return $this->delete( $id );
	}

	public function removeByIndex( $indexKey, $indexValue ){
		$items	= $this->index( array( $indexKey => $indexValue ) );
		foreach( $items as $item ){
			$item	= (array) $item;
			if( !isset( $item[$idKey] ) ){
				$msg	= 'No value set for ID key %s for modul %s';
				throw new DomainException( sprintf( $msg, $this->idKey, $this->className ) );
			}
			$this->delete( $item[$idKey] );
		}
		return count( $items );
	}

	public function removeByIndices( $indices ){
		$items	= $this->index( $indices );
		foreach( $items as $item ){
			$item	= (array) $item;
			if( !isset( $item[$idKey] ) ){
				$msg	= 'No value set for ID key %s for modul %s';
				throw new DomainException( sprintf( $msg, $this->idKey, $this->className ) );
			}
			$this->delete( $item[$idKey] );
		}
		return count( $items );
	}
}
