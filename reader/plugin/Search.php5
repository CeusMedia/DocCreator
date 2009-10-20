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
 *	@version		$Id: Search.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'de.ceus-media.alg.TermExtractor' );
import( 'reader.plugin.Abstract' );
/**
 *	...
 *	@category		cmTools
 *	@package		DocCreator_Reader_Plugin
 *	@extends		Reader_Plugin_Abstract
 *	@uses			Alg_TermExtractor
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Search.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Reader_Plugin_Search extends Reader_Plugin_Abstract
{
	/**
	 *	Extracts Terms from Descriptions for Search Index.
	 *	@access		public
	 *	@param		Model_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( Model_Container $data )
	{


		$clock2	= new Alg_Time_Clock();												//  start inner Clock
		if( $this->verbose )
			remark( "Extracting Search Terms..." );
		foreach( $data->getFiles() as $fileName => $file )
		{
			$document	= array();
			if( $file->getClasses() )
			{
				$class	= array_shift( $file->getClasses() );
				$document[]	= $class->getName();
				$document[]	= $class->getDescription();
#				foreach( $class->getAuthors() as $author )
#					$document[]	= $author->getName();
				foreach( $class->getTodos() as $todo )
					$document[]	= $todo;
				foreach( $class->getDeprecations() as $deprecated )
					$document[]	= $deprecated;
				foreach( $class->getMembers() as $member )
				{
					$document[]	= $member->getName();
					$document[]	= $member->getDescription();
				}
				foreach( $class->getMethods() as $method )
				{
					$document[]	= $method->getName();
					$document[]	= $method->getDescription();
				}
			}
			foreach( $file->getFunctions() as $functionName => $function )
			{
				$document[]	= $function->getName();
				$document[]	= $function->getDescription();
			}
			foreach( $document as $line => $entry )
				if( !trim( $entry ) )
					unset( $document[$line] );

			$document	= implode( "\n", $document );
			$terms		= Alg_TermExtractor::getTerms( $document );
			$data->getFile( $fileName )->search	= array(
				'document'	=> $document,
				'terms'		=> $terms,
			);
		}
		$data->timeTerms	= $clock2->stop( 6, 0 );								//  note needed time
	}

	protected function setUp()
	{
		$termsBlacklist	= array( 'for', 'and', 'with', 'of', 'if', 'else', 'returns', 'method', 'function', 'functions', 'methods', 'method' );
		Alg_TermExtractor::setBlacklist( $termsBlacklist );
	}
}
?>