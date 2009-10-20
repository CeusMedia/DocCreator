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
 *	@version		$Id: Builder.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 */
/*
import( 'builder.html.cm1.classes.site.info.HomeBuilder' );
import( 'builder.html.cm1.classes.site.info.TodoBuilder' );
import( 'builder.html.cm1.classes.site.info.DeprecationBuilder' );
import( 'builder.html.cm1.classes.site.info.AboutBuilder' );
import( 'builder.html.cm1.classes.site.info.BugBuilder' );
import( 'builder.html.cm1.classes.site.info.ClassListBuilder' );
import( 'builder.html.cm1.classes.site.info.ChangeLogBuilder' );
import( 'builder.html.cm1.classes.site.info.HistoryBuilder' );
import( 'builder.html.cm1.classes.site.info.InstallationBuilder' );
import( 'builder.html.cm1.classes.site.info.ParseErrorBuilder' );
import( 'builder.html.cm1.classes.site.info.EncodingErrorBuilder' );
import( 'builder.html.cm1.classes.site.info.StatisticsBuilder' );
import( 'builder.html.cm1.classes.site.info.SearchBuilder' );
import( 'builder.html.cm1.classes.site.info.TriggersBuilder' );
*/
/**
 *	Creates Documentation Sites from Parser Data.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site
 *	@uses			Builder_HTML_CM1_Site_Info_HomeBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_TodoBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_DeprecationBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_AboutBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_BugBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_ClassListBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_ChangeLogBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_HistoryBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_InstallationBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_ParseErrorBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_EncodingErrorBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_StatisticsBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_SearchBuilder
 *	@uses			Builder_HTML_CM1_Site_Info_TriggersBuilder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Builder.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 *	@todo			Code Doc
 */
class Builder_HTML_CM1_Site_Builder extends Builder_HTML_CM1_Builder
{

	/**
	 *	Creates several Sites: Index, Todos, Deprecated, ChangeLog, ReadMe and Statistics.
	 *	@access		public
	 *	@param		string			$pathProject	Path to Project Configuration
	 *	@param		string			$pathTarget		Path to save Sites in
	 *	@return		void
	 */
	public function createSites( &$linkList )
	{
		$pathProject	= $this->env->config['project.path.source'];
		$pathTarget		= $this->env->config['doc.path'];
		$this->createHtaccess( $pathTarget );
		$this->createFrameset( $pathTarget );
		
		$infoSites	= $this->env->config['project.builder.site.plugins'];
		$infoSites	= explode( ',', $infoSites );
		
		foreach( $infoSites as $infoSite )
		{
			$infoSite	= trim( $infoSite );
			if( $infoSite )
			{
				$classFile	= dirname( __FILE__ )."/info/".$infoSite.".php5";
				if( !file_exists( $classFile ) )
					throw new RuntimeException( 'Invalid info site "'.$infoSite.'"' );

				require_once( $classFile );
				$class		= 'Builder_HTML_CM1_Site_Info_'.$infoSite;
				$builder	= new $class( $this->env, $linkList );
				$builder->setProjectPath( $pathProject );
				$builder->setTargetPath( $pathTarget );
				$builder->createSite();
			}
		}
	}

	protected function createFrameset( $pathTarget )
	{
		$data	= array(
			'config'	=> $this->env->config,
			'language'	=> $this->env->config['doc.language'],
			'title'		=> $this->env->config['doc.title'],
		);
		$index		= $this->env->loadTemplate( "site/frameset", $data );
		file_put_contents( $pathTarget."index.html", $index );
	}

	protected function createHtaccess( $pathTarget )
	{
		if( $this->env->verbose )
			remark( "Creating Site: Htaccess" );
		$htaccess	= file_get_contents( $this->env->getBuilderPath().'templates/htaccess' );
		file_put_contents( $pathTarget.".htaccess", $htaccess );
	}
}
?>