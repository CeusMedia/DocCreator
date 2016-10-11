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

$pathSelf			= __DIR__ . '/';
$pathCmCommon		= "";
$pathMarkdown		= "";


if (file_exists('vendor/autoload.php')) {
	require_once 'vendor/autoload.php';
} else {
	if (!($pathCmCommon && file_exists($pathCmCommon . '/autoload.php'))) {
		die('Please install CeusMedia/Common first and set path in $pathCmCommon!' . PHP_EOL);
	}
	require_once $pathCmCommon . '/autoload.php';
	if ($pathMarkdown && file_exists($pathMarkdown)) {
		CMC_Loader::registerNew('php', 'Michelf\\', $pathMarkdown . 'Michelf/');
	}
	CMC_Loader::registerNew('php5', 'DocCreator_', $pathSelf . '/classes/');			//  enable class auto loading for DocCreator
}

/*
$pathLib			= __DIR__."/lib/";
$pathCmClasses		= file_exists( $pathLib ) ? $pathLib."cmClasses/" : "cmClasses/trunk/";
$pathPhpMarkdown	= file_exists( $pathLib ) ? $pathLib."php-markdown/" : "/var/www/lib/php-markdown/";

@require_once $pathCmClasses.'autoload.php5';
if( !class_exists( 'CMC_Loader' ) ) die( "Please install cmClasses first!");
*/

new \CeusMedia\DocCreator\Core\ConsoleRunner();									//  open new starter
?>
