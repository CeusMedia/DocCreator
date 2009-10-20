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
 *	@version		$Id: Defaults.php5 718 2009-10-19 01:34:14Z christian.wuerker $
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
 *	@version		$Id: Defaults.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Reader_Plugin_Defaults extends Reader_Plugin_Abstract
{
	/**
	 *	...
	 *	@access		public
	 *	@param		Model_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( Model_Container $data )
	{
		$default	= $this->config['doc.package.default'];
		$default	= trim( $default ) ? trim( $default ) : 'default';

		foreach( $data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				if( !$class->getCategory() )
					$class->setCategory( 'default' );
				if( !$class->getPackage() )
					$class->setPackage( $default );
			}
		}
	}
}
?>