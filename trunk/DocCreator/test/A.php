<?php
/**
 *	@category		cmTools
 *	@package		DocCreator_Test_A
 */
require_once 'interface/Documentable.php';
/**
 *	@category		cmTools
 *	@package		DocCreator_Test_A
 *	@extends		ArrayObject
 *	@implements		Test_Interface_Documentable
 */
class Test_A extends ArrayObject implements Interface_Documentable
{
	/** @var	int		$a		Integer 1 */
	public $a	= 1;
	
	/**
	 *	@param		ArrayObject		$a		This is A!
	 *	@param		int				$b		Should be integer.
	 *	@param		bool			$c		Should be boolean.
	 *	@return		void
	 */
	public function __construct( ArrayObject $a, $b, $c = NULL )
	{
		$sum	 = 1 + 2;
		/**
		 * @trigger onDisplayObjectPropertyValue Triggered if an object value of some 
		 * property is returned. Plugins can attach to this trigger in order to modify 
		 * the value that gets displayed.
		 * Event payload: value, property, title and link
		 */
		$sum	 = 1 + 2;
	}
	
	/**
	 *	Combines all arguments with a dot.
	 *	@access		public
	 *	@param		...				$...	Several primitive arguments
	 *	@return		string			Combined string
	 */
	public function combineAll()
	{
		/**
		 * @trigger onCallTryToDestroy This trigger is not destroying
		 * anything but it is existing just to be there.
		 * This is a test.
		 */
		return implode( "", func_get_args() );
	}
	
	public function toDoc()
	{
		return get_class( $this );
	}
}
?>