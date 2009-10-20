<?php
/**
 *	Starter for DocCreator.
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
 *	@package		DocCreator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: DocCreatorStarter.php5 722 2009-10-20 01:35:47Z christian.wuerker $
 */
/**
 *	Starter for DocCreator.
 *	@category		cmTools
 *	@package		DocCreator
 *	@uses			DocCreator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: DocCreatorStarter.php5 722 2009-10-20 01:35:47Z christian.wuerker $
 */
class DocCreatorStarter
{
	protected $configFile	= NULL;

	public function setProjectConfigFile( $uri )
	{
		$this->configFile	= $uri;
	}
	
	public function start()
	{
		$pathOld		= getCwd();
		$this->pathTool	= dirname( dirname( __FILE__ ) );

		chdir( $this->pathTool );
		import( 'classes.DocCreator' );
		new DocCreator( $this->configFile, TRUE );
		chdir( $pathOld );
	}
}
?>