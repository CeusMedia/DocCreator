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

$cmcPath	= 'cmClasses/';
$cmcPath	= __DIR__.'/../lib/cmClasses/';												//  if folder containing cmClasses is in PHP include path
$cmcPath	= '/var/www/lib/cmClasses/trunk/';
$cmcPath	= __DIR__.'/lib/cmClasses/';												//  if folder containing cmClasses is in PHP include path

require_once $cmcPath.'autoload.php5';									//  try to load cmClasses
CMC_Loader::registerNew( 'php5', 'DocCreator_', __DIR__.'/classes/' );	//  enable class auto loading for DocCreator
new DocCreator_Core_ConsoleRunner();									//  open new starter
?>
