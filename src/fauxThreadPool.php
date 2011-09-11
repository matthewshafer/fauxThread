<?php
require_once('fauxThread.php');

class fauxThreadPool
{
	private $numThreads;
	private $currentRunningThreads;
	private $taskQueue;
	
	public function __construct($maxThreads = 2)
	{
		$this->numThreads = $maxThreads;
		$this->currentRunningThreads = 0;
		$this->taskQueue = new SplQueue();
		
		if(!$this->havePcntlFork())
		{
			throw new Exception("no pcntl fork");
		}
		
		pcntl_signal(SIGCHLD, array(&$this, 'taskComplete'));
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
	
	public function taskComplete()
	{
		echo "child exited\n";
		
		// used to wait for children to finish
		while(pcntl_waitpid(-1, $status, WNOHANG) > 0)
		{
			$this->currentRunningThreads--;
		}
		
		$this->checkQueue();
	}
	
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
					$this->currentRunningThreads++;
				}
				else
				{
					// we are the child
					
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
	
	public function addTask($object)
	{
		
		if(!($object instanceof fauxThreadRunner))
		{
			throw new Exception("not instance of fauxThreadRunner");
		}
		
		$this->taskQueue->enqueue($object);
		
		$this->checkQueue();
	}
	
	public function hasRunningTasks()
	{
		$return = true;
		
		if($this->currentRunningThreads === 0)
		{
			$return = false;
		}
		
		return $return;
	}
}
?>