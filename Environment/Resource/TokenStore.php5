<?php
/**
 *	Token Store.
 *
 *	Copyright (c) 2010-2012 Christian Würker (ceus-media.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Token Store.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_TokenStore {

	protected $fileName;
	protected $map			= array();

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment Object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ) {
		$this->env	= $env;
		$this->fileName	= $this->env->getConfig()->get( 'auth.token.store' );
		$this->loadStore();
	}

	/**
	 *	Returns calculated Token.
	 *	@access		public
	 *	@return		string
	 */
	protected function calculateToken()
	{
		$config			= $this->env->getConfig();
		$credentials	= array();
		$credentials['ip']	= $this->getClientIp();													//  use remote IP as base salt
		if( $config->get( 'auth.token.cred.secret' ) )												//  use secret as encryption salt
			$credentials['secret']		= $config->get( 'auth.token.cred.secret' );
		if( $config->get( 'auth.token.cred.protocol' ) )											//  use request protocol as encryption pepper
			$credentials['protocol']	= getEnv( 'SERVER_PROTOCOL' );
		if( $config->get( 'auth.token.cred.host' ) )												//  use host name as encryption chilli
			$credentials['host']		= getEnv( 'HTTP_HOST ' );
		$seed	= implode( '|', $credentials );
		$salt	= $config->get( 'auth.token.salt' );
		return md5( $salt.$seed );
	}

	protected function getClientIp() {
		if( $this->env->getRequest()->isAjax() )
			return getEnv( 'REMOTE_ADDR' );
		if( $this->env->getRequest()->getFromSource( 'ip', 'POST' ) )
			return $this->env->getRequest()->getFromSource( 'ip', 'POST' );
		if( $this->env->getRequest()->getFromSource( 'ip', 'GET' ) )
			return $this->env->getRequest()->getFromSource( 'ip', 'GET' );
		if( getEnv( 'REMOTE_ADDR' ) == "::1" )
			return '127.0.0.1';
		return getEnv( 'REMOTE_ADDR' );
	}

	public function getToken( $credentials ) {

		$config	= $this->env->getConfig();
		$ip		= $this->getClientIp();
		if( $config->get( 'auth.token.cred.secret' ) )
			if( !$this->verifySecret( $credentials ) )
				throw new RuntimeException( 'Secret invalid' );
		$token	= $this->calculateToken();
		$this->map[$ip]	= $token;
		$this->saveStore();
		return $token;
	}

	public function hasToken() {
		$ip	= $this->getClientIp();
		return isset( $this->map[$ip] );
	}

	protected function loadStore() {
		if( !file_exists( $this->fileName ) )
			File_Writer::save( $this->fileName, serialize( array() ) );
		$this->map	= unserialize( File_Reader::load( $this->fileName ) );
	}

	protected function saveStore() {
		return File_Writer::save( $this->fileName, serialize( $this->map ) );
	}

	public function validateToken( $token ) {
		$ip	= $this->getClientIp();
		if( !isset( $this->map[$ip] ) )
			throw new InvalidArgumentException( 'No token registered for IP '.$ip );
		return $token === $this->map[$ip];
	}

	protected function verifySecret( $credentials ) {
		$secretConfig	= (string) $this->env->getConfig()->get( 'auth.token.cred.secret' );
		if( !$secretConfig )
			return TRUE;
		$secretSent		= !empty( $credentials['secret'] ) ? $credentials['secret'] : NULL;
		return $secretConfig === $secretSent;
	}
}
?>