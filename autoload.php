<?php
return;
//  --  CeusMedia:Common is required  --
if( !class_exists( 'Loader' ) )															//  CeusMedia:Common is not loaded
	throw new RuntimeException( 'Please install and load "CeusMedia/Common" first' );	//  exit with exception
if( !defined( 'CMF_PATH' ) )															//  path to cmFrameworks is not defined
	define( 'CMF_PATH', __DIR__.'/' );													//  assume own path
$folder	= basename( __DIR__ );															//  ...

if( !defined( 'CMF_VERSION' ) )															//  ...
	define( 'CMF_VERSION', 'dev-master' );

//  --  cmFrameworks AutoLoader  --
$__loaderFrameworks	= new Loader;														//  get new Loader Instance
$__loaderFrameworks->setExtensions( 'php' );											//  set Class File Extension
$__loaderFrameworks->setPrefix( 'CMF_Hydrogen_' );										//  set Class Name Prefix
$__loaderFrameworks->setPath( CMF_PATH.'src/' );										//  set Class Path
$__loaderFrameworks->setVerbose( FALSE );												//  set autoloader verbosity
$__loaderFrameworks->registerAutoloader();
?>
