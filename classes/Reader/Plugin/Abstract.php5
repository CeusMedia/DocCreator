<?php
/**
 *	Abstract Reader Plugin.
 *
 *	Copyright (c) 2008 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Abstract.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
/**
 *	Abstract Reader Plugin.
 *	@category		cmTools
 *	@package		DocCreator_Reader_Plugin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Abstract.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
abstract class DocCreator_Reader_Plugin_Abstract{

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		ArrayObject		$config		Configuration Array Object 
	 *	@return		void
	 */
	public function __construct( $env, $verbose ){
		$this->env	= $env;
		$this->verbose	= $verbose;
		$this->setUp();
	}

	abstract public function extendData( ADT_PHP_Container $data );

	protected function setUp(){}
}
?>
