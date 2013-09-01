<?php


//http://habrahabr.ru/post/134620/
namespace Daemonize\Daemonizator;

class Daemonizator 
{
	protected $isSigterm = false;
	protected $stdout;
	protected $stderr;
	
	protected static $isSigtermS = false;

	public function __construct($stdout = '/dev/null', $stderr = '/dev/null')
	{
		$this->stdout = $stdout;
		$this->stderr = $stderr;
	}

	public function catchSigtermHandler() 
	{
		$this->isSigterm = true;
		
		echo "sigterm";
	}
	

	public static function catchSigtermHandlerS() 
	{
		//$this->isSigterm = true;
		self::$isSigtermS = true;
		echo "sigterm";
	}


	public function isSigterm() 
	{
		echo "sigterm " . $this->isSigterm;
		return $this->isSigterm;
	}

	public static function isSigtermS() 
	{
		return self::$isSigtermS;
	}

	public function daemonizeProcess($method) 
	{
		
	
		//pcntl_signal_dispatch();
		declare(ticks=1); 
		pcntl_signal(SIGTERM, array($this, "catchSigtermHandler"));
		$child_pid = pcntl_fork();
		if ($child_pid) {
			// Выходим из родительского, привязанного к консоли, процесса
			echo PHP_EOL . "#pid - $child_pid" . PHP_EOL;
			sleep(1);
			exit();
		}
		// Делаем основным процессом дочерний.
		posix_setsid();

		fclose(STDIN);
		fclose(STDOUT);
		fclose(STDERR);
		$STDIN = fopen('/dev/null', 'r');
		$STDOUT = fopen($this->stdout, 'wb');
		$STDERR = fopen($this->stderr, 'wb');
		
		$this->work();
		exit();

	}	


	public function work()
	{
		echo 'work';
		sleep(30);

	}



	public static function daemonizeProcessS($stdout = '/dev/null', $stderr = '/dev/null', $cathsigTermHandler = "Daemonize\\Daemonizator\\Daemonizator::catchSigtermHandlerS") 
	{

		pcntl_signal_dispatch();
		declare(ticks=1); 
		pcntl_signal(SIGTERM, $cathsigTermHandler);
		$child_pid = pcntl_fork();
		if ($child_pid) {
			// Выходим из родительского, привязанного к консоли, процесса
			echo PHP_EOL . "#pid - $child_pid" . PHP_EOL;
			sleep(1);
			exit();
		}
		// Делаем основным процессом дочерний.
		posix_setsid();

		ignore_user_abort(true); 
		fclose(STDIN);
		fclose(STDOUT);
		fclose(STDERR);
		$STDIN = fopen('/dev/null', 'r');
		$STDOUT = fopen('/dev/null', 'wb');
		$STDERR = fopen('/dev/null', 'wb');
		ignore_user_abort(true);
		//pcntl_signal_dispatch();
		//declare(ticks=1); 
		//pcntl_signal(SIGTERM, $cathsigTermHandler);
		return posix_getpid();

	}	


	
}
