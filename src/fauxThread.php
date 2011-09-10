<?php

class fauxThread
{
	private $object;
	private $pid;
	
	public function __construct($object)
	{
		// not sure if we need the is_object part
		if(is_object($object) && !($object instanceof fauxThreadRunner))
		{
			throw new Exception("Object must implement fauxThreadRunner");
		}
		
		$this->object = $object;
		
		if(!$this->havePcntlFork())
		{
			throw new Exception("no pcntl fork");
		}
	}
	
	private function havePcntlFork()
	{
		$return = false;
		
		if(function_exists('pcntl_fork'))
		{
			$return = true;
		}
		
		return $return;
	}
	
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
	
	public function getPid()
	{
		return $this->pid;
	}
}
?>