<?php
/**
 *	Script to execute DocCreator Runnner on console.
 */

//  --  PLEASE SET UP YOUR ENVIRONMENT  --  //
$configFile	= './config/doc.test.xml';												

require_once 'cmClasses/0.7.0/autoload.php5';										//  load library importer
CMC_Loader::registerNew( 'php5', 'DocCreator_' );
CMC_Loader::registerNew( 'php5' );

new DocCreator_Core_ConsoleRunner( $configFile );									//  open new starter
?>
