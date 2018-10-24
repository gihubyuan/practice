<?php
return array(
//'配置项'=>'配置值'
'DB_TYPE'   => 'mysql', 
// 数据库类型
'DB_HOST'   => 'localhost', 
// 服务器地址
'DB_NAME'   => 'practice', 
// 数据库名
'DB_USER'   => 'root', 
// 用户名
'DB_PWD'    => '123456', 
// 密码
'DB_PORT'   => 3306, 
// 端口
'DB_PREFIX' => '', 
// 数据库表前缀 
'DB_CHARSET'=> 'utf8',
// 字符集
'TOKEN_ON'      =>    true, 
// 是否开启令牌验证 默认关闭
'TOKEN_NAME'    =>    '__hash__',   
// 令牌验证的表单隐藏字段名称，默认为__hash__
'TOKEN_TYPE'    =>    'md5', 
//令牌哈希验证规则 默认为MD5
'TOKEN_RESET'   =>    true

);