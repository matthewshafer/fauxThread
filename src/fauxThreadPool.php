<?php

/**
 * fauxThreadPool class.
 * 
 * creates a pool of fauxThreads
 * @author Matthew Shafer <matt@niftystopwatch.com>
*/
class fauxThreadPool
{
	private $numThreads;
	private $currentRunningThreads;
	private $taskQueue;
	private $isParent = true;
	
	/**
	 * __construct function.
	 * 
	 * constructs a fauxThreadPool
	 * @access public
	 * @param int $maxThreads (default = 2)
	 * @return void
	*/
	public function __construct($maxThreads = 2)
	{
		$this->numThreads = $maxThreads;
		$this->currentRunningThreads = 0;
		$this->taskQueue = new SplQueue();
		
		if(!$this->havePcntlFork())
		{
			throw new Exception("no pcntl fork");
		}
		
		// sets up a signal to be call taskComplete when a child ends
		// should exist if pcntl_fork does
		pcntl_signal(SIGCHLD, array(&$this, 'taskComplete'));
	}
	
	/**
	 * havePcntlFork function.
	 * 
	 * Checks to see if pcntl_fork exists
	 * @access private
	 * @return boolean true of pcntl_fork exists
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
	 * taskComplete function.
	 * 
	 * Callback function when SIGCHLD is called by pcntl_signal
	 * @access public
	 * @return void
	*/
	public function taskComplete()
	{
		//echo "child exited\n";
		
		// used to wait for children to finish
		// it checks to see if a child finished
		// if it did it should return > 0
		// in this case it checks again to see if any other children quit
		while(pcntl_waitpid(-1, $status, WNOHANG) > 0)
		{
			--$this->currentRunningThreads;
		}
		
		$this->checkQueue();
	}
	
	/**
	 * checkQueue function.
	 * 
	 * Checks to see if we can spawn more processes and if so it dequeue's the next item
	 * if there are no items in the queue a RuntimeException is thrown and is caught
	 * @access private
	 * @return void
	*/
	private function checkQueue()
	{
		if($this->currentRunningThreads < $this->numThreads)
		{
			try
			{
				// need to write a fifo queue sometime because as this is now its filo
				$task = $this->taskQueue->dequeue();
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
					++$this->currentRunningThreads;
				}
				else
				{
					// we are the child
					$this->isParent = false;
					$task->run();
				}
			}
			catch(RuntimeException $e)
			{
				// this exception gets thrown when we attempt to dequeue when there is nothing
				// in the queue.  no need to do anything when we catch the exception
			}
		}
	}
	
	/**
	 * addTask function.
	 * 
	 * Adds a task to be run into the queue
	 * @access public
	 * @param object $object
	 * @return void
	*/
	public function addTask($object)
	{
		
		if(!($object instanceof fauxThreadRunner))
		{
			throw new Exception("not instance of fauxThreadRunner");
		}
		
		$this->taskQueue->enqueue($object);
		
		$this->checkQueue();
	}
	
	/**
	 * hasRunningTasks function.
	 * 
	 * Lets us know if there are tasks currently running
	 * @access public
	 * @return boolean True if there are running tasks
	*/
	public function hasRunningTasks()
	{
		$return = true;
		
		if($this->currentRunningThreads === 0)
		{
			$return = false;
		}
		
		return $return;
	}
	
	/**
	 * isParent function.
	 * 
	 * Lets us know if we are the parent or the child
	 * @access public
	 * @return boolean True if we are the parent
	*/
	public function isParent()
	{
		return $this->isParent;
	}
}
?>