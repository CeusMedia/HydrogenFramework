<?php
namespace CeusMedia\HydrogenFramework\Model;

use CeusMedia\HydrogenFramework\Environment;
use DomainException;
use Exception;

abstract class Abstraction
{
	protected Environment $env;

	protected string $idKey;

	protected string $className;

	/**
	 *	@param		Environment		$env
	 *	@throws		Exception		if not ID key id set
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->__onInit();
		$this->className	= get_class( $this );
	}

	/*  --  ABSTRACT CRUD METHODS  --  */

	abstract public function count( array $conditions = [] ): int;

	/**
	 *	@param		array		$data
	 *	@return		string
	 */
	abstract public function create( array $data ): string;

	/**
	 *	@param		string		$id
	 */
	abstract public function delete( string $id ): bool;

	abstract public function index( array $conditions = [], array $orders = [], array $limits = [] ): array;

	/**
	 *	@param		string		$id
	 *	@return		mixed
	 */
	abstract public function read( string $id );

	/**
	 *	@param		string		$id
	 *	@param		array		$data
	 *	@return		bool
	 */
	abstract public function update( string $id, array $data ): bool;


	/*  --  ALIAS METHODS FOR COMFORT  --  */
/*	public function indexByIndex( $indexKey, $indexValue, $orders, $limits ){
		return $this->index( array( $indexKey => $indexValue ), $orders, $limits );
	}

	public function indexByIndices( $indices, $orders, $limits ){
		return $this->index( $indices, $orders, $limits );
	}*/

	/*  --  ALIAS METHODS FOR COMFORT/COMPAT  --  */

	/**
	 *	@param		array		$data
	 *	@return		string
	 */
	public function add( array $data ): string
	{
		return $this->create( $data );
	}

	/**
	 *	@param		string		$id
	 *	@param		array		$data
	 *	@return		void
	 */
	public function edit( string $id, array $data )
	{
		$this->update( $id, $data );
	}

	/**
	 *	@param		string		$id
	 *	@return		mixed
	 */
	public function get( string $id )
	{
		return $this->read( $id );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function getAll( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->index( $conditions, $orders, $limits );
	}

	/**
	 *	@param		string		$indexKey
	 *	@param		mixed		$indexValue
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function getAllByIndex( string $indexKey, $indexValue, array $orders = [], array $limits = [] ): array
	{
		return $this->index( array( $indexKey => $indexValue ), $orders, $limits );
	}

	/**
	 *	@param		array		$indices
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function getAllByIndices( array $indices, array $orders = [], array $limits = [] ): array
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

	/**
	 *	@param		string		$id
	 *	@return		bool
	 */
	public function remove( string $id ): bool
	{
		return $this->delete( $id );
	}

	/**
	 *	@param		string		$indexKey
	 *	@param		mixed		$indexValue
	 *	@return		int
	 *	@throws		DomainException
	 */
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

	/**
	 *	@param		array		$indices
	 *	@return		int
	 */
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

	/**
	 *
	 *	@return		void
	 *	@throws		Exception		if not ID key id set
	 */
	protected function __onInit()
	{
		if( !$this->idKey )
			throw new Exception( sprintf( 'No ID key set for model %s', $this->className ) );
	}
}
