<?php
/**
 *	Builder for File Function View.
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
 *	@package		DocCreator_Builder_HTML_CM1_File
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: FunctionsBuilder.php5 740 2009-10-24 00:04:50Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.file.Info' );
/**
 *	Builder for File Function View.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_File
 *	@extends		Builder_HTML_CM1_File_Info
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: FunctionsBuilder.php5 740 2009-10-24 00:04:50Z christian.wuerker $
 */
class Builder_HTML_CM1_File_Functions extends Builder_HTML_CM1_File_Info
{
	/**
	 *	Builds View of a Function with all Information.
	 *	@access		private
	 *	@param		ADT_PHP_File		$file			File Object
	 *	@param		ADT_PHP_Function	$function		Data of Function
	 *	@return		string
	 */
	private function buildFunctionEntry( ADT_PHP_File $file, ADT_PHP_Function $function )
	{
		$attributes	= array();

		$attributes['name']			= $this->buildParamStringList( $function->getName(), 'name' );
		$attributes['description']	= $this->buildParamStringList( $function->getDescription(), 'description' );

		$attributes['version']		= $this->buildParamStringList( $function->getVersion(), 'version' );
		$attributes['since']		= $this->buildParamStringList( $function->getSince(), 'since' );
		$attributes['copyright']	= $this->buildParamStringList( $function->getCopyright(), 'copyright' );
		$attributes['deprecated']	= $this->buildParamStringList( $function->getDeprecations(), 'deprecated' );
		$attributes['todo']			= $this->buildParamStringList( $function->getTodos(), 'todo' );

		$attributes['author']		= $this->buildParamAuthors( $function );
		$attributes['link']			= $this->buildParamLinkedList( $function->getLinks(), 'link' );
		$attributes['see']			= $this->buildParamLinkedList( $function->getSees(), 'see' );
		$attributes['license']		= $this->buildParamLicenses( $function );

		$attributes['return']		= $this->buildParamReturn( $function );
		$attributes['throws']		= $this->buildParamThrows( $function );

		$params	= array();
		foreach( $function->getParameters() as $parameter )
		{
			$signature	= $this->getParameterMarkUp( $parameter );
			$text		= $parameter->getDescription() ? '&nbsp;&minus;&nbsp;'.$parameter->getDescription() : "";
			$params[]	= $signature.$text;
		}
		$params	= implode( "<br/>", $params );	
		$attributes['param']	= $this->buildParamList( $params, 'param' );

		$attributes		= $this->loadTemplate( 'file.function.attributes', $attributes );


		$return			= $this->getTypeMarkUp( $function->getReturn() ? $function->getReturn()->getType() : "void" );
		$functionName	= UI_HTML_Elements::Link( "#source_file_function_".$function->getName(), $function->getName() );

		$params	= array();
		foreach( $function->getParameters() as $parameter )
			$params[]	= $this->getParameterMarkUp( $parameter );
		$params	= implode( ", ", $params );	
		if( $params	)
			$params	= " ".$params." ";
		
		$data	= array(
			'functionName'	=> $function->getName(),
			'functionTitle'	=> $functionName,
			'return'		=> $return,
			'attributes'	=> $attributes,
			'parameters'	=> $params,
			'description'	=> nl2br( trim( $function->getDescription() ) ),
		);
		return $this->loadTemplate( 'file.function', $data );
	}

	/**
	 *	Builds View of File Functions for File Information File.
	 *	@access		public
	 *	@param		ADT_PHP_File		$file			File Object
	 *	@return		string
	 */
	public function buildView( ADT_PHP_File $file )
	{
		$this->type	= "file";
		$list		= array();
		$functions	= $file->getFunctions();
		if( !$functions )
			return "";
		ksort( $functions );
		foreach( $functions as $functionName => $function )
			$list[$functionName]	= $this->buildFunctionEntry( $file, $function );

		$words		= $this->env->words['fileFunctions'];
		$heading	= sprintf( $words['heading'], $file->getBasename() );
		$data	= array(
			'words'		=> $words,
			'heading'	=> $heading,
			'list'		=> implode( "", $list ),
		);
		return $this->loadTemplate( 'file.functions', $data );
	}
}
?>