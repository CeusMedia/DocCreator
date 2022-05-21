<?php
/**
 *	Abstract Reader Plugin.
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

use ArrayObject;

/**
 *	Abstract Reader Plugin.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
abstract class Abstraction
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		ArrayObject		$config		Configuration Array Object
	 *	@return		void
	 */
	public function __construct( $env, bool $verbose )
	{
		$this->env		= $env;
		$this->verbose	= $verbose;
		$this->setUp();
	}

	abstract public function extendData( PhpContainer $data );

	protected function setUp(){}
}
?>
