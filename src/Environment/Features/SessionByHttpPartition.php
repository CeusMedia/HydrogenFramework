<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Net\HTTP\PartitionSession as HttpPartitionSession;

trait SessionByHttpPartition
{
	/**	@var	HttpPartitionSession	$session	Session Object */
	private HttpPartitionSession $session;

	/**
	 *	Returns Session Object.
	 *	@access		public
	 *	@return		HttpPartitionSession
	 */
	public function getSession(): HttpPartitionSession
	{
		return $this->session;
	}

	/**
	 *	@param		?string		$keyPartitionName
	 *	@param		?string		$keySessionName
	 *	@return		static
	 */
	protected function initSession( string $keyPartitionName = NULL, string $keySessionName = NULL ): static
	{
		$partitionName	= md5( (string) getCwd() );
		$sessionName	= 'sid';
		if( $keyPartitionName && $this->config->get( $keyPartitionName ) )
			$partitionName	= $this->config->get( $keyPartitionName );
		if( $keySessionName && $this->config->get( $keySessionName ) )
			$sessionName	= $this->config->get( $keySessionName );

		$this->session	= new HttpPartitionSession(
			$partitionName,
			$sessionName
		);
		$this->runtime->reach( 'env: session: construction' );

		// @todo check if this old workaround public URL paths extended by module is still needed and remove
		$isInside	= (int) $this->session->get( 'auth_user_id' );
		$inside		= explode( ',', $this->config->get( 'module.acl.inside', '' ) );		//  get current inside link list
		$outside	= explode( ',', $this->config->get( 'module.acl.outside', '' ) );		//  get current outside link list
		foreach( $this->modules->getAll() as $module ){
			foreach( $module->links as $link ){														//  iterate module links
				$link->path	= $link->path ?: 'index/index';
				if( $link->access == "inside" ){													//  link is inside public
					$path	= str_replace( '/', '_', $link->path );					//  get link path
					if( !in_array( $path, $inside ) )												//  link is not in public link list
						$inside[]	= $path;														//  add link to public link list
				}
				if( $link->access == "outside" ){													//  link is outside public
					$path	= str_replace( '/', '_', $link->path );					//  get link path
					if( !in_array( $path, $inside ) )												//  link is not in public link list
						$outside[]	= $path;														//  add link to public link list
				}
			}
		}
		$this->config->set( 'module.acl.inside', implode( ',', array_unique( $inside ) ) );	//  save public link list
		$this->config->set( 'module.acl.outside', implode( ',', array_unique( $outside ) ) );	//  save public link list
		$this->modules->callHook( 'Session', 'init', $this->session );
		$this->runtime->reach( 'env: session: init done' );
		return $this;
	}
}