# phplib
实际使用总结出来的通用phpLib

## TLogger
日志记录工具

```php
interface ILoggerHandle{
    // 解析配置文件,看配置文件是否满足需求
    public static function parseConfig($rawConfig);

    // 不同级别日志记录函数
    public function fatal($message);
    public function error($message);
    public function warn($message);
    public function info($message);
    public function debug($message);
}
```
对外提供的函数,总共有5种日志级别,等级依次降低

#### 配置样例
```php
    array(
        'name' => 'default',                           // 日志名称,全局唯一
        'isLogging' => true,                           // 当前日志是否记录
        'basePath' => TLogger::$g_basePath,            // 当前日志的记录根目录,没有,默认全局目录:g_basePath
        'mode' => TLogger::LOG_MODE_FILE,              // 记录模式
        'level' => TLogger::LOG_LEVEL_DEBUG,           // 日志等级
        'frequency' => TLogger::LOG_FREQUENCY_NONE,    // 切割日志方式
    )
```

#### 使用举例
```php
    $config = array(  // 日志配置文件数组,default是默认配置项
        'name' => 'test',
        'level' => TLogger::LOG_LEVEL_INFO,
        'frequency' => TLogger::LOG_FREQUENCY_MINUTE
    );
    TLogger::$g_basePath = __DIR__.DIRECTORY_SEPARATOR.'log';
    TLogger::loadOneConfig($config);

    $logger = TLogger::getLogger("test");
    $logger->debug("this is debug info ");
    $logger->info(array("is","info","recode"));
    $logger->warn(21);
    $logger->error("error info ");
    $logger->fatal($logger);
```



## Image
一些图片相关操作封装

## MysqlDB
mysql操作类，需要mysqli扩展

## phpAb
php实现的压测工具
