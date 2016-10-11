<?php
/**
 *	Builds Interface Information File.
 *
 *	Copyright (c) 2008-2015 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interface
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Builder\HTML\Interfaces;
/**
 *	Builds Interface Information File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Interface
 *	@extends		DocCreator_Builder_HTML_Abstract
 *	@uses			DocCreator_Builder_HTML_Interface_Info
 *	@uses			DocCreator_Builder_HTML_Interface_Methods
 *	@uses			DocCreator_Builder_HTML_File_Info
 *	@uses			DocCreator_Builder_HTML_File_Functions
 *	@uses			DocCreator_Builder_HTML_File_SourceCode
 *	@uses			DocCreator_Builder_HTML_File_Index
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class Builder extends \CeusMedia\DocCreator\Builder\HTML\Abstraction{

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Environment	$env	Environment Object
	 *	@return		void
	 */
	public function __construct( $env ){
		parent::__construct( $env );
		$this->builderFile			= new \CeusMedia\DocCreator\Builder\HTML\File\Info( $env );
		$this->builderFunctions		= new \CeusMedia\DocCreator\Builder\HTML\File\Functions( $env );
		$this->builderInterface		= new \CeusMedia\DocCreator\Builder\HTML\Interfaces\Info( $env );
		$this->builderMethods		= new \CeusMedia\DocCreator\Builder\HTML\Interfaces\Methods( $env );
		$this->builderSourceCode	= new \CeusMedia\DocCreator\Builder\HTML\File\SourceCode( $env );
		$this->builderIndex			= new \CeusMedia\DocCreator\Builder\HTML\File\Index( $env );
	}

	/**
	 *	Builds entire Interface Information File.
	 *	@access		public
	 *	@param		ADT_PHP_File		$file			File Data Object
	 *	@param		ADT_PHP_Interface	$interface		Interface Data Object
	 *	@return		string
	 */
	public function buildView( \ADT_PHP_File $file, \ADT_PHP_Interface $interface ){
		$data	= array(
			'words'				=> $this->env->words['interfaceInfo'],
			'index'				=> $this->builderIndex->buildIndex( $file ),
			'interfaceName'		=> $interface->getName(),
			'fileName'			=> $file->getBasename(),
			'pathName'			=> $file->getPathname(),
			'fileInfo'			=> $this->builderFile->buildView( $file ),
			'interfaceInfo'		=> $this->builderInterface->buildView( $interface ),
			'interfaceMethods'	=> $this->builderMethods->buildView( $interface ),
			'fileFunctions'		=> $this->builderFunctions->buildView( $file ),
			'fileSource'		=> $this->builderSourceCode->buildSourceCode( $file, TRUE ),
			'footer'			=> $this->buildFooter(),
		);
		return $this->loadTemplate( 'interface.content', $data );
	}
}
?>
