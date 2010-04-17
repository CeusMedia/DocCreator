<?php
/**
 *	Creates Documentation Sites from Parser Data.
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
 *	@package		DocCreator_Site
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.Abstract' );
/**
 *	Creates Documentation Sites from Parser Data.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@extends		Builder_HTML_CM1_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 732 2009-10-21 06:27:05Z christian.wuerker $
 *	@todo			Code Doc
 */
class Builder_HTML_CM1_Site_Builder extends Builder_HTML_CM1_Abstract
{
	protected function createFrameset( $pathTarget )
	{
		$data	= array(
			'language'	=> $this->env->builder->language->getValue(),
			'title'		=> $this->env->builder->title->getValue(),
		);
		$index		= $this->loadTemplate( "site/frameset", $data );
		file_put_contents( $pathTarget."index.html", $index );
	}

	protected function createHtaccess( $pathTarget )
	{
		if( $this->env->verbose )
			remark( "Creating Site: Htaccess" );
		$source		= $this->pathTheme.'templates/htaccess';
		$htaccess	= file_get_contents( $source );
		file_put_contents( $pathTarget.".htaccess", $htaccess );
	}

	/**
	 *	Creates several Sites: Index, Todos, Deprecated, ChangeLog, ReadMe and Statistics.
	 *	@access		public
	 *	@param		string			$pathProject	Path to Project Configuration
	 *	@param		string			$pathTarget		Path to save Sites in
	 *	@return		void
	 */
	public function createSites( &$linkList )
	{
#		$pathProject	= $this->env->config['project.path.source'];
		$pathTarget		= $this->env->getBuilderTargetPath();
		$this->createHtaccess( $pathTarget );
		$this->createFrameset( $pathTarget );
		
		$infoSites	= $this->env->config->getBuilderPlugins( $this->env->builder );
		foreach( $infoSites as $infoSite )
		{
			$infoSite	= trim( $infoSite );
			if( $infoSite )
			{
				$classFile	= dirname( __FILE__ )."/Info/".$infoSite.".php5";
				if( !file_exists( $classFile ) )
					throw new RuntimeException( 'Invalid info site "'.$infoSite.'"' );

				require_once( $classFile );
				$class		= 'Builder_HTML_CM1_Site_Info_'.$infoSite;
				$builder	= new $class( $this->env, $linkList );
#				$builder->setProjectPath( $pathProject );
				$builder->setTargetPath( $pathTarget );
				$builder->createSite();
			}
		}
	}
}
?>
