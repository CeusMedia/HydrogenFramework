<?php
if( !@include_once dirname( __DIR__ ).'/vendor/autoload.php' )
	die( 'You need to "composer install" for having full unit test support.' );

/*$vendorAutoload	= dirname( __DIR__ ).'/vendor/autoload.php';
if( !file_exists( $vendorAutoload ) || !@include_once $vendorAutoload ){
	$path = dirname(__DIR__) . '/src/';
	require_once $path . 'Address.php';
	require_once $path . 'Address/Check/Availability.php';
	require_once $path . 'Address/Check/Syntax.php';
	require_once $path . 'Address/Collection/Parser.php';
	require_once $path . 'Message.php';
	require_once $path . 'Message/Header/Field.php';
	require_once $path . 'Message/Header/Section.php';
	require_once $path . 'Message/Part.php';
	require_once $path . 'Message/Part/Attachment.php';
	require_once $path . 'Message/Part/Text.php';
	require_once $path . 'Message/Part/HTML.php';
	require_once $path . 'Message/Parser.php';
	require_once $path . 'Message/Renderer.php';
}
*/
if( !class_exists( 'PHPUnit_Framework_TestCase' ) ){
	if( class_exists( 'PHPUnit\\Framework\\TestCase' ) ){
		class PHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase{}
	}
}

require_once __DIR__ . '/TestCase.php';

//class Test_Case extends PHPUnit_Framework_TestCase{}
