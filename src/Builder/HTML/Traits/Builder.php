<?php
/**
 *	Builds Trait Information File.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Traits
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Traits;

use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\DocCreator\Builder\HTML\File\Index as FileIndex;
use CeusMedia\DocCreator\Builder\HTML\File\Info as FileInfo;
use CeusMedia\DocCreator\Builder\HTML\File\Functions as FileFunctions;
use CeusMedia\DocCreator\Builder\HTML\File\SourceCode as FileSourceCode;
use CeusMedia\DocCreator\Builder\HTML\Traits\Info as TraitInfo;
use CeusMedia\DocCreator\Builder\HTML\Traits\Members as TraitMembers;
use CeusMedia\DocCreator\Builder\HTML\Traits\Methods as TraitMethods;
use CeusMedia\DocCreator\Core\Environment;
use CeusMedia\PhpParser\Structure\File_ as PhpFile;
use CeusMedia\PhpParser\Structure\Trait_ as PhpTrait;

/**
 *	Builds Trait Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Traits
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Builder extends HtmlBuilderAbstraction
{
	protected FileInfo $builderFile;
	protected FileFunctions $builderFunctions;
	protected TraitInfo $builderInfo;
	protected TraitMembers $builderMembers;
	protected TraitMethods $builderMethods;
	protected FileSourceCode $builderSourceCode;
	protected FileIndex $builderIndex;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment	$env	Environment Object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		parent::__construct( $env );
		$this->builderFile			= new FileInfo( $env );
		$this->builderFunctions		= new FileFunctions( $env );
		$this->builderInfo			= new TraitInfo( $env );
		$this->builderMembers		= new TraitMembers( $env );
		$this->builderMethods		= new TraitMethods( $env );
		$this->builderSourceCode	= new FileSourceCode( $env );
		$this->builderIndex			= new FileIndex( $env );
	}

	/**
	 *	Builds entire Class Information File.
	 *	@access		public
	 *	@param		PhpFile			$file			File Object
	 *	@param		PhpTrait		$trait			...
	 *	@return		string
	 */
	public function buildView( PhpFile $file, PhpTrait $trait ): string
	{
		$data	= [
			'index'				=> $this->builderIndex->buildIndex( $file ),
			'traitName'			=> $trait->getName(),
			'fileName'			=> $file->getBasename(),
			'pathName'			=> $file->getPathname(),
			'fileInfo'			=> $this->builderFile->buildView( $file ),
			'traitInfo'			=> $this->builderInfo->buildView( $trait ),
			'traitMembers'		=> $this->builderMembers->buildView( $trait ),
			'traitMethods'		=> $this->builderMethods->buildView( $trait ),
			'fileFunctions'		=> $this->builderFunctions->buildView( $file ),
			'fileSource'		=> $this->builderSourceCode->buildSourceCode( $file, TRUE ),
			'footer'			=> $this->buildFooter(),
		];
		return $this->loadTemplate( 'trait.content', $data );
	}
}
