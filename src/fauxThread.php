<?php

/**
 * fauxThread class.
 * 
 * Handles the creating of a fauxThread
 * @author Matthew Shafer <matt@niftystopwatch.com>
*/
class fauxThread
{
	private $object;
	private $pid;
	
	/**
	 * __construct function.
	 * 
	 * Constructs for a fauxThread object
	 * @access public
	 * @param object $object
	 * @return void
	*/
	public function __construct($object)
	{
		// not sure if we need the is_object part
		if(is_object($object) && !($object instanceof fauxThreadRunner))
		{
			throw new Exception("Object must implement fauxThreadRunner");
		}
		
		$this->object = $object;
		
		// checks to see if we have pcntl_fork installed in php
		if(!$this->havePcntlFork())
		{
			throw new Exception("no pcntl fork");
		}
	}
	
	/**
	 * havePcntlFork function.
	 * 
	 * checks to see if pcntl_fork exists on the machine
	 * @access private
	 * @return boolean True if pcntl_fork exists false if it does not
	*/
	private function havePcntlFork()
	{
		$return = false;
		
		if(function_exists('pcntl_fork'))
		{
			$return = true;
		}
		
		return $return;
	}
	
	/**
	 * start function.
	 * 
	 * forks php and then runs the object that is supposed to be run in the fork
	 * @access public
	 * @return void
	*/
	public function start()
	{
		// the @ stops it from throwing an exception when unable to fork
		$pid = @pcntl_fork();
		
		if($pid === -1)
		{
			throw new Exception("Unable to fork");
		}
		
		// where all of the fun happens
		if($pid !== 0)
		{
			// we are the parent
			$this->pid = $pid;
		}
		else
		{
			// we are the child
			
			// we would call pcntl_signal here but I need to read more about it
			
			$this->object->run();
		}
	}
	
	/**
	 * getPid function.
	 * 
	 * gets the pid of the child process
	 * @access public
	 * @return int pid of child process
	*/
	public function getPid()
	{
		return $this->pid;
	}
}
?>