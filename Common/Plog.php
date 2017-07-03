<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 7/1/2017
 * Time: 12:10 AM
 */

namespace Common;


use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class PLog
{
    protected static $instance = null;

    private function __construct()
    {
    }

    public static function getLogPath()
    {
        return __DIR__ . '/../logs/payment.log';
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Logger('Payment');
            self::$instance->pushHandler(new StreamHandler(self::getLogPath()));
            self::$instance->pushHandler(new FirePHPHandler());


        }
        return self::$instance;
    }

    public static function info($action, $msg)
    {
        $pLog = self::getInstance();
        $pLog->info(self::prepare($action, $msg));
    }

    public static function error($action, $msg)
    {
        $pLog = self::getInstance();
        $pLog->error(self::prepare($action, $msg));
    }

    public static  function prepare($action, $msg) {
        return json_encode([
            'action' => $action,
            'time' => date('d-m-Y h:i:s'),
            'msg' => $msg
        ]);
    }

}