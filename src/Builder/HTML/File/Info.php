<?php
/**
 *	Builder for File Info View.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Builder\HTML\File;

use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\PhpParser\Structure\File_ as PhpFile;
use CeusMedia\PhpParser\Structure\Function_ as PhpFunction;

use UI_HTML_Elements as HtmlElements;

/**
 *	Builder for File Info View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Info extends HtmlBuilderAbstraction
{
	/**
	 *	Builds File Info View.
	 *	@access		public
	 *	@param		PhpFile		$file		File Object to build Info View for
	 *	@return		string
	 */
	public function buildView( PhpFile $file ): string
	{
		$this->type	= 'file';

		$package		= $this->buildPackageLink( $file->getPackage(), $file->getCategory() );
		$category		= $this->buildCategoryLink( $file->getCategory() );
		$package		= $this->getTypeMarkUp( $package );
		$category		= $this->getTypeMarkUp( $category );

		$attributesData	= array(
			'category'		=> $this->buildParamStringList( $category, 'category' ),					//  category (linked if resolvable)
			'package'		=> $this->buildParamStringList( $package, 'package' ),						//  package (linked if resolvable)
			'version'		=> $this->buildParamStringList( $file->getVersion(), 'version' ),			//  version id
			'since'			=> $this->buildParamStringList( $file->getSince(), 'since' ),				//  since version
			'copyright'		=> $this->buildParamStringList( $file->getCopyright(), 'copyright' ),		//  copyright notes
			'authors'		=> $this->buildParamAuthors( $file ),										//  authors
			'link'			=> $this->buildParamLinkedList( $file->getLinks(), 'link' ),				//  links
			'see'			=> $this->buildParamLinkedList( $file->getSees(), 'see' ),					//  see-also-references
			'license'		=> $this->buildParamLicenses( $file ),										//  license notes
			'deprecated'	=> $this->buildParamStringList( $file->getDeprecations(), 'deprecated' ),	//  deprecated
			'todo'			=> $this->buildParamStringList( $file->getTodos(), 'todo' ),				//  todos
		);
		$attributes	= max( $attributesData ) ? $this->loadTemplate( 'file.info.attributes', $attributesData ) : "";
		$uiData	= array(
			'description'	=> $this->getFormatedDescription( $file->getDescription() ),
			'attributes'	=> $attributes,
		);
		if( !max( $uiData ) )
			return "";
		$uiData['heading']	= $this->words['fileInfo']['heading'];
		return $this->loadTemplate( 'file.info', $uiData );
	}

	/**
	 *	Builds List of License Attributes.
	 *	@access		protected
	 *	@param		PhpFile|PhpFunction		$data		Array of File Data
	 *	@param		array			$list		List to fill
	 *	@return		string
	 */
	protected function buildParamLicenses( $data, array $list = array() ): string
	{
		if( !$data->getLicenses() )
			return "";
		foreach( $data->getLicenses() as $license ){
			$label	= $license->getName();
			if( $license->getUrl() ){
				$url	= $license->getUrl().'?KeepThis=true&TB_iframe=true';
				$class	= 'file-info-license';
				$label	= HtmlElements::Link( $url, $label, $class );
			}
			$list[]	= $this->loadTemplate( 'file.info.param.item', array( 'value' => $label ) );
		}
		return $this->buildParamList( $list, 'licenses' );
	}

	/**
	 *	Builds Return Description.
	 *	@access		protected
	 *	@param		PhpFunction	$data		Data object of function or method
	 *	@return		string				Return Description
	 */
	protected function buildParamReturn( PhpFunction $data ): string
	{
		if( !$data->getReturn() )
			return "";
		$type	= $data->getReturn()->getType() ? $this->getTypeMarkUp( $data->getReturn()->getType() ) : "";
		if( $data->getReturn()->getDescription() )
			$type	.= " ".$data->getReturn()->getDescription();
		return $this->buildParamList( $type, 'return' );
	}

	/**
	 *	Builds Authors Entry for Parameters List.
	 *	@access		protected
	 *	@param		PhpFunction	$data		Authors Data Array
	 *	@param		array			$list		List to fill
	 *	@return		string
	 */
	protected function buildParamThrows( PhpFunction $data, array $list = array() ): string
	{
		foreach( $data->getThrows() as $throws ){
			$type	= $throws->getName() ? $this->getTypeMarkUp( $throws->getName() ) : "";
			$type	.= $throws->getReason() ? " ".$throws->getReason() : "";
			$list[]	= $this->loadTemplate( $this->type.'.info.param.item', array( 'value' => $type ) );
		}
		return $this->buildParamList( $list, 'throws' );
	}
}

