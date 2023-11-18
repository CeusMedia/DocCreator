<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Source Code View.
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

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\PhpParser\Structure\File_ as ParsedFile;
use CeusMedia\PhpParser\Structure\Function_ as ParsedFunction;

/**
 *	Builds Source Code View.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class SourceCode extends HtmlBuilderAbstraction
{
	/**
	 *	Builds Source Code View.
	 *	@access		public
	 *	@param		ParsedFile	$file			File Object
	 *	@param		bool		$isClass		Flag: build Source Code View of a Class
	 *	@return		string
	 */
	public function buildSourceCode( ParsedFile $file, bool $isClass = FALSE ): string
	{
		$options	= new Dictionary( $this->env->getBuilderOptions() );
		if( !$options->get( 'showSourceCode' ) )
			return '';
		$code		= $file->getSourceCode();
		$data	= array(
			'words'	=> $this->env->words['source'],
			'code'	=> htmlspecialchars( $code ),
		);
		return $this->loadTemplate( "file.source.list", $data);

		$regExHide	= array(
			"@^/\*.*\*/$@",
			"@^//@",
			"@^<\?(php)?$@",
			"@^(php)?\?>$@",
			"@import\s*\(@",
			"@^\*@",
			"@^\s*#@",
		);
		$number		= 0;
		$list		= [];
		$docOpen	= FALSE;
		$lines		= explode( "\n", $file->getSourceCode() );
		$lastEmpty	= FALSE;
		while( $lines ){
			$hide	= FALSE;
			$line	= array_shift( $lines );
			$number++;
			if( !trim( $line ) ){
				if( $lastEmpty )
					$hide		= TRUE;
				$lastEmpty	= TRUE;
			}
			else{
				$lastEmpty	= FALSE;
				if( !$docOpen && preg_match( "@^/\*\*?$@", trim( $line ) ) ){
					$docOpen	= TRUE;
					$hide		= TRUE;
				}
				else{
					if( preg_match( "@^\*?\*/$@", trim( $line ) ) ){
						$docOpen	= FALSE;
						$hide		= TRUE;
					}
					else{
						foreach( $regExHide as $regEx )
							if( preg_match( $regEx, trim( $line ) ) )
								$hide		= TRUE;
					}
				}
			}
			if( !$lines && $lastEmpty )
				$hide		= TRUE;
			$indent		= str_repeat( "&nbsp;", 4 );
			$line		= htmlspecialchars( $line );
#			$line		= str_replace( ' ', '&nbsp;', $line );
			$line		= str_replace( "\t", $indent, $line );
			if( $isClass )
				$line	= preg_replace( "@^(.+function\s+)(\w+)\(@", '\\1<a name="source_class_method_\\2" href="#class_method_\\2">\\2</a>(', $line );
			$line		= preg_replace( "@^(function\s+)(\w+)\(@", '\\1<a name="source_file_function_\\2" href="#file_function_\\2">\\2</a>(', $line );
			$classes	= [];
			$line		= preg_replace( '@^(\n\r?| *)$@', "&nbsp;", $line );
			if( $hide )
				$classes[]	= "source-line-unimportant source-line-hidden";
			$classes[]	= $number % 2 ? "type1" : "type2";
			$line		= str_replace( array( '<%', '%>' ), array( '[%', '%]' ), $line );

			$data	= array(
				'class'		=> implode( " ", $classes ),
				'number'	=> $number,
				'line'		=> $line,
			);
			$list[]		= $this->loadTemplate( 'file.source.item', $data );
		}
		$data	= array(
			'words'	=> $this->env->words['source'],
			'list'	=> implode( "\n", $list ),
		);
		return $this->loadTemplate( "file.source.list", $data);
	}
}
