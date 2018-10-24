<?php

namespace Home\Model;

use Think\Model;

class DocumentModel extends Model
{
	 protected $_validate = array(
	 	 array('document_name','require', '文档名称不得为空'),
	 	 array('document_name', '', '文档名称不得重复', 0, 'unique'),
	 	 array('model', 'require', '模型名称不得为空')
	 );

	 protected $_auto = array(
	 	 array('status', 1)
	 );

	 public function info($id, $field = true) 
	 {
	 	  if(!$id) {
	 	  	return false;
	 	  }

	 	  return $this->field($field)->find($id);
	 }

	 public function lists()
	 {
	 	 
	 }
}