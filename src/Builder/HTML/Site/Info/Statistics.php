<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Statistics Info Site File.
 *
 *	Copyright (c) 2008-2023 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;

use CeusMedia\Common\Alg\Time\Clock;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;

/**
 *	Builds Statistics Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Statistics extends SiteInfoAbstraction
{
	/**
	 *	Creates Statistics Info Site File.
	 *	@access		public
	 *	@return		void
	 */
	public function createSite()
	{
		$this->verboseCreation( 'statistics' );

		//  --  READ PROJECT  --  //
		$numberCodes	= 0;
		$numberDocs		= 0;
		$numberFiles	= 0;
		$numberLength	= 0;
		$numberLines	= 0;
		$numberStrips	= 0;

		$clock	= new Clock();
		foreach( $this->env->data->getFiles() as $file ){
			$stats				= $file->statistics;
			$numberFiles		++;
			$numberLength		+= $stats['length'];
			$numberLines		+= $stats['linesTotal'];
			$numberStrips		+= $stats['numberStrips'];
			$numberCodes		+= $stats['numberCodes'];
			$numberDocs			+= $stats['numberDocs'];
		}
		if( !$numberFiles )
			return;
		$linesPerFile	= $numberLines / $numberFiles;
		$data	= array(
			'number'	=> [
				'files'		=> $numberFiles,
				'lines'		=> $numberLines,
				'codes'		=> $numberCodes,
				'docs'		=> $numberDocs,
				'strips'	=> $numberStrips,
				'length'	=> $numberLength,
			],
			'ratio'			=> array(
				'linesPerFile'		=> round( $linesPerFile ),
				'codesPerFile'		=> round( $numberCodes / $numberFiles ),
				'docsPerFile'		=> round( $numberDocs / $numberFiles ),
				'stripsPerFile'		=> round( $numberStrips / $numberFiles ),
				'codesPerFile%'		=> round( $numberCodes / $numberFiles / $linesPerFile * 100, 1 ),
				'docsPerFile%'		=> round( $numberDocs / $numberFiles / $linesPerFile * 100, 1 ),
				'stripsPerFile%'	=> round( $numberStrips / $numberFiles / $linesPerFile * 100, 1 ),
			),
			'seconds'	=> $clock->stop( 6 ),
		);

		//  --  TOTAL TABLE  --  //
		$data['length']['total']			= UnitFormater::formatBytes( $data['number']['length'], 1 );
		$data['length']['perFile']			= UnitFormater::formatBytes( $data['number']['length'] / $data['number']['files'], 1 );
		$data['time']['stats']['total']		= UnitFormater::formatMicroSeconds( $data['seconds'] );
		$data['time']['stats']['perFile']	= UnitFormater::formatMicroSeconds( $data['seconds'] / $data['number']['files'] );
		unset( $data['files'] );

		//  --  GRAPH  --  //
		$graphFile	= "loc.svg";
/*
		$graphData	= array(
			new \UI_SVG_ChartData( $data['ratio']['codesPerFile'], "Code" ),
			new \UI_SVG_ChartData( $data['ratio']['docsPerFile'], "Docs" ),
			new \UI_SVG_ChartData( $data['ratio']['stripsPerFile'], "stripped" ),
		);
		$chart		= new \UI_SVG_Chart( $graphData, array( "blue", "green", "gray" ) );
		$chart->buildPieGraph( array( "x" => 50, "y" => 50, "legend" => TRUE ) );
		$chart->save( $this->pathTarget.$graphFile );
*/
		$parseTime	= 0;
		$buildTime	= 0;
		$fileCount	= 0;

		foreach( $this->env->data->getFiles() as $file ){
			if( !( isset( $file->timeParse ) && isset( $file->timeBuild ) ) )
				continue;
			$buildTime	+= (float) $file->timeBuild;
			$fileCount	++;
		}
		if( $fileCount ){
			$parseTime	= $this->env->data->timeTotal;
			$data['time']['parse']['total']		= UnitFormater::formatMicroSeconds( $parseTime );
			$data['time']['parse']['perFile']	= UnitFormater::formatMicroSeconds( $parseTime / $fileCount );
			$data['time']['build']['total']		= UnitFormater::formatMicroSeconds( $buildTime );
			$data['time']['build']['perFile']	= UnitFormater::formatMicroSeconds( $buildTime / $fileCount );
		}

		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'topic'		=> $this->env->words['statistics']['heading'],
			'data'		=> $data,
			'graphFile'	=> $graphFile,
			'words'		=> $this->env->words,
		);
		$content	= $this->loadTemplate( 'site/info/statistics', $uiData );
		$this->saveFile( 'statistics.html', $content );
		$this->appendLink( 'statistics.html', 'statistics' );
	}
}

