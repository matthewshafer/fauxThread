<?php
/**
 * fauxThreadRunner interface.
 * 
 * Interface that needs to be implemented to support running fauxThreads
 * @author Matthew Shafer <matt@niftystopwatch.com>
*/
interface fauxThreadRunner
{
	/**
	 * run function.
	 * 
	 * what is run in a fauxThread
	 * @access public
	 * @return void
	*/
	public function run();
}

?>