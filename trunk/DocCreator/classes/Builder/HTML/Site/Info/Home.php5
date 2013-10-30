<?php
/**
 *	Builds Home Info Site File.
 *
 *	Copyright (c) 2008-2013 Christian Würker (ceusmedia.de)
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
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Home.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
/**
 *	Builds Home Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Home.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Site_Info_Home extends DocCreator_Builder_HTML_Site_Info_Abstract{

	/**
	 *	Creates Home Site from Template and Locales.
	 *	@access		public
	 *	@return		void
	 */
	public function createSite(){
		if( $this->env->verbose )
			$this->env->out->sameLine( "Creating site: Home" );
		$words		= $this->env->words['home'];
		date_default_timezone_set( $this->env->builder->timezone->getValue() );
		$data		= array(
			'title'		=> $this->env->builder->title->getValue(),
			'words'		=> $words,
			'date'		=> date( $words['formatDate'], time() ),
			'footer'	=> $this->buildFooter(),
		);
		$home	= $this->loadTemplate( 'site/home', $data );
		$this->saveFile( 'home.html', $home );
		$this->appendLink( 'home.html', 'home' );
	}
}
?>
