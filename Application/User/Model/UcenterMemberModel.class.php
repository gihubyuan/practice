<?php
namespace User\Model;

use Think\Model;

class UcenterMemberModel extends Model
{

	protected $tableName = 'users';
	protected $tablePrefix = 'my_';
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
	  		 if(md5($password . $user['my_salt']) == $user['password']
	  	}
	  }
	}


	public function register($username, $password, $email)
	{
	}

}