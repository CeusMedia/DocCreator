<?php
/**
 *	Script to execute DocCreator Runnner on console.
 **/

#CMC_Loader::registerNew( 'php5' );

//  --  PLEASE SET UP YOUR ENVIRONMENT  --  //
$pathCmLib	= "cmClasses/0.7.0/";													//  path to cmClasses, absolute or relative or empty if already loaded
$pathSelf	= dirname( __FILE__ ).'/';												//  path to current folder
$configFile	= $pathSelf.'config/doc.xml';

//  --  PLEASE DON'T CHANGE BELOW IF NOT NECESSARY  --  //
if( $pathCmLib )																	//  path to cmClasses is set
	require_once( $pathCmLib.'autoload.php5' );									//  load library importer
require_once( $pathSelf.'Core/ConsoleRunner.php5' );								//  load starter of DocCreator
$starter	= new DocCreator_Core_ConsoleRunner( $configFile );						//  open new starter
?>
