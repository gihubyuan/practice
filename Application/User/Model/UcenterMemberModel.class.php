<?php
namespace User\Model;

use Think\Model;

class UcenterMemberModel extends Model
{

	protected $tableName = 'users';
	protected $tablePrefix = 'my_';

	protected $_validate = array(
		array('username', '6,12', -1, 0, 'length',self::MODEL_BOTH),
		array('username', '', -2, 0, 'unique',self::MODEL_INSERT),

		array('password', '5,16', -3, 0, 'length'),
		array('repassword', 'password', -4, 0, 'confirm'),

		array('email', 'check_email', -5, 0, 'function')
	);

	public function login($username, $password)
	{
	  $user = $this->where(['username'=>$username])->find();

	  if(!$user || !$user['status']) {
	  	return -1;
	  }

	  if(!empty($user['salt'])) {
	  	$enc_type = substr($user['salt'], 0, 1);
	  	$enc_seed = substr($user['salt'], 1);
	  	switch($enc_type) {
	  		case '1':
	  			$password = md5(md5($password) . $enc_seed);
	  			break;
	  		case '2':
	  			$password = md5(md5($password . $enc_seed));
	  			break;
	  		default:
	  			$password = md5($password);
	  			break;
	  	}

	  	if($user['password'] == $password) {
	  		$this->save(['id'=>$user['id'], 'salt'=>'']);
	  		return $user['id'];
	  	}else {
	  		return -2;
	  	}

	  }else {
	  	if(empty($user['my_salt'])) {

	  	}else {
	  		 if(md5($password . $user['my_salt']) == $user['password']) {

	  		 }
	  	}
	  }
	}


	public function register($username, $password, $repassword, $email)
	{

		$data = [
			'username' => $username,
			'password' => $password,
			'repassword' => $repassword,
			'email' => $email
		];
		if($this->create()) {
			$uid = $this->add();
			return $uid;
		}else {
			return $this->getError();
		}
	}

}