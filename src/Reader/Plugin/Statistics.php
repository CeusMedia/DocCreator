<?php
/**
 *	...
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
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Reader\Plugin;

use CeusMedia\PhpParser\Structure\Container_ as PhpContainer;

use Alg_Time_Clock as Clock;
use FS_File_CodeLineCounter as CodeLineCounter;

/**
 *	...
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Statistics extends Abstraction
{
	/**
	 *	...
	 *	@access		public
	 *	@param		PhpContainer	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( PhpContainer $data )
	{
		foreach( $data->getFiles() as $file ){
			$clock				= new Clock();
			$sourceCode			= $file->getSourceCode();
			$file->statistics	= CodeLineCounter::countLinesFromSource( $sourceCode );
			unset( $file->statistics['linesCodes'] );
			unset( $file->statistics['linesStrips'] );
			unset( $file->statistics['linesDocs'] );
			$file->time['statistics']	= $clock->stop( 6, 0 );
		}
	}
}
