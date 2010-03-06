<?php
if( !defined( 'CMC_LOADED' ) )
	throw new RuntimeException( 'Please install and load "cmClasses" first' );

/*  --  cmFrameworks AutoLoader  -- */
$__loaderFrameworks	= new CMC_Loader;																//  get new Loader Instance
$__loaderFrameworks->setExtensions( 'php5' );														//  set Class File Extension
$__loaderFrameworks->setPrefix( 'Framework_' );														//  set Class Name Prefix
$__loaderFrameworks->setPath( dirname( __FILE__ ) );												//  set Class Path
$__loaderFrameworks->setLowerPath( TRUE );
$__loaderFrameworks->setVerbose( FALSE );
$__loaderFrameworks->registerAutoloader();

?>