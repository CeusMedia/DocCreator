<?php
/**
 *	Builds File Information File.
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

use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\DocCreator\Builder\HTML\File\Info as FileInfo;
use CeusMedia\DocCreator\Builder\HTML\File\Functions as FileFunctions;
use CeusMedia\DocCreator\Builder\HTML\File\SourceCode as FileSourceCode;
use CeusMedia\DocCreator\Builder\HTML\File\Index as FileIndex;
use CeusMedia\DocCreator\Core\Environment;
use CeusMedia\PhpParser\Structure\File_ as PhpFile;

/**
 *	Builds File Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Builder extends HtmlBuilderAbstraction
{
	protected FileInfo $builderFileInfo;
	protected FileFunctions $builderFileFunctions;
	protected FileSourceCode $builderSourceCode;
	protected FileIndex $builderIndex;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment Object
	 *	@return		void
	 */
	public function __construct( Environment $env ){
		parent::__construct( $env );
		$this->builderFileInfo		= new FileInfo( $env );
		$this->builderFileFunctions	= new FileFunctions( $env );
		$this->builderSourceCode	= new FileSourceCode( $env );
		$this->builderIndex			= new FileIndex( $env );
	}

	/**
	 *	Builds File View.
	 *	@access		public
	 *	@param		PhpFile		$file			File Object
	 *	@return		string
	 */
	public function buildView( PhpFile $file ): string
	{
		$data	= array(
			'fileName'		=> $file->getBasename(),
			'pathName'		=> $file->getPathname(),
			'index'			=> $this->builderIndex->buildIndex( $file ),
			'description'	=> $this->getFormatedDescription( $file->getDescription() ),
			'fileInfo'		=> $this->builderFileInfo->buildView( $file ),
			'fileFunctions'	=> $this->builderFileFunctions->buildView( $file ),
			'fileSource'	=> $this->builderSourceCode->buildSourceCode( $file ),
			'footer'		=> $this->buildFooter(),
		);
		return $this->loadTemplate( 'file.content', $data );
	}

	/**
	 *	Builds Function List of File.
	 *	@access		public
	 *	@param		PhpFile		$file			File Object
	 *	@return		array
	 */
	public function getFunctionList( PhpFile $file ): array
	{
		$list		= [];
		$functions	= $file->getFunctions();
		ksort( $functions );
		foreach( $functions as $function )
			$list[$function->getName()]	= $function->getDescription();
		return $list;
	}
}

