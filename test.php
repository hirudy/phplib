<?php
/**
 * User: rudy
 * Date: 2016/01/20 12:31
 *
 *  功能描述
 *
 */

include 'Logger.php';

$log = Logger::factory();
$log->log(array('abaa',"'aaa'",'中午'));
$log->log($log);