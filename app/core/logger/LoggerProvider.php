<?php
/**
 * Wen, an open source application development framework for PHP
 *
 * @link http://wen.wenzzz.com/
 * @copyright Copyright (c) 2016 Wen
 * @license http://opensource.org/licenses/MIT	MIT License
 */

namespace app\core\logger;

use Exception;
use \app\core\base\Wen;

/**
 * 日志器服务提供者，统一接口，通过setLogger方法实例化具体的日志器，实现依赖注入
 * 
 * 这样做的好处：解耦，高层业务不依赖底层实现
 *
 *
 * @author WenXiong Cai <caiwxiong@qq.com>
 * @since 1.0
 */
class LoggerProvider
{

	private $logger;
	
    public function __construct($config)
    {
        $this->setLogger($config);
    }

    private function setLogger($config)
    {
        if(empty($config) || empty($config['class'])  ) {
            throw new Exception(Wen::t('cannot find logger class'), 500);
        }

        $loggerClass = $config['class'];
        
        if(!class_exists($loggerClass)) {
            throw new Exception( Wen::t('cannot create logger instance',['logger'=>$loggerClass]), 500);
        }

        $logDir = isset($config['dir']) ? $config['dir'] : ROOT . '/tmp/logs/';
        $this->logger = new $loggerClass($logDir);

        $format = isset($config['fileNameFormat']) ? $config['fileNameFormat'] : 'app.%name.%date.log';
        $this->logger->setLoggerFormat($format);
    }

    public function error($message, $fileName='')
    {
        $this->logger->error($message, $fileName);
    }

    public function notice($message, $fileName='')
    {
        $this->logger->notice($message, $fileName);
    }

    public function info($message, $fileName='')
    {
        $this->logger->info($message, $fileName);
    }

    public function debug($message, $fileName='')
    {
        $this->logger->debug($message, $fileName);
    }
   
}