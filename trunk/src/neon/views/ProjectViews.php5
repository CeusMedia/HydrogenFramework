<?php
/**
 *	Views of Projects.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmClasses
 *	@package		framework.neon.views
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			26.03.2007
 *	@version		0.2
 */
import( 'de.ceus-media.framework.neon.DefinitionView' );
import( 'de.ceus-media.database.StatementBuilder' );
import( 'de.ceus-media.framework.neon.models.ProjectStatements' );
import( 'de.ceus-media.framework.neon.models.Project' );
/**
 *	Views of Projects.
 *	@category		cmClasses
 *	@package		framework.neon.views
 *	@extends		Framework_Neon_DefinitionView
 *	@uses			Database_StatementBuilder
 *	@uses			Framework_Neon_Models_ProjectStatements
 *	@uses			Framework_Neon_Models_Project
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			26.03.2007
 *	@version		0.2
 */
class Framework_Neon_Views_ProjectViews extends Framework_Neon_DefinitionView
{
	/**
	 *	Builds Author Views.
	 *	@access		public
	 *	@return		string
	 */
	public function buildContent()
	{
		$request	= $this->ref->get( 'request' );

		$projectId	= $request->get( 'projectId' );
		if( $projectId )
			return $this->buildContentEdit( $projectId );
		else if( $request->has( 'add' ) )
			return $this->buildContentAdd();
		else
			return $this->buildContentList();
	}
	
	public function buildControl()
	{
		return $this->buildContentFilter();
	}

	/**
	 *	Build View for Project Addition.
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentAdd()
	{
		$config		= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['project']['add'];

		$ui				= $this->buildForm( 'project', 'addProject', 'project', 'add' );
		$button_cancel	= $this->html->LinkButton( $request->get( 'link' ).".html", $words['button_cancel'], 'but cancel' );
		$button_add		= $this->html->Button( 'addProject', $words['button_add'], 'but add' );
		
		$ui['field_cancel']	= $this->html->Field( '', $button_cancel );
		$ui['field_button']	= $this->html->Field( '', $button_add );
		$ui['form']			= $this->html->Form( 'addProject', $request->get( 'link' ).".html;add" );
		$ui['colgroup']		= $this->html->ColumnGroup( "30%", "70%" );
		$ui['caption']		= $this->html->TableCaption( $words['caption'], 'add' );
		$ui['heading']		= $words['heading'];
		return $this->loadTemplate( 'project.add', $ui );
	}

	/**
	 *	Build View for Project Edition.
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentEdit()
	{
		$config	= $this->ref->get( 'config' );
		$request	= $this->ref->get( 'request' );
		$words	= $this->words['project']['edit'];
		if( $projectId = $request->get( 'projectId' ) )
		{
			$project	= new Framework_Neon_Models_Project( $projectId );
			$data		= $project->getData( true );

			$ui		= $this->buildForm( 'project', 'editProject', 'project', 'edit', $data );
			$button_cancel	= $this->html->LinkButton( $request->get( 'link' ).".html", $words['button_cancel'], 'but cancel' );
			$button_edit	= $this->html->Button( 'editProject',	$words['button_edit'], 'but edit' );
			$button_remove	= $this->html->Button( 'removeProject',	$words['button_remove'], 'buthot remove', $words['button_remove_confirm'] );

			$ui['field_button_cancel']	= $this->html->Field( '', $button_cancel );
			$ui['field_buttons']		= $this->html->Field( '', $button_edit.$button_remove );

			$ui['colgroup']	= $this->html->ColumnGroup( "30%", "70%" );
			$ui['form']		= $this->html->Form( 'editEntry', $request->get( 'link' ).".html;projectId,".$projectId );
			$ui['caption']	= $this->html->TableCaption( $words['caption'], 'edit' );
			$ui['heading']	= $words['heading'];
			return $this->loadTemplate( 'project.edit', $ui );
		}
		else
			$this->_messenger->noteError( $words['msg_error1'] );
	}

	/**
	 *	Build View for Project List Filter.
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentFilter()
	{
		$config		= $this->ref->get( 'config' );
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['project']['filter'];

		$project	= new Framework_Neon_Models_Project();
		$fieldList		= $project->getFields();
		$opt_order	= array( "" => $words ['order_none'] );
		foreach( $fieldList as $field )
			if( isset( $words['order_'.$field] ) )
				$opt_order[$field]	= $words['order_'.$field];
		if( $select = $session->get( 'project_order' ) )
			$opt_order['_selected']	= $select;

		$opt_direction	= array(
			"ASC"	=> $words['direction_asc'],
			"DESC"	=> $words['direction_desc']
			);
		if( $select = $session->get( 'project_direction' ) )
			$opt_direction['_selected']	= $select;
			
		$values	= array(
			'title'	=> $session->get( 'project_title' ),
			'limit'	=> $session->get( 'project_limit' ),
			);
		$sources	= array(
			'opt_order'		=> $opt_order,
			'opt_direction'	=> $opt_direction,
			);

		$ui		= $this->buildForm( 'project', 'filterProjects', 'project', 'filter', $values, $sources );

		$input_button_filter		= $this->html->Button( 'filterProjects', $words['button_filter'], 'but filter' );
		$input_button_reset			= $this->html->LinkButton( $request->get( 'link' ).".html;resetFilters", $words['button_reset'], 'but reset' );
		$ui['field_button_filter']	= $this->html->Field( 'filterProjects', $input_button_filter );
		$ui['field_button_reset']	= $this->html->Field( 'resetFilter', $input_button_reset );

		$ui['colgroup']	= $this->html->ColumnGroup( $words['colgroup'] );
		$ui['form']		= $this->html->Form( 'filterProjects', $request->get( 'link' ).".html" );
		$ui['caption']	= $this->html->TableCaption( $words['caption'], 'filter' );
		$ui['heading']	= $words['heading'];
		return $this->loadTemplate( 'project.filter', $ui );
	}
	
	/**
	 *	Build View for Project List.
	 *	@access		private
	 *	@return		string
	 */
	private function buildContentList()
	{
		$config		= $this->ref->get( 'config' );
		$dbc		= $this->ref->get( 'dbc' );
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );
		$auth		= $this->ref->get( 'auth' );
		$words		= $this->words['project']['list'];
		
