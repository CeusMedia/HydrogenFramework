<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Migration/Modifier.php';
require_once __DIR__.'/Migration/Applier.php';

use Tool_Migration_Modifier as Modifier;
use Tool_Migration_Applier as Applier;

$places	= array(
	'src'	=> (object) array(
		'active'	=> TRUE,
		'path'		=> 'src',
		'modifiers'	=> array(
			array( Modifier::class, 'updateLineBreak' ),
			array( Modifier::class, 'clearEndingPhpTagInLines' ),
			array( Modifier::class, 'removeIndentsInEmptyLines' ),
			array( Modifier::class, 'updateCopyrightYearInLines', '201\d', '2020' ),
			array( Modifier::class, 'clearDocVersionInLines' ),
		//	array( Modifier::class, 'breakCommentsInLines' ),
		)
	),
	'test'	=> (object) array(
		'active'	=> TRUE,
		'path'		=> 'test',
		'modifiers'	=> array(
			array( Modifier::class, 'updateLineBreak' ),
			array( Modifier::class, 'clearEndingPhpTagInLines' ),
			array( Modifier::class, 'removeIndentsInEmptyLines' ),
			array( Modifier::class, 'updateCopyrightYearInLines', '201\d', '2020' ),
			array( Modifier::class, 'clearDocVersionInLines' ),
//			array( Modifier::class, 'breakCommentsInLines' ),
			array( Modifier::class, 'updateTestSetUpAndTearDown' ),
		)
	),
	'demo'	=> (object) array(
		'active'	=> TRUE,
		'path'		=> 'demo',
		'modifiers'	=> array(
			array( Modifier::class, 'updateLineBreak' ),
			array( Modifier::class, 'clearEndingPhpTagInLines' ),
			array( Modifier::class, 'removeIndentsInEmptyLines' ),
			array( Modifier::class, 'updateCopyrightYearInLines', '201\d', '2020' ),
			array( Modifier::class, 'clearDocVersionInLines' ),
//			array( Modifier::class, 'breakCommentsInLines' ),
		)
	),
);

new UI_DevOutput();
$cliColor	= new CLI_Color();

foreach( $places as $placeKey => $placeData ){
	if( empty( $placeData->active ) )
		continue;
	CLI::out();
	CLI::out( $cliColor->asInfo( 'Place: '.$placeKey ) );
	$folder		= new FS_Folder( realpath( __DIR__.'/../'.$placeData->path ) );
	$applier	= new Applier();
	$applier->setRootFolder( $folder );
	$applier->setModifiers( $placeData->modifiers );
	$stats		= $applier->apply();
	CLI::out( 'Files changed: '.$stats->nrFilesChanged.' of '.$stats->nrFiles );
}

CLI::out();
CLI::out();
