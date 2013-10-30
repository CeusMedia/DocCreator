<?php
/**
 *	Builds Statistics Info Site File.
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
 *	@version		$Id: Statistics.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
/**
 *	Builds Statistics Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@uses			Alg_UnitFormater
 *	@uses			UI_SVG_Chart
 *	@uses			UI_SVG_ChartData
 *	@uses			Alg_Time_Clock
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Statistics.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Site_Info_Statistics extends DocCreator_Builder_HTML_Site_Info_Abstract{

	/**
	 *	Creates Statistics Info Site File.
	 *	@access		public
	 *	@return		void
	 */
	public function createSite(){
		$this->verboseCreation( 'statistics' );

		//  --  READ PROJECT  --  //
		$numberCodes	= 0;
		$numberDocs		= 0;
		$numberFiles	= 0;
		$numberLength	= 0;
		$numberLines	= 0;
		$numberStrips	= 0;

		$clock	= new Alg_Time_Clock();
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
			'number'	=> array(
				'files'		=> $numberFiles,
				'lines'		=> $numberLines,
				'codes'		=> $numberCodes,
				'docs'		=> $numberDocs,
				'strips'	=> $numberStrips,
				'length'	=> $numberLength,
			),
			'ratio'			=> array(
				'linesPerFile'		=> round( $linesPerFile, 0 ),
				'codesPerFile'		=> round( $numberCodes / $numberFiles, 0 ),
				'docsPerFile'		=> round( $numberDocs / $numberFiles, 0 ),
				'stripsPerFile'		=> round( $numberStrips / $numberFiles, 0 ),
				'codesPerFile%'		=> round( $numberCodes / $numberFiles / $linesPerFile * 100, 1 ),
				'docsPerFile%'		=> round( $numberDocs / $numberFiles / $linesPerFile * 100, 1 ),
				'stripsPerFile%'	=> round( $numberStrips / $numberFiles / $linesPerFile * 100, 1 ),
			), 
			'seconds'	=> $clock->stop( 6 ),
		);

		//  --  TOTAL TABLE  --  //
		$data['length']['total']			= Alg_UnitFormater::formatBytes( $data['number']['length'], 1 );
		$data['length']['perFile']			= Alg_UnitFormater::formatBytes( $data['number']['length'] / $data['number']['files'], 1 );
		$data['time']['stats']['total']		= Alg_UnitFormater::formatMicroSeconds( $data['seconds'] );
		$data['time']['stats']['perFile']	= Alg_UnitFormater::formatMicroSeconds( $data['seconds'] / $data['number']['files'] );
		unset( $data['files'] );

		//  --  GRAPH  --  //
		$graphData	= array(
			new UI_SVG_ChartData( $data['ratio']['codesPerFile'], "Code" ), 
			new UI_SVG_ChartData( $data['ratio']['docsPerFile'], "Docs" ),
			new UI_SVG_ChartData( $data['ratio']['stripsPerFile'], "stripped" ),
		);
		$graphFile	= "loc.svg";
		$chart		= new UI_SVG_Chart( $graphData, array( "blue", "green", "gray" ) );
		$chart->buildPieGraph( array( "x" => 50, "y" => 50, "legend" => TRUE ) );
		$chart->save( $this->pathTarget.$graphFile );

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
			$data['time']['parse']['total']		= Alg_UnitFormater::formatMicroSeconds( $parseTime );
			$data['time']['parse']['perFile']	= Alg_UnitFormater::formatMicroSeconds( $parseTime / $fileCount );
			$data['time']['build']['total']		= Alg_UnitFormater::formatMicroSeconds( $buildTime );
			$data['time']['build']['perFile']	= Alg_UnitFormater::formatMicroSeconds( $buildTime / $fileCount );
		}

		$uiData	= array(
			'data'		=> $data,
			'graphFile'	=> $graphFile,
			'words'		=> $this->env->words,
		);
		$content	= $this->loadTemplate( 'site/info/statistics', $uiData );
		$this->saveFile( 'statistics.html', $content );
		$this->appendLink( 'statistics.html', 'statistics' );
	}
}
?>
