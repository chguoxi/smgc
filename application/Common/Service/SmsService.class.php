<?php
namespace Common\Service;
use Common\Helper\ValidateHelper;
use Common\Helper\ServiceHelper as Service;
use Common\Helper\JuheHelper;

class SmsService extends CommonService{
	public $error;
	public $model;
	public $debug = TRUE;
	
	/**
	 * 每日限制条数
	 * @var integer
	 */
	private $_dayLimit=10;
	/**
	 * 每小时限制条数
	 * @var integer
	 */
	private $_hourLimit=3;
	
	/**
	 * 发送频率间隔，发送一条出去后，发送下一条的间隔时间 单位为秒
	 * @var integer
	 */
	private $_frequency=90;
	
	/**
	 * 验证码有效时间 /s
	 * @var integer
	 */
	public $validtime = 90;
	public $smsType = array(
		'register',
		'forget'
	);
	
	public function __construct(){
		$this->model = D('Sms');
	}
	
	public function send($phone,$type='register'){
		//$model = D('Sms');
		if (!$this->_checkPhoneValid($phone)){
			return false;
		}
		
		if ($type=='register' && !$this->_checkUserExist($phone,$type)){
			return false;
		}
		
		if (!$this->_checkHourSentCount($phone) || !$this->_checkDaySentCount($phone) || !$this->_chekcSentFrequency($phone)){
			return false;
		}
		
		$code = rand(100000,999999);
		// 测试模式不真实发送短信
		if (!$this->debug){
		    $response = json_decode(JuheHelper::send($code,$phone,$type),true);
		}
		
		
		$data = array(
				'phone'     => $phone,
				'type'      => $type,
				'code'      => $code,
				'senttime'  => time(),
				'validtime' => $this->validtime
		);
		
		if ($response['error_code']==0){
			return $this->model->add($data);
		}
		else{
			$this->error = $response['reason'];
			return false;
		}
	}
	
	private function _iniDebugMode(){
	    $this->_frequency = 9000;
	    $this->_dayLimit  = 10000;
	    $this->_hourLimit = 600;
	}
	
	private function _checkPhoneValid($phone){
		if (!ValidateHelper::isPhone($phone)){
			$this->error = '手机号码格式不正确';
			return false;
		}
		return ture;
	}
	
	private function _checkUserExist($phone,$type='register'){
		$user = D('Users')->getUserByPhone($phone);
		if ($user && $type=='register'){
			$this->error = '该手机号码已注册,请登录';
			return false;
		}
		else if(!$user && $type=='forget'){
			$this->error = '该手机号码为注册,请注册';
			return false;
		}
		return true;
	}
	
	public function validateSms($code,$phone,$type){
		if (!$code || !$phone || !$type){
			return false;
		}
		$where = array(
				'phone' => $phone,
				'code'  => $code,
				'type'  => $type
		);
		$cinfo = $this->model->where($where)->order("id desc")->find();
		// 找不到验证码
		if (!$cinfo){
			$this->error = '手机验证码不正确';
			return false;
		}
		$curtime = time();
		// 找到验证码，检查是否已过期
		if ($curtime > $cinfo['senttime'] + $cinfo['validtime']){
			$this->error = '验证码已过期';
			return false;
		}
		return true;
	}
	
	/**
	 * 一小时发送量
	 */
	private function _checkHourSentCount($phone){
		$lastHourTimestamp = time() - 3600;
		$where['phone']    = array('eq',$phone);
		$where['senttime'] = array('between',$lastHourTimestamp,time());
		
		$count = $this->model->where($where)->count();
		
		if ($count>=$this->_dayLimit){
			$this->error = "您的手机接收短信数超过限制，一天内最多能接收{$this->_dayLimit}条";
			return false;
		}
		return true;
	}
	
	/**
	 * 一天内的发送量
	 * 
	 * @param string $phone
	 */
	private function _checkDaySentCount($phone){
		$lastDayTimestamp = time() - 3600 * 24;
		$where['phone']    = array('eq',$phone);
		$where['senttime'] = array('between',array($lastHourTimestamp,time()));
		$count = $this->model->where($where)->count();
		
		if ($count>=$this->_hourLimit){
			$this->error = "您的手机接收短信数超过限制，一小时内最多能接收{$this->_hourLimit}条";
			return false;
		}
		return true;
	}
	
	private function _chekcSentFrequency($phone){
		$where['phone']  = array('eq',$phone);
		
		$lastSent = $this->model->where($where)->order("senttime desc")->getField("senttime");
		if (!$lastSent || time()-$lastSent >= $this->_frequency){
			return true;
		}
		else{
			$this->error = "发送频率过高，请稍后再试";
			return false;
		}
	}
}