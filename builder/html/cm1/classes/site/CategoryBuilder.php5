<?php
/**
 *	Builder for Category View.
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
 *	@version		$Id: CategoryBuilder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.Builder' );
/**
 *	Builder for Category View.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@extends		Builder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: CategoryBuilder.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_CategoryBuilder extends Builder_HTML_CM1_Builder
{
	/**
	 *	Builds Class List from Package Key.
	 *	@access		private
	 *	@param		Model_Category	$category		Category Object
	 *	@return		string
	 *	@todo		fix
	 */
	private function buildClassList( Model_Category $category )
	{
		$list	= array();			
		foreach( $category->getClasses() as $class )
		{
			$type	= $this->getTypeMarkUp( $class, TRUE );
			$list[]	= UI_HTML_Elements::ListItem( $type, 0, array( 'class' => "file" ) );
		}
		if( $list )
		{
			$data	= array(
				'words'	=> $this->env->words['packageClasses'],
				'list'	=> UI_HTML_Elements::unorderedList( $list ),
			);
			return $this->loadTemplate( 'category.classes', $data );
		}
	}

	/**
	 *	Builds nested Package List from Package Key.
	 *	@access		private
	 *	@param		Model_Category	$category		Category Object
	 *	@return		string
	 */
	private function buildPackageList( Model_Category $category )
	{
		$list	= array();
		foreach( $category->getPackages() as $packageName => $package )
		{
#			$label	= $this->env->capitalizePackageName( $package->getLabel() );
			$label	= $package->getLabel();
			$url	= 'package.'.$package->getId().".html";
			$link	= UI_HTML_Elements::Link( $url, $label );
			$item	= UI_HTML_Elements::ListItem( $link, 0, array( 'class' => "package" ) );
			$list[]	= $item;
		}
		if( $list )
		{
			$packageList	= UI_HTML_Elements::unorderedList( array_values( $list ) );
			$data	= array(
				'words'	=> $this->env->words['packages'],
				'list'	=> $packageList,
			);
			return $this->loadTemplate( 'category.packages', $data );
		}
	}

	/**
	 *	Builds Package View.
	 *	@access		public
	 *	@param		Model_Category	$category		Category Object
	 *	@return		string
	 */
	public function buildView( Model_Category $category )
	{
#		$packageName	= $this->env->capitalizePackageName( $category->getLabel() );
		$packageName	= $category->getLabel();
		$classList		= $this->buildClassList( $category );
		$packageList	= $this->buildPackageList( $category );
		
		$data	= array(
			'words'			=> $this->env->words['package'],
			'icon'			=> 'images/mini/icon_component.png',
			'categoryName'	=> $packageName,
			'categoryKey'	=> "key-id:".$category->getId(),
			'packageList'	=> $packageList,
			'classList'		=> $classList,
		);
		return $this->loadTemplate( 'category.content', $data );
	}
}
?>