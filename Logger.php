<?php

/**
 * User: rudy
 * Date: 2016/01/19 19:39
 *
 * 日志记录工具
 *
 */

class Logger{
    public static $g_isLogging = true;  // 总开关，是否记录日志

    const LOG_MODE_NORMAL = 1; // 生产模式,正常记录到文件中
    const LOG_MODE_PRINT  = 2; // 调试模式,打印到屏幕上
    const LOG_MODE_BOTH   = 3; // 文件与屏幕上都有

    const LOG_FREQUENCY_NONE   = 0; // 存放的日志文件始终只有一个文件,形如 default.log
    const LOG_FREQUENCY_MINUTE = 1; // 存放日志每隔一分钟换一个，形如 default_201601192357.log
    const LOG_FREQUENCY_HOUR   = 2; // 存放日志每隔一小时换一个，形如 default_2016011923.log
    const LOG_FREQUENCY_DAY    = 3; // 存放日志每隔一天换一个，形如   default_20160119.log
    const LOG_FREQUENCY_MONTH  = 4; // 存放日志每隔一月换一个，形如   default_201601.log

    const LOG_LEVEL_ERROR = 1;  //日志等级,错误日志
    const LOG_LEVEL_WARN  = 2;  //日志等级,警告日志
    const LOG_LEVEL_INFO  = 3;  //日志等级,信息记录日志

    public static $g_basePath = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR; //默认存储路径

    protected static $allowFrequencyList = array(   //允许修改日志频率
        self::LOG_FREQUENCY_MINUTE,
        self::LOG_FREQUENCY_HOUR,
        self::LOG_FREQUENCY_DAY,
        self::LOG_FREQUENCY_MONTH
    );

    protected static $g_config_arr = array(  // 日志配置文件数组,default是默认配置项
        'default' => array(
            'isLogging' => true,
            'basePath' => '',
            'suffix' => 'log',
            'mode' => self::LOG_MODE_NORMAL,
            'frequency' => self::LOG_FREQUENCY_MINUTE,
        ),
    );

    protected $isLoging;      //当前日志,是否记录
    protected $logName;       //当前日志,日志名称
    protected $basePath;      //当前日志,存储路径
    protected $suffix;        //当前日志,日志文件后缀
    protected $mode;          //当前日志,记录方式
    protected $frequency;     //当前日志,日志记录每隔一（分钟/小时/天/月）换一个文件记录

    private $logFilePath;     //完整的日志路径


    public static function loadConfig($arr){
        if(!empty($arr) && is_array($arr)){
            unset($arr['default']);
            self::$g_config_arr = array_merge(self::$g_config_arr,$arr);
        }
    }

    public static function factory($logName='default'){
        if(empty($logName) || !is_string($logName) || !isset(self::$g_config_arr[$logName])){
            throw new Exception("Make sure that the log configuration which name is '{$logName}' is loaded successfully");
        }
        return new self($logName,self::$g_config_arr[$logName]);
    }

    public function __construct($logName='default',$config = array()){
        $this->isLoging = (isset($config['isLogging']))?$config['isLogging']:self::$g_config_arr['default']['isLogging'];
        $this->logName = (empty($logName) || !is_string($logName))?'default':$logName;
        $this->basePath = isset($config['basePath'])?$config['basePath']:self::$g_basePath;
        $this->suffix = isset($config['suffix'])?$config['suffix']:self::$g_config_arr['default']['suffix'];
        $this->mode = isset($config['mode'])?$config['mode']:self::$g_config_arr['default']['mode'];
        $this->frequency = isset($config['frequency'])?$config['frequency']:self::$g_config_arr['default']['frequency'];

        $this->basePath = rtrim($this->basePath,"\\/");
        if(!is_dir($this->basePath)){
            if( !mkdir($this->basePath,0755,true)){
                throw new Exception("create directory fail:".$this->basePath);
            }
        }
        $this->logFilePath = $this->basePath.DIRECTORY_SEPARATOR.$this->logName.$this->suffix;
    }

    protected function write($filePath,$content){
        $return_value = false;
        if(self::$g_isLogging && $this->isLoging){
            $content = $content."\n";
            switch($this->mode){
                case self::LOG_MODE_NORMAL:{
                    $return_value = file_put_contents($filePath,$content,LOCK_EX);
                    $return_value = (int)$return_value > 0 ?true:false;
                }break;
                case self::LOG_MODE_PRINT:{
                    echo @substr($filePath,-10,10),':',$content;
                    $return_value = true;
                }break;
                case self::LOG_MODE_BOTH:{
                    echo @substr($filePath,-10,10),':',$content;
                    file_put_contents($filePath,$content,LOCK_EX);
                    $return_value = (int)$return_value > 0 ?true:false;
                }break;
            }
        }
        return $return_value;
    }


    public function log($content,$level = self::LOG_LEVEL_INFO){
        if(!is_string($content)){
            $content = serialize($content);
        }
        if($content === ''){
            return false;
        }

        $logTime = time();
        switch($level){
            case self::LOG_LEVEL_ERROR:{
                $content = sprintf('[%s %s] %s',@date('Y-m-d H:i:s',$logTime),'error',$content);
            }break;
            case self::LOG_LEVEL_WARN:{
                $content = sprintf('[%s %s] %s',@date('Y-m-d H:i:s',$logTime),'warn',$content);
            }break;
            default:
                $content = sprintf('[%s %s] %s',@date('Y-m-d H:i:s',$logTime),'info',$content);
        }

        $rel = $this->write($this->logFilePath,$content);

        // 检测是否需要对日志文件进行重命名
        if($rel && in_array($this->frequency,self::$allowFrequencyList)){
            $fileCreateTime = @filectime($this->logFilePath);
            if($fileCreateTime){
                $interval = $logTime - $fileCreateTime;
                $timeLength = 0;
                if($interval >= 60){
                    switch($this->logFilePath){
                        case self::LOG_FREQUENCY_MINUTE: $timeLength = 12;break;
                        case self::LOG_FREQUENCY_HOUR: $timeLength = 10;break;
                        case self::LOG_FREQUENCY_DAY: $timeLength = 8;break;
                        case self::LOG_FREQUENCY_MONTH: $timeLength = 6;break;
                    }
                }
                if($timeLength > 0){
                    $logTimeFormat = substr(@date('YmdHis',$logTime),0,$timeLength);
                    $createTimeFormat = substr(@date('YmdHis',$fileCreateTime),0,$timeLength);
                    if(strcmp($logTimeFormat,$createTimeFormat) !== 0){
                        $newLogFilePath = $this->basePath.DIRECTORY_SEPARATOR.$this->logName.'_'.$createTimeFormat.$this->suffix;
                        rename($this->logFilePath,$newLogFilePath);
                    }
                }

            }
        }
        return true;
    }
}

//测试
if(true){
    $log = Logger::factory();
    $data =  $log->log('hello 哈哈中文');
    echo $data,"\n";
}