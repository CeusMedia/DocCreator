<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds for Index Tree for Classes or Files.
 *
 *	Copyright (c) 2008-2023 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Site;

use CeusMedia\Common\ADT\Tree\Menu\Collection as TreeMenuCollection;
use CeusMedia\Common\ADT\Tree\Menu\Item as TreeMenuItem;
use CeusMedia\Common\UI\HTML\Tree\Menu as TreeMenu;
use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\PhpParser\Structure\Category_ as PhpCategory;
use CeusMedia\PhpParser\Structure\Package_ as PhpPackage;

/**
 *	Builds for Index Tree for Classes or Files.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Tree extends HtmlBuilderAbstraction
{
	protected ?string $targetFrame		= NULL;

	/**
	 *	Builds Tree View.
	 *	@access		public
	 *	@return		string
	 *	@todo		rename to buildView
	 */
	public function buildTree(): string
	{
		if( $this->env->verbose )
			$this->env->out->sameLine( "Creating tree" );

		$tree		= $this->env->tree;
		if( count( $tree->getPackages() ) == 1 ){
			$packages	= $tree->getPackages();
			$tree	= array_pop( $packages );
		}

		$menu		= new TreeMenuCollection( '', [] );
		$menu->setAttribute( 'class', NULL );
		$menu->setAttribute( 'id', 'tree' );

		$this->convertTreeToTreeMenuRecursive( $tree, $menu );
		$builder	= new TreeMenu();
		$builder->setTarget( $this->targetFrame );
		$tree		= $builder->buildMenuFromMenuList( $menu );
		$uiData	= [
			'cookieId'	=> "doc_tree",#$this->env->config['project.name'].'_tree',
			'words'		=> $this->env->words['tree'],
			'tree'		=> $tree,
		];
		return $this->loadTemplate( "site.tree", $uiData );
	}

    /**
     * @param PhpCategory $root
     * @param TreeMenuCollection $menu
     */
	protected function convertTreeToTreeMenuRecursive( PhpCategory $root, TreeMenuCollection $menu )
	{
		$list	= array();
		foreach( $root->getPackages() as $package ){
			$prefix	= '';
			$class	= '';
			if( $package instanceof PhpPackage ){
				$prefix	= 'package.';
				$class	= 'package';
			}
			else if( $package instanceof PhpCategory ){
				$prefix	= 'category.';
				$class	= 'category';
			}
			$uri	= $prefix.$package->getId().".html";
			$label	= $this->env->capitalizePackageLabel( $package->getLabel() );
			$item	= new TreeMenuItem( $uri, $label );
			$item->setAttribute( 'class', $class );
			$this->convertTreeToTreeMenuRecursive( $package, $item );
			$list[$label]	= $item;
		}
		ksort( $list );
		foreach( $list as $item )
			$menu->addChild( $item );

		$list		= array();
		foreach( $root->getClasses() as $class ){
			$parts	= explode( "_", $class->getName() );
			$name	= array_pop( $parts );
			$item	= new TreeMenuItem( 'class.'.$class->getId().'.html', $name );
			$item->setAttribute( 'class', 'class' );
			$uniqueKey	= $class->getName()."_".uniqid();
			$list[$uniqueKey]	= $item;
		}
		ksort( $list );
		foreach( $list as $item )
			$menu->addChild( $item );

		$list	= array();
		foreach( $root->getInterfaces() as $interface ){
			$parts	= explode( "_", $interface->getName() );
			$name	= array_pop( $parts );
			$item	= new TreeMenuItem( 'interface.'.$interface->getId().'.html', $name );
			$item->setAttribute( 'class', 'interface' );
			$uniqueKey	= $interface->getName()."_".uniqid();
			$list[$uniqueKey]	= $item;
		}
		ksort( $list );
		foreach( $list as $item )
			$menu->addChild( $item );
	}

	public function setTargetFrame( string $targetFrame ): self
	{
		$this->targetFrame	= $targetFrame;
		return $this;
	}
}
