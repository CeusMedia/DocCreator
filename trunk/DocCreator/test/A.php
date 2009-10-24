<?php
/**
 *	@category		cmTools
 *	@package		DocCreator_Test_A
 */
/**
 *	@extends		ArrayObject
 *	@implements		Countable
 */
class Test_A extends ArrayObject implements Documentable
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
		return implode( "", func_get_args() );
	}
}
?>