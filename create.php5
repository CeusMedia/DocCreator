<?php
/**
 *	Script to execute DocCreator Runnner on console.
 **/

//  --  PLEASE SET UP YOUR ENVIRONMENT  --  //
$pathCmLib	= "cmClasses/trunk/";													//  path to cmClasses, absolute or relative or empty if already loaded
$configFile	= dirname( __FILE__ ).'/config/doc.test.xml';

//  --  PLEASE DON'T CHANGE BELOW IF NOT NECESSARY  --  //
if( $pathCmLib )																	//  path to cmClasses is set
	require_once( $pathCmLib."useClasses.php5" );									//  load library importer
require_once( dirname( __FILE__ ).'/core/ConsoleRunner.php5' );						//  load starter of DocCreator
$starter	= new DocCreator_Core_ConsoleRunner( $configFile );						//  open new starter
?>