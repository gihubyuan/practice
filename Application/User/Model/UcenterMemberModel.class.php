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

	protected $_auto = array(
		array('password', 'compile_pwd', 1, 'callback')
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
	  		case 2:
	  			$enc_password = md5(md5($password) . $enc_seed);
	  			break;
	  		case 3:
	  			$enc_password = md5($enc_seed.md5($password));
	  			break;
	  		default:
	  			$enc_password = '';
	  			break;
	  	}

	  	if($user['password'] != $enc_password) {
	  		return -2;
	  	}
	  	$this->save(['id'=>$user['id'], 'salt'=>'', 'password'=>$this->compile_pwd(array('password' =>$password))]);
	  	return $user['id'];
	  }else {
	  	if($user['password'] == $this->compile_pwd(array('password'=>$password, 'user_salt'=>$user['user_salt']))) {
	  		if(empty($user['user_salt'])) {
	  			$user_salt = mt_rand(10000, 99999);
	  			M('myUsers')
	  			  ->where(['id'=>$user['id']])
	  			  ->save([
	  			  	'user_salt'=>$user_salt, 
	  			  	'password'=>$this->compile_pwd(array('password'=>$password, 'user_salt'=>$user_salt))]);
	  		}
	  		return $user['id'];
	  	}else {
	  		 return -2;
	  	}
	  }
  }


	protected function compile_pwd($data)
	{
		 if(is_string($data)) {
		 	 return md5($data);
		 }
		 if(!empty($data['password'])) {
		 	 $data['md5password'] = md5($data['password']);
		 }

		 if(!isset($data['type'])) {
		 	  $data['type'] = 1;
		 }

		 switch($data['type']) {
		 	  case 1:
		 	   if(empty($data['user_salt'])) {
				 	  return $data['md5password'];
				 }else {
		 	 		  return md5($data['md5password'] . $data['user_salt']);
				 }
		 	  case 2:
		 	  	if(empty($data['salt'])) {
		 	  		$data['salt'] = '';
		 	  	}
		 	  	return md5($data['md5password'] . $data['salt']);
		 	   case 3:
		 	   	if(empty($data['salt'])) {
		 	  		$data['salt'] = '';
		 	  	}
		 	  	return md5($data['salt'] . $data['md5password'] );
		 	  	default: 
		 	  		return '';
		 }
	}

	public function register($username, $password, $repassword, $email, $sex = 0, $birthday = '')
	{
		$data = [
			'username' => $username,
			'password' => $password,
			'repassword' => $repassword,
			'email' => $email
		];
		$data['sex'] = in_array((int) $sex, array(0,1,2)) ? (int) $sex : 0;
		!empty($birthday) && $data['birthday'] = $birthday; 		
		if($this->create($data)) {
			$uid = $this->add();
			$user = ['uid'=>$uid, 'username'=>$data['username']];
			return $uid;
		}else {
			return $this->getError();
		}
	}
}