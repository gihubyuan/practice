<?php

namespace Home\Model;

use Think\Model;

class UserModel extends Model
{
	protected $tableName = 'users';
	protected $_validate = array(
		array('username', '5,12', -1, 1, 'length'),
		array('username','',-2 ,0,'unique',self::MODEL_INSERT),
		array('password', '5,16', -3 , 1, 'length'),
		array('repassword','password', -4, 0,'confirm')
	);

	protected $_auto = array(
		array('reg_time', 'time', self::MODEL_INSERT, 'function'),
		array('status', '1')
	);

	public function login($username, $password, $remember)
	{
		$user = $this->where(['username' => $username])->find();
		if($user && $user['status']) {
			if($user['password'] == $password) {
				$data = ['username' => $user['username'], 'uid'=>$user['id']];
				session('user_auth', $data);
				session('user_auth_sign', data_auth_sign($data));
				 if(!empty($remember)) {
				 	 cookie('uid', $user['id'], 3600*24);
				 	 return $user['id'];
				 }
			}else {
				$this->error = "密码错误";
				return false;
			}
		}else {
			$this->error = "用户不存在或被禁用";
			return false;
		}		
	}

	public function register($username, $password, $repassword)
	{
		if($this->create()) {
			$uid = $this->add();	
			return $uid ? $uid : 0;
		}
		return $this->getError();
	}

	public function lists($map = null, $field = true)
	{
		 return $this->field($field)->select();
	}
}