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
 *	@version		$Id: Triggers.php5 725 2009-10-20 05:41:39Z christian.wuerker $
 */
import( 'reader.plugin.Abstract' );
/**
 *	...
 *	@category		cmTools
 *	@package		DocCreator_Reader_Plugin
 *	@extends		Reader_Plugin_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Triggers.php5 725 2009-10-20 05:41:39Z christian.wuerker $
 */
class Reader_Plugin_Triggers extends Reader_Plugin_Abstract
{
	/**
	 *	...
	 *	@access		public
	 *	@param		Model_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( Model_Container $data )
	{
		foreach( $data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				foreach( $class->getMethods() as $method )
				{
					if( $method->sourceCode )
					{
						$body	= implode( "\n", $method->sourceCode );
						if( preg_match( '/@trigger/', $body ) )
						{
							$matches	= array();
							preg_match_all( '@/\*\*.+\*/@s', $body, $matches );
							foreach( $matches as $match )
							{
								$match	= preg_replace( '@\n\s*\*\s+@Us', ' ', array_shift( $match ) );
								$parts	= array();
								preg_match_all( '/^\/\*+\s+@trigger\s+(\w+)\s+(.+)\s*\*+\/$/Us', $match, $parts );
								if( $parts[1] )
									$data->triggers[$parts[1][0]]	= $parts[2][0];
							}
						}
					}
				}
			}
		}
	}
}
?>