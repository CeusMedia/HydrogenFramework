<?php
class App extends CMF_Hydrogen_Application_Console
{
	public function run()
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$captain	= $this->env->getCaptain();

		$session->set( 'timeOnAppRun', microtime( TRUE ) );

		$commands	= $request->get( 'commands' );
		switch( current( $commands ) ){
			default:
				$controller	= new Controller_CLI_Demo( $this->env );
				$controller->run();
		}

		$payload	= (object) array( 'key' => 'value' );
		$captain->callHook( 'Demo', 'demonstrateHook', $this, $payload );
		CLI::out();
		CLI::out( 'Hook Payload:      '.json_encode( $payload ) );
		CLI::out( 'Session Data:      '.json_encode( $session->getAll() ) );
		CLI::out();
	}
}
?>
