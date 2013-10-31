<?php
$pathCmClasses  = file_exists( __DIR__."/lib" ) ? __DIR__."/lib/cmClasses/" : "cmClasses/trunk/";
@require_once $pathCmClasses.'autoload.php5';
if( !class_exists( 'CMC_Loader' ) ) die( "Please install cmClasses first!");

CMC_Loader::registerNew( 'php5', 'DocCreator_', 'classes/' );
new DocCreator_Web_Application();
?>
