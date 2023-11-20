<?php
return;
use CeusMedia\Common\Loader;

$pathLib	= realpath( dirname( __FILE__, 2 ) );
$pathSrc	= realpath( $pathLib . '/src' );
$pathTest	= realpath( $pathLib . '/test' );

require_once $pathSrc . '/FS/Autoloader/Psr0.php';

/*$loaderTest	= new \CeusMedia\Common\FS\Autoloader\Psr0;
$loaderTest->setIncludePath( $pathLib );
$loaderTest->register();*/

$loaderSrc	= new \CeusMedia\Common\FS\Autoloader\Psr0;
$loaderSrc->setIncludePath( $pathSrc );
$loaderSrc->register();

$__config	= parse_ini_file( $pathLib.'/Common.ini', TRUE );
//print_m( $__config );die;

$loaderTest	= new Loader();													//  get new Loader Instance
$loaderTest->setExtensions( 'php' );											//  set allowed Extension
$loaderTest->setPath( dirname( __FILE__ ).DIRECTORY_SEPARATOR );				//  set fixed Library Path
$loaderTest->setVerbose( 0 );												//  show autoload attempts
$loaderTest->setPrefix( 'Test_' );												//  set prefix class prefix
$loaderTest->registerAutoloader();												//  apply this autoloader

//Test_Case::$config = $__config;
class_exists( 'UI_DevOutput' );
return;

