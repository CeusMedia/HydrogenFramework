<?php
/**
 *	Project Actions.
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
 *	@package		framework.neon.actions
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			26.03.2007
 *	@version		0.2
 */
import( 'de.ceus-media.framework.neon.DefinitionAction' );
import( 'de.ceus-media.framework.neon.models.Project' );
/**
 *	Project Actions.
 *	@category		cmClasses
 *	@package		framework.neon.actions
 *	@extends		Framework_Neon_DefinitionAction
 *	@uses			Framework_Neon_Models_Project
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@since			26.03.2007
 *	@version		0.2
 */
class Framework_Neon_Actions_ProjectActions extends Framework_Neon_DefinitionAction
{
	/**
	 *	Executes called Actions.
	 *	@access		public
	 *	@return		void
	 */
	public function act()
	{
		$config		= $this->ref->get( 'config' );
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );

		$this->loadLanguage( 'project' );

		$projectId	= $request->get( 'projectId' );
		if( $projectId )
		{
			if( $request->get( 'removeProject' ) )
				$this->removeProject();
			else if( $request->get( 'editProject' ) )
				$this->editProject();
		}
		else
		{
			if( $request->get( 'addProject' ) )
				$this->addProject();
		}
		if( $request->has( 'resetFilters' ) )
			$this->resetFilters();
		$this->filterProjects();
	}

	/**
	 *	Adds a new Project.
	 *	@access		private
	 *	@return		void
	 */
	private function addProject()
	{
		$request	= $this->ref->get( 'request' );
		$words		= $this->words['project']['msg'];

		if( $this->validateForm( 'project', 'addProject', 'project', 'add' ) )
		{
			$data	= $request->getAll();
			$data['created']	= time();

			$project	= new Project();
			$project->focusForeign( "title", $data['title'] );
			$list	= $project->getData();
			if( count( $list ) )
				return $this->messenger->noteError( $words['error_duplicate'], $data['title'] );

			$projectId	= $project->addData( $data );
			$this->messenger->noteSuccess( $words['success_added'], $data['title'] );
			$this->restart( $request->get( 'link' ).".html" );
		}
	}

	/**
	 *	Edits an existing Project.
	 *	@access		private
	 *	@return		void
	 */
	private function editProject()
	{
		$request	=& $this->ref->get( 'request' );
		$words		= $this->words['project']['msg'];

		if( $projectId = $request->get( 'projectId' ) )
		{
			if( $this->validateForm( 'project', 'editProject', 'project', 'edit' ) )
			{
				$data		= $request->getAll();
				$project	= new Project();
				$projects	= $project->getAllData(
					array(),
					array(
						'title'	=> $data['title'],
						'projectId' => "!=".$projectId )
					);
				if( count( $projects ) )
					return $this->messenger->noteError( $words['error_duplicate'], $data['title'] );
				$data['modified']	= time();
				$project	= new Project( $projectId );
				$project->modifyData( $data );
				$this->messenger->noteSuccess( $words['success_edited'], $data['title'] );
				$this->restart( $request->get( 'link' ).".html" );
			}
		}
		else
			$this->messenger->noteError( $words['error1'] );
	}

	/**
	 *	Removes an existing Project.
	 *	@access		private
	 *	@return		void
	 */
	private function removeProject()
	{
		$request	=& $this->ref->get( 'request' );
		$words	= $this->words['project']['msg'];
		if( $projectId = $request->get( 'projectId' ) )
		{
			$project	= new Project( $projectId );
			$data		= $project->getData( true );
			$project->deleteData();
			$this->messenger->noteSuccess( $words['success_removed'], $data['title'] );
			$this->restart( $request->get( 'link' ).".html" );
		}
	}
	
	/**
	 *	Sets current Filters in Session.
	 *	@access		private
	 *	@return		void
	 */
	private function filterProjects()
	{
		$config		=& $this->ref->get( 'config' );
		$session	=& $this->ref->get( 'session' );
		$request	=& $this->ref->get( 'request' );
		if( $request->get( 'filterProjects' ) )
		{
			$session->set( 'project_title',		$request->get( 'filter_title' ) );
			$session->set( 'project_order',		$request->get( 'filter_order' ) );
			$session->set( 'project_direction', $request->get( 'filter_direction' ) );
			$session->set( 'project_limit',		$request->get( 'filter_limit' ) );
			$session->set( 'project_offset',	0 );
		}
		else
		{
			if( NULL !== ( $offset = $request->get( 'offset' ) ) )
				$session->set( 'project_offset', $offset );
			if( !$session->get( 'project_limit' ) )
				$session->set( 'project_limit', $config['layout']['list_limit'] );
		}
		if( !$session->get( 'project_limit' ) )
			$session->set( 'project_limit', $config['layout']['list_limit'] );
		if( $session->get( 'project_offset' ) < 0 )
			$session->set( 'project_offset', 0 );
	}

	/**
	 *	Resets Filters in Session and Request.
	 *	@access		private
	 *	@return		void
	 */
	private function resetFilters()
	{
		$session	= $this->ref->get( 'session' );
		$request	= $this->ref->get( 'request' );
		$session->remove( 'project_title' );
		$session->remove( 'project_order' );
		$session->remove( 'project_direction' );
		$session->remove( 'project_limit' );
		$session->remove( 'project_offset' );
		$request->remove( 'filter_title' );
		$request->remove( 'filter_order' );
		$request->remove( 'filter_direction' );
		$request->remove( 'filter_limit' );
		$request->remove( 'filter_offset' );
	}
}
?>