		$link		= $request->get( 'link' );

		$sb	= new Database_StatementBuilder( $config['config']['table_prefix'] );
		$bs	= new Framework_Neon_Models_ProjectStatements( $sb );
		$bs->setOffset( $session->get( 'project_offset' ) );
		$bs->setLimit( $session->get( 'project_limit' ) );
		if( $session->get( 'project_order' ) )
			$bs->orderBy( $session->get( 'project_order' ), $session->get( 'project_direction' ) );
		if( $session->get( 'project_title' ) )
			$bs->withTitle( $session->get( 'project_title' ) );

		$query	= $sb->buildCountStatement();
		$data	= $dbc->execute( $query );
		$row	= $data->fetchArray();
		if( $row['rowcount'] <= $session->get( 'project_offset' ) )
		{
			$session->set( 'project_offset', 0 );
			$bs->Limit( array( $session->get( 'project_offset'), $session->get( 'project_limit' ) ) );
		}
		$query	= $sb->buildSelectStatement();
		$data	= $dbc->execute( $query );
		if( $config['debug']['show_query'] )
			remark( $query );


		$ui['paging']	= "";
		if( $data->recordCount() )
		{
			$editor	= $auth->hasRight( 'project', 'edit' );
			if( $row['rowcount'] > $session->get( 'project_limit' ) )
			{	
				$options	= array(
					'coverage'		=> $config['layout']['list_coverage'],
					'uri'			=> $link.".html",
					'key_request'	=> ';',
					'key_param'		=> ';',
					'key_assign'	=> ',',
					);
				$pages	= $this->buildPaging( $row['rowcount'], $session->get( 'project_limit' ), $session->get( 'project_offset' ), $options );
				$ui['paging']	= $this->loadTemplate( 'paging', array( "pages" => $pages ) );
			}
			$i = 0;
			while( $entry = $data->fetchNextArray( false ) )
			{
				$i++;
				$item['style']	= $i % 2 ? 'list1' : 'list2';
				$item['title']	= htmlspecialchars( $this->str_shorten( $entry['title'], $config['layout']['shorten_content'] ) );
				if( $editor )
					$item['title']		= $this->html->Link( $request->get( 'link' ).".html;projectId,".$entry['projectId'], $item['title'] );
				$item['created']	= $this->tc->convertToHuman( $entry['created'], $config['layout']['format_timestamp'] );
				$item['modified']	= $entry['modified'] ? $this->tc->convertToHuman( $entry['modified'], $config['layout']['format_timestamp'] ) : "";
				$list[]		= $this->loadTemplate( 'project.listitem', $item );
			}
			$ui['list'] = implode( "\n", $list );
		}
		else
		{
			$item	= array(
				'title'		=> $words['no_entries'],
				'created'	=> "",
				'modified'	=> "",
				'style'		=> ""
				);
			$ui['list']		= $this->loadTemplate( 'project.listitem', $item );
		}

		$ui['link_add']		= "";
		if( $auth->hasRight( 'project', 'add' ) )
			$ui['link_add']	= $this->html->Link( $request->get( 'link' ).".html;add", $words['link_add'] );
		
		$heads	= array(
			$words['head_title'],
			$words['head_created'],
			$words['head_modified'],
			);
		$ui['heads']		= $this->html->TableHeads( $heads );
		$ui['caption']		= $this->html->TableCaption( $words['caption'] );
		$ui['colgroup']		= $this->html->ColumnGroup( $words['colgroup'] );
		$ui['heading']		= $words['heading'];
		return $this->loadTemplate( 'project.list', $ui );
	}
	
	public function buildExtra()
	{
//		return $this->loadContent( 'extra_project.html' );
	
	}
}
?>