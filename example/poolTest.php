<?php
//declare(ticks = 1);
require_once('../src/fauxThreadPool.php');
require_once('counter.php');


$c1 = new counter(0);
$c2 = new counter(500, 510);
$c3 = new counter(1500, 1505);

$pool = new fauxThreadPool();


$pool->addTask($c1);
$pool->addTask($c2);

try
{
	$pool->addTask(function(){echo "fail";});
}
catch(Exception $e)
{
	echo $e->getMessage() . "\n";
}

$pool->addTask($c3);

while(pcntl_signal_dispatch() && $pool->hasRunningTasks())
{
	sleep(1);
}

echo "processes finished\n";


?>