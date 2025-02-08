<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Page as PageResource;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var PageResource $page */
/** @var string $content */
/** @var ?string $dev */

$page->addBody( $content );

//  --  add external resources
$page->addStylesheet( 'style.css' );
//$page->addJavaScript( 'javascript.js' );

//  --  gimmick: show total render time (=runtime until now in ms)
$page->addBody( '<br/>Render time: '.$env->getRuntime()->get( 3, 1 ).'ms' );

//  --  gimmick: show runtime ticks caught by profiler
$list	= [];
$timeTotal	= $env->getRuntime()->get( 6, 0 );
foreach( $env->getRuntime()->getGoals() as $nr => $tick ){
	$label	= $tick->label;
	if( !empty( $tick->description ) )
		$label	= '<abbr title="'.$tick->description.'">'.$label.'</abbr>';
	$list[] = '<tr>'.join( [
		sprintf( '<td class="right">%s</td>', ++$nr ),
		sprintf( '<td>%s</td>', $label ),
		sprintf( '<td class="right">%s</td>', round( $tick->timeMicro / $timeTotal * 100 ).'%' ),
		sprintf( '<td class="right">%s</td>', $tick->timeMicro ),
		sprintf( '<td class="right">%s</td>', $tick->totalMicro ),
	] ).'</tr>';
}
$ticks	= '<h3>Ticks</h3><div id="ticksContainer"><table id="ticksTable">'.join( $list ).'</table></div>';
$page->addBody( $ticks );

$page->addBody( $dev );

$page->setTitle( $env->getConfig()->get( 'app.title' ) );
$env->getResponse()->addHeaderPair( 'X-Hydrogen-Process-Time', $env->getRuntime()->get( 3, 0 ).'ms' );

return $page->build();
