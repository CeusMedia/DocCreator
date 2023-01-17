<?php
/**
 *	Builder for Package View.
 *
 *	Copyright (c) 2008-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Builder\HTML\Site;

use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\PhpParser\Structure\Package_ as PhpPackage;

use UI_HTML_Elements as HtmlElements;

/**
 *	Builder for Package View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Package extends HtmlBuilderAbstraction
{
	/**
	 *	Builds Class List from Package Key.
	 *	@access		private
	 *	@param		PhpPackage	$package		Package Object
	 *	@return		string
	 *	@todo		fix
	 */
	private function buildClassList( PhpPackage $package ): string
	{
		$list	= array();
		foreach( $package->getClasses() as $className => $class ){
			$type	= $this->getTypeMarkUp( $class, TRUE );
			$item	= HtmlElements::ListItem( $type, 0, array( 'class' => "class" ) );
			$list[$className]	= $item;
		}
		if( 0 === count( $list ) )
			return '';
		ksort( $list );
		$data	= array(
			'words' => $this->env->words['packageClasses'],
			'list'	=> HtmlElements::unorderedList( $list ),
		);
		return $this->loadTemplate( 'package.classes', $data );
	}

	/**
	 *	Builds Interface List from Package Key.
	 *	@access		private
	 *	@param		PhpPackage	$package		Package Object
	 *	@return		string
	 *	@todo		fix
	 */
	private function buildInterfaceList( PhpPackage $package ): string
	{
		$list	= array();
		foreach( $package->getInterfaces() as $interfaceName => $interface ){
			$type	= $this->getTypeMarkUp( $interface, TRUE );
			$item	= HtmlElements::ListItem( $type, 0, array( 'class' => "interface" ) );
			$list[$interfaceName]	= $item;
		}
		if( 0 === count( $list ) )
			return '';
		ksort( $list );
		$data	= array(
			'words'	=> $this->env->words['packageInterfaces'],
			'list'	=> HtmlElements::unorderedList( $list ),
		);
		return $this->loadTemplate( 'package.interfaces', $data );
	}

	/**
	 *	Builds nested Package List from Package Key.
	 *	@access		private
	 *	@param		PhpPackage	$superPackage	Package Object
	 *	@return		string
	 */
	private function buildPackageList( PhpPackage $superPackage ): string
	{
		$list	= array();
		foreach( $superPackage->getPackages() as $packageName => $package ){
#			$label	= $this->env->capitalizePackageName( $package->getLabel() );
			$type	= $this->getTypeMarkUp( $package );
			$item	= HtmlElements::ListItem( $type, 0, array( 'class' => "package" ) );
			$list[$packageName]	= $item;
		}
		if( 0 === count( $list ) )
			return '';
		ksort( $list );
		$packageList	= HtmlElements::unorderedList( array_values( $list ) );
		$data	= [
			'words'	=> $this->env->words['packages'],
			'list'	=> $packageList,
		];
		return $this->loadTemplate( 'package.packages', $data );
	}

	/**
	 *	Builds Package View.
	 *	@access		public
	 *	@param		PhpPackage	$package		Package Object
	 *	@return		string
	 */
	public function buildView( PhpPackage $package ): string
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
			'packageKey'	=> $package->getId(),
			'packageList'	=> $packageList,
			'classList'		=> $classList,
			'interfaceList'	=> $interfaceList,
			'footer'		=> $this->buildFooter(),
		);
		return $this->loadTemplate( 'package.content', $data );
	}
}

