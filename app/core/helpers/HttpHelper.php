<?php
/**
 * Wen, an open source application development framework for PHP
 *
 * @link http://www.wenzzz.com/
 * @copyright Copyright (c) 2015 Wen
 * @license http://opensource.org/licenses/MIT  MIT License
 */

namespace app\core\helpers;

use app\core\helpers\StringHelper;

/**
 * 处理http请求相关的助手静态类
 *
 *
 * @author WenXiong Cai <caiwxiong@qq.com>
 * @since 1.0
 */
class HttpHelper
{
	    
	/**
	 * 读取参数，默认读取GET参数
	 * 
	 * @param  string $str    参数key
	 * @param  string $method 数据来源类型，有 get, post, request
	 * @return string 参数key对应的值
	 */
	public static function v( $str, $method='get')
	{
	    if ('get' === strtolower($method))
	    {
	        return isset( $_GET[$str] ) ? StringHelper::transcribe($_GET[$str]) : '';
	    }

	    if ('post' === strtolower($method))
	    {
	        return isset( $_POST[$str] ) ? StringHelper::transcribe($_POST[$str]) : '';
	    }

	    if ('request' === strtolower($method))
	    {
	        return isset( $_REQUEST[$str] ) ? StringHelper::transcribe($_REQUEST[$str]) : '';
	    }
	}


	/**
	 * 跳转url
	 * @param  string $url 跳转地址
	 * @return 
	 */
	public static function forward( $url )
	{
	    if(!strstr($url, 'http://')){
	        $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
	    }
		header( "Location: " . $url );
	}


	public static function curl( $url , $data=array() , $method='get', $cookie = NUll)
	{
	    $ch = curl_init();
	    if( 'post' == strtolower($method) )
	    {
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
	    }else{
	        if($data)
	        {
	            foreach($data as $key=>$val)
	            {
	                $parame[] = $key.'='.$val;
	            }
	            $parame_str = implode('&', $parame);
	            $url = $url . '?' . $parame_str;
	        }
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    
	    if($cookie)
	    {
	        curl_setopt($ch, CURLOPT_COOKIE , $cookie);
	    }
	   
	    $response = curl_exec($ch);
	    
	    return $response;
	}


	/**
	 * 设置cookie
	 *
	 */
	public static function setCookie($key, $value, $expire='', $path='', $domain='')
	{
	    //默认24小时内有效
	    $expire = empty($expire) ?  time() + 24 * 3600 : $expire;
	    $path = empty($path) ? '/' : $path;
	    $domain = empty($domain) ? '' : $domain ;

	    setcookie($key, $value, $expire, $path, $domain);
	}

	/**
	 * 获取cookie
	 */
	public static function getCookie($name)
	{
	    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false; 
	}

	/**
	 * 删除cookie
	 *
	 */
	public static function deleteCookie($key, $domain='')
	{
	    $domain = empty($domain) ? '' : $domain ;
	    set_cookie($key, '', time()-3600, '/', $domain);
	    unset($_COOKIE[$key]);
	}


	/**
	 * 获取ip
	 */
	public static function getIP() {
	  if (isset($_SERVER)) {
	    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
	      $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	    } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
	      $realip = $_SERVER["HTTP_CLIENT_IP"];
	    } elseif( isset($_SERVER["REMOTE_ADDR"]) ) {
	      $realip = $_SERVER["REMOTE_ADDR"];
	    }else{
	      $realip = '';
	    }

	    if($realip == '127.0.0.1' && isset($_SERVER["HTTP_X_REAL_IP"])) {
	      $realip = $_SERVER["HTTP_X_REAL_IP"];
	    }
	  } else {
	    if (getenv('HTTP_X_FORWARDED_FOR')) {
	      $realip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif (getenv('HTTP_CLIENT_IP')) {
	      $realip = getenv('HTTP_CLIENT_IP');
	    } else {
	      $realip = getenv('REMOTE_ADDR');
	    }

	    if($realip == '127.0.0.1' && getenv('HTTP_X_REAL_IP')) {
	      $realip = getenv('HTTP_X_REAL_IP');
	    }
	  }

	  return $realip;
	}


	/**
	 * 常用发送socket请求
	 * @param $ip 连接IP
	 * @param $port 连接端口
	 * @param $cmd 请求命令
	 * @param $timeout 连接超时[最大超时时长5s，最大重试次数3次]
	 */
	public static function sendSock($ip, $port, $cmd, $timeout = 1) 
	{
	    if ($timeout > 5) 
	    {
	        $timeout = 5;
	    }
	    $retry = 0;
	    while($retry++ < 3)
	    {
	        $sock = fsockopen($ip, $port, $errno, $errstr, $timeout);
	        if ($sock) break;
	        usleep(100);//每次重试等待0.1秒
	    }

	    if (!$sock)
	    {
	        return FALSE;
	    }
	    fputs($sock, $cmd);

	    $body = '';
	    while (!feof($sock)) 
	    {
	        $body .= fread($sock, 1024);
	    }
	    
	    fclose($sock);
	    return $body;
	}



	/**
	 * 输出json格式数据
	 * @param  integer $errno  状态码标识
	 * @param  string  $msg    提示信息
	 * @param  array   $data   返回数据
	 * @param  boolean $isExit 是否退出
	 * @return 
	 */
	public static function jsonEcho($errno = 0, $msg = '', $data = array(), $isExit = TRUE) 
	{
	  echo json_encode(
	    array(
	        'errno' => $errno,
	        'msg' => $msg,
	        'data' => $data
	    ), JSON_UNESCAPED_UNICODE
	  );

	  $isExit && exit;
	}

	/**
	 * 输出jsonp格式数据
	 * @param  integer $errno  状态码标识
	 * @param  string  $msg    提示信息
	 * @param  array   $data   返回数据
	 * @param  boolean $isExit 是否退出
	 * @return 
	 */
	public static function jsonpEcho($errnoData = 0, $msg = '', $data = array(), $isExit = TRUE) { 

	    if( is_array($errnoData) && isset($errnoData['errno']) && isset($errnoData['msg']) && isset($errnoData['data']) ){
	      $errno = $errnoData['errno'];
	      $msg = $errnoData['msg'];
	      $data = $errnoData['data'];
	    }else{
	      $errno = $errnoData;
	    }

	    $info = array( 'errno' => $errno, 'msg' => $msg, 'data' => $data);
	    $callback = v('callback') !='' ? v('callback') : "callback";
	    if (!preg_match("/^[0-9a-zA-Z_]+$/", $callback)) {
	        die('callback parameter error');
	    }
	    echo "$callback(".json_encode($info, JSON_UNESCAPED_UNICODE).");";
	    $isExit && exit;
	}


}
