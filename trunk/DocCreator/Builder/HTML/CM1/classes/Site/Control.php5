<?php
/**
 *	Builds for Index Tree for Classes or Files.
 *
 *	Copyright (c) 2008-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmTools
 *	@package		DocCreator_Site
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id$
 */
import( 'builder.html.cm1.classes.Abstract' );
import( 'builder.html.cm1.classes.site.Tree' );
/**
 *	Builds for Index Tree for Classes or Files.
 *	@category		cmTools
 *	@extends		Builder_HTML_CM1_Abstract
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@uses			Builder_HTML_CM1_Site_Tree
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id$
 */
class Builder_HTML_CM1_Site_Control extends Builder_HTML_CM1_Abstract
{
	protected $linkTarget	= "content";

	private function buildLinks( $linkList )
	{
		$list	= array();
		$words	= $this->env->words['links'];
		foreach( $linkList as $link )
		{
			$label	= isset( $words[$link['key']] ) ? $words[$link['key']] : $link['key'];
			$label	.= $link['count'] > 1 ? " (".$link['count'].")" : "";
			$class	= 'info '.$link['key'];
			$link	= UI_HTML_Elements::Link( $link['url'], $label, $class, $this->linkTarget );
			$list[]	= UI_HTML_Elements::ListItem( $link );
		}

		$uiData	= array(
			'list'	=> UI_HTML_Elements::unorderedList( $list, 0, array( 'class' => "links" ) ),
			'words'	=> $words,
		);
		return $this->loadTemplate( "site/links", $uiData );
	}
	
	/**
	 *	Builds complete Control Frame View.
	 *	@access		public
	 *	@return		string
	 *	@todo		rename to buildView
	 */
	public function createControl( $linkList )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new Builder_HTML_CM1_Site_Tree( $this->env );
		$tree		= $builder->buildTree();

		$uiData	= array(
			'tree'		=> $tree,
			'links'		=> $this->buildLinks( $linkList ),
		);
		$content	= $this->loadTemplate( "site/control", $uiData );
		File_Writer::save( $pathTarget."control.html", $content );
	}
}
?>