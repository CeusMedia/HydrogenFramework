<?php

$page->addBody( $content );

//  --  add external resources
$page->addStylesheet( 'style.css' );
//$page->addJavaScript( 'javascript.js' );

//  --  gimick: show runtime ticks caught by profiler
$list	= [];
$timeTotal	= $env->getRuntime()->stop( 6, 0 );
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

$page->setTitle( $env->config->get( 'app.title' ) );
$env->getResponse()->addHeaderPair( 'X-Hydrogen-Process-Time', $env->getRuntime()->stop( 3, 0 ).'ms' );

return $page->build();
