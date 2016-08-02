<?php
class Process{
    private $pid_dir="/tmp";
    private $pid_file="";
    private $terminate=false; //是否中断
    private $daemonized=false; //是否守护进程
    private $times = 0; //子进程执行次数
    private $workers_count=0;
    private $workers_max=8; //最多运行8个进程
  
    public function __construct($is_sington=false, $user='nobody', $output="/dev/null"){
        $this->is_sington=$is_sington; //是否单例运行，单例运行会在tmp目录下建立一个唯一的PID
        $this->user=$user;//设置运行的用户 默认情况下nobody
        $this->output=$output; //设置输出的地方
        $this->checkEnv();
    }
    //检查环境是否支持pcntl支持
    public function checkEnv(){
        if (!function_exists('pcntl_signal_dispatch')) {
            // PHP < 5.3 uses ticks to handle signals instead of pcntl_signal_dispatch
            // call sighandler only every 10 ticks
            declare(ticks = 10);
        }
  
        // Make sure PHP has support for pcntl
        if (!function_exists('pcntl_signal')) {
            $message = 'PHP does not appear to be compiled with the PCNTL extension.  This is neccesary for daemonization';
            $this->_log($message);
            throw new Exception($message);
        }
        //信号处理
        pcntl_signal(SIGTERM, array(__CLASS__, "signalHandler"),false);
        pcntl_signal(SIGINT, array(__CLASS__, "signalHandler"),false);
        pcntl_signal(SIGQUIT, array(__CLASS__, "signalHandler"),false);
    }
  
    // daemon化程序
    public function daemonize(){
        global $stdin, $stdout, $stderr;
        global $argv;
  
        set_time_limit(0);
  
        // 只允许在cli下面运行
        if (php_sapi_name() != "cli"){
            die("only run in command line mode\n");
        }
  
        // 只能单例运行
        if ($this->is_sington==true){
            $this->pid_file = $this->pid_dir . "/" .__CLASS__ . "_" . substr(basename($argv[0]), 0, -4) . ".pid";
            $this->checkPidfile();
        }
  
        umask(0); //把文件掩码清0
  
        if (pcntl_fork() != 0) //是守护进程，守护进程退出
        { 
            exit();
        }
        posix_setsid();//设置新会话组长，脱离终端
        if (pcntl_fork() != 0) //是第一子进程，结束第一子进程  
        {
            exit();
        }
  
        chdir("/"); //改变工作目录
  
        $this->setUser($this->user) or die("cannot change owner");
  
        //关闭打开的文件描述符
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
  
        $stdin  = fopen($this->output, 'r');
        $stdout = fopen($this->output, 'a');
        $stderr = fopen($this->output, 'a');
  
        if ($this->is_sington==true)
        {
            $this->createPidfile();
        }
        $this->daemonized = true;
    }
    //--检测pid是否已经存在
    public function checkPidfile()
    {
        if (!file_exists($this->pid_file))
        {
            return true;
        }
        $pid = file_get_contents($this->pid_file);
        $pid = intval($pid);
        if ($pid > 0 && posix_kill($pid, 0))
        {
            $this->_log("the daemon process is already started");
        }
        else
        {
            $this->_log("the daemon proces end abnormally, please check pidfile " . $this->pid_file);
        }
        exit(1);
    }
    //--创建pid
    public function createPidfile()
    {
        if (!is_dir($this->pid_dir))
        {
            mkdir($this->pid_dir);
        }
        $fp = fopen($this->pid_file, 'w') or die("cannot create pid file");
        fwrite($fp, posix_getpid());
        fclose($fp);
        $this->_log("create pid file " . $this->pid_file);
    }
  
