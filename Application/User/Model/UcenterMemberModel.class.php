<?php
namespace User\Model;

use Think\Model;

class UcenterMemberModel extends Model
{
	protected $tableName = 'users';
	protected $tablePrefix = 'my_';
	public function login($username, $password)
	{
		if($user = $this->where(['username'=>$username])->find()) {
			if($user && $user['status']) {
				if(!empty($user['salt'])) {
					if($user['password'] = compile_password($password, $user['salt'])) {
						$this->save(['id' =>$user['id'],  'salt'=>'' ]);
					}else {

					}

				}else {
					if(!empty($user['my_salt'])) {
						if($user['password'] = compile_password($password, $user['my_salt'])) {

						}else {
							
						}
					}else {
						mt_rand(10000, 99999);
					}
				}
			}else {
				return -2;
			}
		}else {
			return -1;
		}
	}

	public function register()
	{

	}
}