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
 *	@version		$Id: ControlBuilder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.site.TreeBuilder' );
/**
 *	Builds for Index Tree for Classes or Files.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@uses			Builder_HTML_CM1_Site_TreeBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: ControlBuilder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_ControlBuilder
{
	private	$hasAbout			= FALSE;
	private	$hasChangeLog		= FALSE;
	private	$hasHistory			= FALSE;
	private	$hasInstallation	= FALSE;
	private $hasEncodingErrors	= FALSE;
	private	$hasParseErrors		= FALSE;
	private	$hasReadMe			= FALSE;
	private $numberDeprecated	= 0;
	private $numberTodos		= 0;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment Object
	 *	@return		void
	 */
	public function __construct( $env )
	{
		$this->env			=& $env;
		$this->linkTarget	= "content";
	}
	
	/**
	 *	Builds Tree View.
	 *	@access		public
	 *	@return		string
	 *	@todo		rename to buildView
	 */
	public function createControl( $linkList )
	{
		$pathTarget	= $this->env->config['doc.path'];
		$builder	= new Builder_HTML_CM1_Site_TreeBuilder( $this->env );
		$tree		= $builder->buildTree();

		$uiData	= array(
			'tree'		=> $tree,
			'links'		=> $this->buildLinks( $linkList ),
		);
		$content	= $this->env->loadTemplate( "site/control", $uiData );
		File_Writer::save( $pathTarget."control.html", $content );
	}

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
		return $this->env->loadTemplate( "site/links", $uiData );
	}
}
?>