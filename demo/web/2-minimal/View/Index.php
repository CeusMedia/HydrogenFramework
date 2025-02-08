<?php
use CeusMedia\HydrogenFramework\View;

class View_Index extends View
{
	public function index(): string
	{
		extract( $this->getData() );
		$payload	= ['number' => 0];
		$this->env->getCaptain()->callHookWithPayload( 'Custom', 'random', $this, $payload );

		return join( '<br/>', [
			'View says: Hello World! The microtime is: '.$microtime,
			'Custom hook rolled random number '.$payload['number'],
		] );
	}
}
