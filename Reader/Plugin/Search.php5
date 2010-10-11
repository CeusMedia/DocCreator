<?php
/**
 *	...
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
 *	@package		DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Search.php5 731 2009-10-21 06:11:05Z christian.wuerker $
 */
/**
 *	...
 *	@category		cmTools
 *	@package		DocCreator_Reader_Plugin
 *	@extends		Reader_Plugin_Abstract
 *	@uses			Alg_Text_Unicoder
 *	@uses			Alg_Text_TermExtractor
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Search.php5 731 2009-10-21 06:11:05Z christian.wuerker $
 */
class Reader_Plugin_Search extends Reader_Plugin_Abstract
{
	/**
	 *	Extracts Terms from Descriptions for Search Index.
	 *	@access		public
	 *	@param		ADT_PHP_Container	$data		Object containing collected Class Data
	 *	@return		void
	 *	@todo		support Interfaces
	 */
	public function extendData( ADT_PHP_Container $data )
	{
		$clock2	= new Alg_Time_Clock();												//  start inner Clock
		if( $this->verbose )
			remark( "Extracting Search Terms..." );
		foreach( $data->getFiles() as $fileName => $file )
		{
			foreach( $file->getClasses() as $classId => $class )
			{
				$facts	= array();
				$facts['className']	= $class->getName();
				$facts['classDesc']	= $class->getDescription();
#				foreach( $class->getAuthors() as $author )
#					$facts['authors'][]			= $author->getName();
				foreach( $class->getTodos() as $todo )
					$facts['todos'][]			= $todo;
				foreach( $class->getDeprecations() as $deprecation )
					$facts['deprecations'][]	= $deprecation;
				if( $class instanceof ADT_PHP_Class )
					foreach( $class->getMembers() as $member )
						$facts['members'][$member->getName()]	= $member->getDescription();
				foreach( $class->getMethods() as $method )
				{
					$facts['methods'][$method->getName()]	= $method->getDescription();
					foreach( $method->getTodos() as $todo )
						$facts['todos'][]			= $todo;
					foreach( $method->getDeprecations() as $deprecation )
						$facts['deprecations'][]	= $deprecation;
				}
				$document	= $this->getFactsDocument( $facts );
				if( !Alg_StringUnicoder::isUnicode( $document ) )
					$document	= Alg_StringUnicoder::convertToUnicode( $document );

				$terms		= Alg_Text_TermExtractor::getTerms( $document );
				$data->getFile( $fileName )->getClass( $class->getName() )->search	= array(
					'document'	=> $document,
					'terms'		=> $terms,
					'facts'		=> $facts,
				);
			}
		}
		$data->timeTerms	= $clock2->stop( 6, 0 );								//  note needed time
	}

	protected function getFactsDocument( $facts )
	{
		$document	= array();
		foreach( array_values( $facts ) as $fact )
		{
			if( is_string( $fact ) && trim( $fact ) )
				$document[]	= $fact;
			else if( is_array( $fact ) )
			{
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

	protected function setUp()
	{
		$termsBlacklist	= array( 'for', 'and', 'with', 'of', 'if', 'else', 'returns', 'method', 'function', 'functions', 'methods', 'method' );
		Alg_Text_TermExtractor::setBlacklist( $termsBlacklist );
	}
}
?>