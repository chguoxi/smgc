<?php
namespace Common\Service;
//use Common\Service\UserService as User;

class UserService extends CommonService{
    protected $model;
    
    public function __construct(){
        $this->model = M('User');
        parent::__construct();
    }
    
    public function getUserByPhone($mobile=''){
        
    }
    
}