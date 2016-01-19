<?php

/**
 * User: rudy
 * Date: 2016/01/19 19:39
 *
 *  日志记录工具
 *
 */

class Logger{

    /**
     * 日志配置文件数组
     * @var array
     */
    private static $config_arr = array(
        'default' => array(
            'config'
        ),
    );

    const LOG_


    private $logName = 'default'; //日志名称
    private $suffix = 'log'; //文件后缀
    private $status;


    public static function loadConfig($arr){
        if(!empty($arr) && is_array($arr)){
            self::$config_arr = array_merge(self::$config_arr,$arr);
        }
    }

    public static function factory($logName){

    }
}