<?php

//  --  PLEASE SET UP YOUR ENVIRONMENT  --  //
$pathCmLib	= "cmClasses/trunk/";											//  path to cmClasses, absolute or relative
$pathTool	= "";															//  path to DocCreator, absolute, relative or empty
$configFile	= dirname( __FILE__ )."/config/doc.zend.ini";
$configFile	= dirname( __FILE__ )."/config/doc.ini";


//  --  PLEASE DON'T CHANGE BELOW IF NOT NECESSARY  --  //
if( $pathCmLib )															//  path to cmClasses is set
	require_once( $pathCmLib."useClasses.php5" );							//  load library importer
require_once( $pathTool."classes/DocCreatorStarter.php5" );					//  load starter of DocCreator
$starter	= new DocCreatorStarter;										//  open new starter
$starter->setProjectConfigFile( $configFile );								//  set project's config file for DocCreator
$starter->start();															//  run starter
?>