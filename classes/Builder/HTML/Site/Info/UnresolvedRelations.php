<?php
/**
 *	...
 *
 *	Copyright (c) 2008-2015 Christian Würker (ceusmedia.de)
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
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: UnresolvedRelations.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
/**
 *	Builds Deprecation Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@uses			Alg_UnusedVariableFinder
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: UnresolvedRelations.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Site_Info_UnresolvedRelations extends DocCreator_Builder_HTML_Site_Info_Abstract
{
	protected $count		= 0;

	protected function checkUnresolvedAndAddListItemIfNot( &$list, $relation, $prefix = NULL )
	{
		if( !is_string( $relation ) )
			return;
		$this->count++;
		$list[]	= UI_HTML_Elements::ListItem( $prefix.$relation, 1 );
	}

	/**
	 *	Creates Deprecation Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		$content	= "";
		$classList	= array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$list	= array();
				$this->checkUnresolvedAndAddListItemIfNot( $list, $class->getExtendedClass(), 'extends: ' );
				foreach( $class->getExtendingClasses() as $extendingClass )
					$this->checkUnresolvedAndAddListItemIfNot( $list, $extendingClass, 'extendedBy: ' );
				if( $list )
				{
					$url	= 'class.'.$class->getId().'.html';
					$link	= UI_HTML_Elements::Link( $url, $class->getName(), 'class' );
					$list	= UI_HTML_Elements::unorderedList( $list, 1 );
					$classList[]	= UI_HTML_Elements::ListItem( $link.$list, 0, array( 'class' => 'class' ) );
				}
			}
			foreach( $file->getInterfaces() as $interface )
			{
				$list	= array();
				foreach( $interface->getExtendingInterfaces() as $extendingInterface )
					$this->checkUnresolvedAndAddListItemIfNot( $list, $extendingInterface, 'extendedBy: ' );
				foreach( $interface->getImplementingClasses() as $implementingClass )
					$this->checkUnresolvedAndAddListItemIfNot( $list, $implementingClass, 'implementedBy: ' );
				if( $list )
				{
					$url	= 'interface.'.$interface->getId().'.html';
					$link	= UI_HTML_Elements::Link( $url, $interface->getName(), 'interface' );
					$list	= UI_HTML_Elements::unorderedList( $list, 1 );
					$classList[]	= UI_HTML_Elements::ListItem( $link.$list, 0, array( 'class' => 'interface' ) );
				}
			}
		}
		if( $this->count )
		{
			$this->verboseCreation( 'unresolvedRelations' );

			$words	= isset( $this->env->words['unresolvedRelations'] ) ? $this->env->words['unresolvedRelations'] : array();
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> 'unresolvedRelations',
				'id'		=> 'info-unresolvedRelations',
				'topic'		=> isset( $words['heading'] ) ? $words['heading'] : 'unresolvedRelations',
				'content'	=> '<div id="tree">'.UI_HTML_Elements::unorderedList( $classList ).'</div>',
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/unresolvedRelations';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "unresolvedRelations.html", $content );
			$this->appendLink( 'unresolvedRelations.html', 'unresolvedRelations', $this->count );
		}
		return (bool) $this->count;
	}
}
?>
