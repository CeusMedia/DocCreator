<?php
/**
 *	Builds Bug Info Site File.
 *
 *	Copyright (c) 2008-2020 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Bugs.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;
/**
 *	Builds Bug Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Bugs.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class Bugs extends \CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction
{
	protected $key			= 'bugs';
	protected $fileNames	= array(
		'bugs',
		'bugs.txt',
		'bugs.log',
		'bugs.nfo',
		'knownbugs',
		'knownbugs.txt',
		'knownbugs.log',
		'knownbugs.nfo',
		'known-bugs',
		'known-bugs.txt',
		'known-bugs.log',
		'known-bugs.nfo',
		'known_bugs',
		'known_bugs.txt',
		'known_bugs.log',
		'known_bugs.nfo',
		'issues',
		'issues.txt',
		'issues.log',
		'issues.nfo',
	);

	/**
	 *	Creates Bug Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		parent::createSiteByFile();
	}
}
?>
