<?php
/**
 *	This class must be extended.
 *	@category	cmTools
 *	@package	DocCreator_Test
 *	@abstract
 *	@name		Test_Abstract
 *	@see		http://google.de
 *	@see		http://yahoo.de
 *	@link		http://google.de
 *	@link		http://yahoo.de
 *	@copyright	2009 me
 *	@license	GPL
 *	@author		me <me@me.de>
 *	@author		you <you@me.de>
 *	@since		yesterday
 *	@version	$id: $
 *	@todo		nothing
 *	@todo		really!
 *	@deprecated	not really
 *	@deprecated	it is necessary
 */
abstract class Test_Abstract
{
	/**
	 *	This method must be implemented.
	 *	@abstract
	 *	@static
	 *	@access		public
	 *	@name		mustBeImplemented
	 *	@param		mixed		$firstParameter		Something, but optional
	 *	@return		mixed		Something else
	 *	@throws		RuntimeException		if something is wrong
	 *	@see		http://google.de
	 *	@see		http://yahoo.de
	 *	@link		http://google.de
	 *	@link		http://yahoo.de
	 *	@copyright	2009 me
	 *	@license	GPL
	 *	@author		me <me@me.de>
	 *	@author		you <you@me.de>
	 *	@since		yesterday
	 *	@version	$id: $
	 *	@todo		nothing
	 *	@todo		really!
	 *	@deprecated	not really
	 *	@deprecated	it is necessary
	 */
	abstract public static function mustBeImplemented( $firstParameter = NULL );
}
?>