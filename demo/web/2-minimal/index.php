<?php
require_once '../../../src/Run/Web.php';

$appRun	= new CMF_Hydrogen_Run_Web();
$appRun->errorReporting		= E_ALL;										//  enable full reporting
$appRun->displayErrors		= TRUE;											//  enable error display
$appRun->catchErrors		= !TRUE;
//$appRun->classRouter		= 'CMF_Hydrogen_Environment_Router_Recursive';	//  set an alternative router class
//$appRun->defaultTimezone	= 'Europe/Berlin';								//  default time zone
$appRun->classFileExtension	= 'php';
$appRun->paths				= [
	'vendor'		=> '../../../vendor/',
	'config'		=> '',
	'classes'		=> '',
	'contents'		=> '',
	'locales'		=> '',
	'templates'		=> '',
];

$appRun->go();
