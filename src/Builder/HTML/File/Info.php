<?php
/**
 *	Builder for File Info View.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\File;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\PhpParser\Structure\File_ as PhpFile;
use CeusMedia\PhpParser\Structure\Function_ as PhpFunction;

/**
 *	Builder for File Info View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
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
			'copyright'		=> $this->buildParamStringList( $file->getCopyrights(), 'copyright' ),		//  copyright notes
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
}

