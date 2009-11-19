<?php
/**
 *	Builds History Info Site File.
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
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: History.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.site.info.Abstract' );
/**
 *	Builds History Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: History.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_Info_History extends Builder_HTML_CM1_Site_Info_Abstract
{
	protected $key			= 'history';
	protected $fileNames	= array(
		'history',
		'history.txt',
		'history.log',
		'history.nfo',
	);

	/**
	 *	Creates History Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		return parent::createSiteByFile();
	}
}
?>