<?php
namespace Common\Helper;


class ValidateHelper {
	public static function isPhone($phone) {
		return preg_match('/1(3|4|5|6|7){1}[\d]{9}/i', $phone);
	}
	
	public static function isEmail(){
		
	}
}