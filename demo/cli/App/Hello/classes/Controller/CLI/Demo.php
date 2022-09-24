<?php

use CeusMedia\Common\CLI;
use CeusMedia\Common\CLI\Color as ConsoleColor;
use CeusMedia\HydrogenFramework\Controller\Console as ConsoleController;

class Controller_CLI_Demo extends ConsoleController
{
	public function run()
	{
		$color		= new ConsoleColor();
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$language	= $this->env->getLanguage();
		$modules	= $this->env->getModules();

		$wordsMain	= (object) $language->getWords( 'main' )['main'];

		$session->set( 'someThingSetByController', substr( md5( uniqid() ), rand( 0, 20 ), 6 ) );

		CLI::out( $color->asSuccess( 'Hello World!' ) );
		CLI::out();
		CLI::out( 'Application Title: '.$wordsMain->title );
		CLI::out();
		CLI::out( 'Commands given:    '.json_encode( $request->get( 'commands' ) ) );
		CLI::out( 'Parameters given:  '.json_encode( $request->get( 'parameters' ) ) );
		CLI::out();
		CLI::out( 'PHP Version:       '.$this->env->getPhp()->getCurrentVersion() );
		CLI::out( 'Framework Version: '.$this->env->version );
		CLI::out();
		CLI::out( 'Environment Class: '.get_class( $this->env ) );
		CLI::out( 'Environment URI:   '.$this->env->uri );
		CLI::out( 'Modules Path:      '.$modules->getPath() );
		CLI::out( 'Modules Installed: '.join( ', ', array_keys( $modules->getAll() ) ) );
		CLI::out();

	}
}
