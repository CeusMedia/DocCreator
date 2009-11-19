<?php
/**
 *	Builder for Package View.
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
 *	@version		$Id: PackageBuilder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.Abstract' );
/**
 *	Builder for Package View.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@extends		Builder_HTML_CM1_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: PackageBuilder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_Package extends Builder_HTML_CM1_Abstract
{
	/**
	 *	Builds Class List from Package Key.
	 *	@access		private
	 *	@param		ADT_PHP_Package	$package		Package Object
	 *	@return		string
	 *	@todo		fix
	 */
	private function buildClassList( ADT_PHP_Package $package )
	{
		$list	= array();			
		foreach( $package->getClasses() as $class )
		{
			$type	= $this->getTypeMarkUp( $class, TRUE );
			$list[]	= UI_HTML_Elements::ListItem( $type, 0, array( 'class' => "class" ) );
		}
		if( $list )
		{
			$data	= array(
				'words'	=> $this->env->words['packageClasses'],
				'list'	=> UI_HTML_Elements::unorderedList( $list ),
			);
			return $this->loadTemplate( 'package.classes', $data );
		}
	}

	/**
	 *	Builds Interface List from Package Key.
	 *	@access		private
	 *	@param		ADT_PHP_Package	$package		Package Object
	 *	@return		string
	 *	@todo		fix
	 */
	private function buildInterfaceList( ADT_PHP_Package $package )
	{
		$list	= array();			
		foreach( $package->getInterfaces() as $interface )
		{
			$type	= $this->getTypeMarkUp( $interface, TRUE );
			$list[]	= UI_HTML_Elements::ListItem( $type, 0, array( 'class' => "interface" ) );
		}
		if( $list )
		{
			$data	= array(
				'words'	=> $this->env->words['packageInterfaces'],
				'list'	=> UI_HTML_Elements::unorderedList( $list ),
			);
			return $this->loadTemplate( 'package.interfaces', $data );
		}
	}

	/**
	 *	Builds nested Package List from Package Key.
	 *	@access		private
	 *	@param		ADT_PHP_Package	$superPackage	Package Object
	 *	@return		string
	 */
	private function buildPackageList( ADT_PHP_Package $superPackage )
	{
		$list	= array();
		foreach( $superPackage->getPackages() as $packageName => $package )
		{
#			$label	= $this->env->capitalizePackageName( $package->getLabel() );
			$type	= $this->getTypeMarkUp( $package, TRUE );
			$item	= UI_HTML_Elements::ListItem( $type, 0, array( 'class' => "package" ) );
			$list[]	= $item;
		}
		if( $list )
		{
			$packageList	= UI_HTML_Elements::unorderedList( array_values( $list ) );
			$data	= array(
				'words'	=> $this->env->words['packages'],
				'list'	=> $packageList,
			);
			return $this->loadTemplate( 'package.packages', $data );
		}
	}

	/**
	 *	Builds Package View.
	 *	@access		public
	 *	@param		ADT_PHP_Package	$package		Package Object
	 *	@return		string
	 */
	public function buildView( ADT_PHP_Package $package )
	{
#		$packageName	= $this->env->capitalizePackageName( $package->getLabel() );
		$packageName	= $package->getLabel();
		$classList		= $this->buildClassList( $package );
		$interfaceList	= $this->buildInterfaceList( $package );
		$packageList	= $this->buildPackageList( $package );
		
		$data	= array(
			'words'			=> $this->env->words['package'],
			'icon'			=> 'images/mini/icon_package.png',
			'packageName'	=> $packageName,
			'packageKey'	=> "key-id:".$package->getId(),
			'packageList'	=> $packageList,
			'classList'		=> $classList,
			'interfaceList'	=> $interfaceList,
			'footer'		=> $this->buildFooter(),
		);
		return $this->loadTemplate( 'package.content', $data );
	}
}
?>