<?php
ini_set('display_errors', 'on');
#require_once '../../../src/Run/Web.php';

$pathVendor	= dirname( __DIR__, 3 ).'/vendor/';
( include_once $pathVendor.'autoload.php' ) or die( 'Install packages using composer, first!'.PHP_EOL );

use CeusMedia\HydrogenFramework\Run\Web as WebRunner;

$appRun	= new WebRunner();
$appRun->errorReporting		= E_ALL;										//  enable full reporting
$appRun->displayErrors		= TRUE;											//  enable error display
$appRun->catchErrors		= !TRUE;
//$appRun->classRouter		= 'CMF_Hydrogen_Environment_Router_Recursive';	//  set an alternative router class
//$appRun->defaultTimezone	= 'Europe/Berlin';								//  default time zone
$appRun->classFileExtension	= 'php';
$appRun->paths				= [
	'vendor'		=> $pathVendor,
	'config'		=> '',
	'classes'		=> '',
	'contents'		=> '',
	'locales'		=> '',
	'templates'		=> '',
];

$appRun->go();
