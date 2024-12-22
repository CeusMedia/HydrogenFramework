<?php
return;
if( !@include_once dirname( __DIR__, 2 ) . '/vendor/autoload.php')
	die( 'You need to "composer install" for having full unit test support.' );

if( !class_exists( 'PHPUnit_Framework_TestCase' ) ){
	if( class_exists( 'PHPUnit\\Framework\\TestCase' ) ){
		class PHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase{}
	}
}

//require_once __DIR__ . '/TestCase.php';

//class Test_Case extends PHPUnit_Framework_TestCase{}
