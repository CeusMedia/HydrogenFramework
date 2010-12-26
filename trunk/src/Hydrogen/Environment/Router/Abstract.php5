<?php
abstract class CMF_Hydrogen_Environment_Router_Abstract implements CMF_Hydrogen_Environment_Router_Interface
{
	protected $env;
#	public $configKeyBaseHref	= 'app.base.url';
#	public $configKeyBaseHref	= 'app.base.href';
	public $configKeyBaseHref	= 'application.base';

	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env	= $env;
		if( FALSE !== getEnv( 'REDIRECT_URL' ) )
			$this->parseFromRequest();
	}

	public function getRelativeUri( $controller = NULL, $action = NULL, $arguments = NULL, $parameters = NULL, $fragmentId = NULL )
	{
		$data	= array(
			'controller'	=> $this->env->request->get( 'controller' ),
			'action'		=> $this->env->request->get( 'action' ),
			'arguments'		=> array(),
			'parameters'	=> array(),
		);
		$uri	= '.';
		if( !is_null( $controller ) )
		{
			$uri	.= '/'.$controller;
			if( !is_null( $action ) )
				$uri	.= '/'.$action;
		}

		if( !is_null( $arguments ) && is_array( $arguments )  )
			foreach( $arguments as $key => $value )
				$uri	.= '/'.$value;
		if( !is_null( $parameters ) && is_array( $parameters ) && count( $parameters ) )
			$uri	.= '?'.http_build_query( $parameters, NULL, '&amp;' );
		if( $fragmentId )
			$uri	.= '#'.$fragmentId;
		return $uri;
	}

	public function getAbsoluteUri( $controller = NULL, $action = NULL, $arguments = NULL, $parameters = NULL, $fragmentId = NULL )
	{
		$uri	= $this->getRelativeUri( $controller, $action, $arguments, $parameters, $fragmentId );
		if( strlen( $uri ) )
		{
			if( $uri == '.' )
				$uri	= '';
			if( substr( $uri, 0, 2 ) == './' )
				$uri	= substr( $uri, 2 );
		}
		$uri	= $this->env->config->get( $this->optionKeyBaseHref ).$uri;
		return $uri;
	}

	public function realizeInResponse(){
		if( !$this->env->response )
			throw new RuntimeException( 'Route filtering needs a registered response resource' );
		$body	= $this->env->response->getBody();
		xmp( $body );
		die;
	}}
?>
