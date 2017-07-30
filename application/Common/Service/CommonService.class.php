<?php
namespace Common\Service;

class CommonService {
	public $error;
	
	public $cachePrefix = "sm_";
	
	public function isAdmin(){
		$group = (int)cookie('group');
		return $this->isLogin() && in_array($group, C('ADMIN_GROUP')) ? true : false;
	}
	
	public function isLogin(){
		$uid =get_uid();
		$username = cookie('username');
		$token = cookie('ltoken');
		
		if ( !$uid || empty($username) || !$token){
			cookie(null);
			return false;
		}
		else{
			$userModel = D('Home/User');
			$userInfo = $userModel->getUserById($uid);
				
			// 			dump($token);
			// 			dump($this->_generateLoginToken($userInfo['password']));
			if (!$userInfo || (string)$token != (string)$this->_generateLoginToken($userInfo['password']) || !isset($userInfo['group']) || (int)$userInfo['group']!=9){
				return false;
			}
		}
		return true;
	}
	
	protected function _generateLoginToken($password){
		return md5(C('LOGIN_ENCRYPT_KEY').md5($password));
	}
	
	public function setError($error){
		$this->error = $error;
	}
	
	public function getError(){
		return $this->error;
	}
}