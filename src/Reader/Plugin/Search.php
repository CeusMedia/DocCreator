<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
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
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Reader\Plugin;

use CeusMedia\Common\Alg\Text\Unicoder as TextUnicoder;
use CeusMedia\Common\Alg\Text\TermExtractor as TermExtractor;
use CeusMedia\Common\Alg\Time\Clock as Clock;
use CeusMedia\PhpParser\Structure\Class_ as PhpClass;
use CeusMedia\PhpParser\Structure\Container_ as PhpContainer;

/**
 *	...
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Search extends Abstraction
{
	/**
	 *	Extracts Terms from Descriptions for Search Index.
	 *	@access		public
	 *	@param		PhpContainer	$data		Object containing collected Class Data
	 *	@return		void
	 *	@todo		support Interfaces
	 */
	public function extendData( PhpContainer $data ): void
	{
		$clock2	= new Clock();												//  start inner Clock
		if( $this->verbose )
			$this->env->out->append( " => Extracting Search Terms ..." );
		foreach( $data->getFiles() as $fileName => $file ){
			foreach( $file->getClasses() as $classId => $class ){
				$facts	= [];
				$facts['className']	= $class->getName();
				$facts['classDesc']	= $class->getDescription();
#				foreach( $class->getAuthors() as $author )
#					$facts['authors'][]			= $author->getName();
				foreach( $class->getTodos() as $todo )
					$facts['todos'][]			= $todo;
				foreach( $class->getDeprecations() as $deprecation )
					$facts['deprecations'][]	= $deprecation;
				if( $class instanceof PhpClass )
					foreach( $class->getMembers() as $member )
						$facts['members'][$member->getName()]	= $member->getDescription();
				foreach( $class->getMethods() as $method ){
					$facts['methods'][$method->getName()]	= $method->getDescription();
					foreach( $method->getTodos() as $todo )
						$facts['todos'][]			= $todo;
					foreach( $method->getDeprecations() as $deprecation )
						$facts['deprecations'][]	= $deprecation;
				}
				$document	= $this->getFactsDocument( $facts );
				if( !TextUnicoder::isUnicode( $document ) )
					$document	= TextUnicoder::convertToUnicode( $document );

				$terms		= TermExtractor::getTerms( $document );
				$data->getFile( $fileName )->getClass( $class->getName() )->search	= [
					'document'	=> $document,
					'terms'		=> $terms,
					'facts'		=> $facts,
				];
			}
		}
		$data->timeTerms	= $clock2->stop( 6, 0 );								//  note needed time
	}

	protected function getFactsDocument( array $facts ): string
	{
		$document	= [];
		foreach( $facts as $fact ){
			if( is_string( $fact ) && trim( $fact ) )
				$document[]	= $fact;
			else if( is_array( $fact ) ){
				foreach( $fact as $factKey => $factValue )
					if( $factValue )
						if( is_string( $factKey ) )
							$document[]	= $factKey . ' ' . $factValue;
						else
							$document[]	= $factValue;
			}
		}
		return implode( "\n", $document );
	}

	protected function setUp(): void
	{
		$termsBlacklist	= array( 'for', 'and', 'with', 'of', 'if', 'else', 'returns', 'method', 'function', 'functions', 'methods', 'method' );
		TermExtractor::setBlacklist( $termsBlacklist );
	}
}
