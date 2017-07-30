<?php

namespace Common\Helper;

/**
 * Instance class with singleton mode
 *
 * @author chenguoxi
 */
class ServiceHelper{
	
	/**
	 * 服务列表
	 * @var array
	 */
	public static $_services = array();
	
	public static function getInstance($service='',$param=array()){
		// first letter with up case 
		$service = ucfirst($service);
		// use name space
		$serviceName = "Common\\Service\\{$service}Service";
		if (!class_exists($serviceName)){
			return false;
		}
		
		if (isset(self::$_services[$service]) && is_object(self::$_services[$service])){
			return self::$_services[$service];
		}
		else {
			self::$_services[$service] = new $serviceName($param);
			return self::$_services[$service];
		}
	}
}