<?php
/**
 *	Script to execute DocCreator Runnner on console.
 **/
//  --  PLEASE SET UP YOUR ENVIRONMENT  --  //
$pathCmLib	= "cmClasses/trunk/";													//  path to cmClasses, absolute or relative or empty if already loaded
$pathSelf	= dirname( __FILE__ ).'/';												//  path to current folder
$configFile	= $pathSelf.'config/doc.xml';

//  --  PLEASE DON'T CHANGE BELOW IF NOT NECESSARY  --  //
if( $pathCmLib )																	//  path to cmClasses is set
	require_once( $pathCmLib."useClasses.php5" );									//  load library importer
require_once( $pathSelf.'core/ConsoleRunner.php5' );								//  load starter of DocCreator
$starter	= new DocCreator_Core_ConsoleRunner( $configFile );						//  open new starter
?>