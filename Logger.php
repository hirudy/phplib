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
     * 日志数组
     * @var array
     */
    private static $config_arr = array(
        'default' => array(
            'config'
        ),
    );

    private $logName; //日志名称

    private $;


    public static function loadConfig($arr){
        if(!empty($arr) && is_array($arr)){
            self::$config_arr = array_merge(self::$config_arr,$arr);
        }
    }

    public static function factory($logName){

    }
}