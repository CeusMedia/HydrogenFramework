<?php
//  --  cmClasses is required  --
if( !defined( 'CMC_LOADED' ) )															//  cmClasses is not loaded
	throw new RuntimeException( 'Please install and load "cmClasses" first' );			//  exit with exception
if( !defined( 'CMF_PATH' ) )															//  path to cmFrameworks is not defined
	define( 'CMF_PATH', dirname( __FILE__ ).'/' );										//  assume own path
$folder	= basename( dirname( __FILE__ ) );												//  ...
if( !defined( 'CMF_VERSION' ) )															//  ...
	define( 'CMF_VERSION', !preg_match( '/[a-z]/i', $folder ) ? $folder : '0.7.0' );	//  ...

//  --  cmFrameworks AutoLoader  --
$__loaderFrameworks	= new CMC_Loader;													//  get new Loader Instance
$__loaderFrameworks->setExtensions( 'php5' );											//  set Class File Extension
$__loaderFrameworks->setPrefix( 'CMF_' );												//  set Class Name Prefix
$__loaderFrameworks->setPath( CMF_PATH/*.'src/'*/ );										//  set Class Path
$__loaderFrameworks->setVerbose( FALSE );												//  set autoloader verbosity
$__loaderFrameworks->registerAutoloader();
?>
