<?php

/**
 * 聚合数据接口
 */

namespace Common\Helper;

class JuheHelper {
	private static $_APP_KEY   = 'b971dd4eaa843db6f4cc45eb310740b9';
	private static $_SERVE_URL = 'http://v.juhe.cn/sms/send';
	
	public static $S_TEMPLATE_ID = array(
			'register' => 31559,
			'forget'   => 31560
	);
	
	public static $ERROR = '';
	
	public static function send($code,$mobile='',$type='register') {
		
		$templateId = self::_getTemplateId($type);
		$param = array(
				'mobile'    => $mobile,
				'tpl_id'    => $templateId,
				'tpl_value' => "#code#={$code}",
				'key'       => self::$_APP_KEY,
				'dtype'     => 'json'
		);
		return self::http(self::$_SERVE_URL."?".http_build_query($param));
	}
	
	public static function http($url, $params, $method = 'GET', $header = array(), $multi = false){
		$opts = array(
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HTTPHEADER     => $header
		);
		/* 根据请求类型设置特定参数 */
		switch(strtoupper($method)){
			case 'GET':
				$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
				break;
			case 'POST':
				//判断是否传输文件
				$params = $multi ? $params : http_build_query($params);
				$opts[CURLOPT_URL] = $url;
				$opts[CURLOPT_POST] = 1;
				$opts[CURLOPT_POSTFIELDS] = $params;
				break;
			default:
				throw new Exception('不支持的请求方式！');
		}
		/* 初始化并执行curl请求 */
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if($error) throw new Exception('请求发生错误：' . $error);
		return  $data;
	}
	
	private static function _getTemplateId($type=''){
		return isset(self::$S_TEMPLATE_ID[$type]) ? self::$S_TEMPLATE_ID[$type] : self::$S_TEMPLATE_ID['register'];
	}
}