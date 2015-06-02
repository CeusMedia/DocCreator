<?php
/**
 *	...
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
 *	@category		cmTools
 *	@package		DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Triggers.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
/**
 *	...
 *	@category		cmTools
 *	@package		DocCreator_Reader_Plugin
 *	@extends		DocCreator_Reader_Plugin_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Triggers.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class DocCreator_Reader_Plugin_Triggers extends DocCreator_Reader_Plugin_Abstract{

	/**
	 *	...
	 *	@access		public
	 *	@param		ADT_PHP_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( ADT_PHP_Container $data ){
		$data->triggers	= array();
		foreach( $data->getFiles() as $file ){
			$source	= $file->getSourceCode();
			if( !preg_match( '/@trigger\s/i', $source ) )
				continue;
			foreach( $file->getClasses() as $class ){
				foreach( $class->getMethods() as $method ){
					if( !$method->getSourceCode() )
						continue;
					$body	= implode( "\n", $method->getSourceCode() );
					if( !preg_match( '/@trigger/si', $body ) )
						continue;
					$matches	= array();
					preg_match_all( '@/\*\*.+\*/@Us', $body, $matches );
					foreach( $matches[0] as $nr => $match ){
						$match	= preg_replace( '@\n\s*\*\s+@Us', ' ', $match );
						$parts	= array();
						preg_match_all( '/^\/\*+\s+@trigger\s+(\w+)\s+(.+)?\s*\*+\/$/Us', $match, $parts );

						if( empty( $parts[1] ) )
							continue;
						if( !isset( $data->triggers[$parts[1][0]] ) )
							$data->triggers[$parts[1][0]]	= array();
						$data->triggers[$parts[1][0]][]	= array(
							'fileId'	=> $file->getId(),
							'classId'	=> $class->getId(),
							'method'	=> $method->getName(),
							'name'		=> $parts[1][0],
							'text'		=> $parts[2][0]
						);
					}
				}
			}
		}
		ksort( $data->triggers );
	}
}
?>