    //设置运行的用户
    public function setUser($name)
    {
        $result = false;
        if (empty($name))
        {
            return true;
        }
        $user = posix_getpwnam($name);
        if ($user)
        {
            $uid = $user['uid'];
            $gid = $user['gid'];
            $result = posix_setuid($uid);
            posix_setgid($gid);
        }
        return $result;
    }
    //信号处理函数
    public function signalHandler($signo)
    {
        switch($signo){
            //子进程结束信号
            case SIGCHLD:
                while(($pid=pcntl_waitpid(-1, $status, WNOHANG)) > 0)
                {
                    $this->workers_count--;
                }
                break;
            //中断进程
            case SIGTERM:
            case SIGHUP:
            case SIGQUIT:
                $this->terminate = true;
                break;
            default:
                return false;
        }
    }
    /**
    *开始开启进程
    *$count 准备开启的进程数
    */
    public function start($count=1, $argv = array())
    {
        $this->_log("parent process (PID:" . posix_getpid() . ") is running now");
        pcntl_signal(SIGCHLD, array(__CLASS__, "signalHandler"),false); // if worker die, minus children num
        while (true)
        {
            if (function_exists('pcntl_signal_dispatch'))
            {
                pcntl_signal_dispatch();
            }
  
            if ($this->terminate)
            {
                break;
            }

            $pid=-1;
            if ($this->workers_count<$count)
            {
                $pid=pcntl_fork();
            }
            
            if ($pid>0) /*parent*/
            {
                if(!$this->daemonized)
                {
                    if($this->times>1)
                    {
                       break;
                    }
                    $this->times++;
                }
                $this->workers_count++;
                $this->_log("child process (PID:" . $pid . ") is running now");
            }
            else if ($pid==0) /*child*/
            {
                // 这个符号表示恢复系统对信号的默认处理
                pcntl_signal(SIGTERM, SIG_DFL);
                pcntl_signal(SIGCHLD, SIG_DFL);
                if (!empty($this->job))
                {
                    while ($this->job['runtime'])
                    {
                        if(!empty($this->job['argv']))
                        {
                            if(isset($argv[$this->workers_count]))
                            {
                                if(is_array($argv[$this->workers_count]))
                                {
                                    array_merge($this->job['argv'], $argv[$this->workers_count]);
                                }
                                else
                                {
                                    array_unshift($this->job['argv'], $argv[$this->workers_count]);
                                }
                            }
                        }
                        else
                        {
                            if(is_array($argv[$this->workers_count]))
                            {
                                $this->job['argv'] = $argv[$this->workers_count];
                            }
                            else
                            {
                                $this->job['argv'] = array($argv[$this->workers_count]);
                            }
                        }
                        call_user_func_array($this->job['function'], $this->job['argv']);
                        $this->job['runtime']--;
                        sleep(2);
                    }
                    exit();
                }
                return;
            }
            else
            {
                sleep(2);
            }
        }
        $this->mainQuit();
        exit(0);
    }
  
    //整个进程退出
    public function mainQuit()
    {
        if (file_exists($this->pid_file))
        {
            unlink($this->pid_file);
            $this->_log("delete pid file " . $this->pid_file);
        }
        $this->_log("daemon process exit now");
        posix_kill(0, SIGKILL);
        exit(0);
    }
  
    // 设置工作实例，目前只支持单个job工作
    public function addJob($job=array())
    {
        if(!isset($job['argv'])||empty($job['argv']))
        {
            $job['argv']=array();
        }
        if(!isset($job['runtime'])||empty($job['runtime']))
        {
            $job['runtime']=1;
        }
        if(!isset($job['function'])||empty($job['function']))
        {
            $this->log("你必须添加运行的函数！");
        }
        $this->job=$job;
    }
    //日志处理
    private  function _log($message)
    {
        printf("%s\t%d\t%d\t%s\n", date("H:i:s"), posix_getpid(), posix_getppid(), $message);
    }
  
}
//单例,无守护进程
//$daemon=new Process(true);
//$daemon->addJob(array('function'=>'work','argv'=>'','runtime'=>1));//function 要运行的函数,argv运行函数的参数，runtime运行的次数
//$daemon->start(2);//开启2个子进程工作
//单例,无守护进程
$daemon=new Process(true);
//$daemon->daemonize();
$daemon->addJob(array('function'=>'work','argv'=>array(),'runtime'=>1));//function 要运行的函数,argv运行函数的参数，runtime运行的次数
$daemon->start(2, array('A', 'B'));//开启2个子进程工作

//具体功能的实现
function work()
{
    $argv = func_get_args();
    echo "test PID:" . posix_getpid() . " argv:" . $argv[0] . "\n";
}
?>