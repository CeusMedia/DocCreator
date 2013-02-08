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
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Tree.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
import( 'de.ceus-media.folder.Iterator' );
import( 'de.ceus-media.adt.tree.menu.Item' );
import( 'de.ceus-media.ui.html.tree.Menu' );
/**
 *	Builds for Index Tree for Classes or Files.
 *	@category		cmTools
 *	@extends		Builder_HTML_CM1_Abstract
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@uses			Folder_Iterator
 *	@uses			ADT_Tree_Menu_Item
 *	@uses			UI_HTML_Tree_Menu
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Tree.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_Tree extends Builder_HTML_CM1_Abstract
{
	/**
	 *	Builds Tree View.
	 *	@access		public
	 *	@param		string		$projectId		Project ID (used as Cookie Name)
	 *	@return		string
	 *	@todo		rename to buildView
	 */
	public function buildTree()
	{
		if( $this->env->verbose )
			$this->env->out->sameLine( "Creating tree" );

		$tree		= $this->env->tree;
		if( count( $tree->getPackages() ) == 1 ){
			$packages	= $tree->getPackages();
			$tree	= array_pop( $packages );
		}

		$menu		= new ADT_Tree_Menu_List();
		$menu->setAttribute( 'class', NULL );
		$menu->setAttribute( 'id', 'tree' );
		
		$this->convertTreeToTreeMenuRecursive( $tree, $menu );
		$builder	= new UI_HTML_Tree_Menu();
		$builder->setTarget( 'content' );
		$tree		= $builder->buildMenuFromMenuList( $menu );
		$uiData	= array(
			'cookieId'	=> "doc_tree",#$this->env->config['project.name'].'_tree',
			'words'		=> $this->env->words['tree'],
			'tree'		=> $tree,
		);
		return $this->loadTemplate( "site.tree", $uiData );
	}

	protected function convertTreeToTreeMenuRecursive( &$root, &$menu )
	{
		$list	= array();
		foreach( $root->getPackages() as $package )
		{
			if( get_class( $package ) == 'ADT_PHP_Category' )
			{
				$prefix	= 'category.';
				$class	= 'category';
			}
			else if( get_class( $package ) == 'ADT_PHP_Package' )
			{
				$prefix	= 'package.';
				$class	= 'package';
			}
			$uri	= $prefix.$package->getId().".html";
			$label	= $this->env->capitalizePackageLabel( $package->getLabel() );
			$item	= new ADT_Tree_Menu_Item( $uri, $label );
			$item->setAttribute( 'class', $class );
			$this->convertTreeToTreeMenuRecursive( $package, $item );
			$list[$label]	= $item;
		}
		ksort( $list );
		foreach( $list as $item )
			$menu->addChild( $item );

		$list	= array();
		foreach( $root->getClasses() as $class )
		{
			$parts	= explode( "_", $class->getName() );
			$name	= array_pop( $parts );
			$item	= new ADT_Tree_Menu_Item( 'class.'.$class->getId().'.html', $name );
			$item->setAttribute( 'class', 'class' );
			$menu->addChild( $item );
		}
		ksort( $list );
		foreach( $list as $item )
			$menu->addChild( $item );

		$list	= array();
		foreach( $root->getInterfaces() as $interface )
		{
			$parts	= explode( "_", $interface->getName() );
			$name	= array_pop( $parts );
			$item	= new ADT_Tree_Menu_Item( 'interface.'.$interface->getId().'.html', $name );
			$item->setAttribute( 'class', 'interface' );
			$menu->addChild( $item );
		}
		ksort( $list );
		foreach( $list as $item )
			$menu->addChild( $item );
	}
}
?>