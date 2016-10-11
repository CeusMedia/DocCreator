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
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Primitives.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Reader\Plugin;
/**
 *	...
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@extends		DocCreator_Reader_Plugin_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Primitives.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class Primitives extends \CeusMedia\DocCreator\Reader\Plugin\Abstraction{

	protected $phpTypeCompat	= array(
		'int'		=> 'integer',
		'bool'		=> 'boolean',
		'double'	=> 'float',
		'real'		=> 'float',
		'...'		=> 'dotdotdot',
		'Object'	=> 'object',
	);

	/**
	 *	...
	 *	@access		public
	 *	@param		ADT_PHP_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	public function extendData( \ADT_PHP_Container $data ){
		foreach( $data->getFiles() as $fileName => $file ){
			foreach( $file->getClasses() as $class ){
				if( $class instanceof \ADT_PHP_Class )
					foreach( $class->getMembers() as $member )
						$this->tryToExtendPrimitiveType( $member );
				foreach( $class->getMethods() as $method ){
					if( $method->getReturn() )
						$this->tryToExtendPrimitiveType( $method->getReturn() );
					foreach( $method->getParameters() as $parameter ){
						$this->tryToExtendPrimitiveType( $parameter );
						$this->tryToExtendPrimitiveCast( $parameter );
					}
				}
			}
		}
	}

	protected function tryToExtendPrimitiveCast( $data ){
		$type	= $data->getCast();
		if( is_string( $type ) && !empty( $type ) )
			if( array_key_exists( $type, $this->phpTypeCompat ) )
				$data->setCast( $this->phpTypeCompat[$type] );
	}

	protected function tryToExtendPrimitiveType( $data ){
		$type	= $data->getType();
		if( is_string( $type ) && !empty( $type ) )
			if( array_key_exists( $type, $this->phpTypeCompat ) )
				$data->setType( $this->phpTypeCompat[$type] );
	}
}
?>
