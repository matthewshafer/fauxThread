<?php

require_once('../src/fauxThreadRunner.php');

class counter implements fauxThreadRunner
{
	private $start;
	private $max;
	
	public function __construct($start = 0, $max = 20)
	{
		$this->start = $start;
		$this->max = $max;
	}
	
	public function run()
	{
		while($this->start < $this->max)
		{
			echo $this->start . "\n";
			$this->start++;
			sleep(1);
		}
		
		exit(0);
	}
	
	
}

?>