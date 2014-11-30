<?php
/**
 *	Script to execute DocCreator Runnner on console.
 *	You can run this script from anywhere on the filesystem.
 *
 *	Usage: php /path/to/DocCreator/create.php5 --config-file=/path/to/your/doc.xml
 *
 *	Attention: You *MUST* make sure that cmClasses is loadable.
 *	Therefore the first 2 lines give to possible presettings.
 *	Please select one and remove the other.
 */
$pathLib			= __DIR__."/lib/";
$pathCmClasses		= file_exists( $pathLib ) ? $pathLib."cmClasses/" : "cmClasses/trunk/";
$pathPhpMarkdown	= file_exists( $pathLib ) ? $pathLib."php-markdown/" : "/var/www/lib/php-markdown/";

@require_once $pathCmClasses.'autoload.php5';
if( !class_exists( 'CMC_Loader' ) ) die( "Please install cmClasses first!");

CMC_Loader::registerNew( 'php', 'Michelf\\', $pathPhpMarkdown.'Michelf/' );
CMC_Loader::registerNew( 'php5', 'DocCreator_', __DIR__.'/classes/' );	//  enable class auto loading for DocCreator

new DocCreator_Core_ConsoleRunner();									//  open new starter
?>
