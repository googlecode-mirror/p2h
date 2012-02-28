<?php
return array(
	'IS_STATIC'=>true, //是否静态化
	'APP_NAME'=>'p2h', //应用名称
	'UPDATE_URL'=>'http://localhost:8081/p2h', //请求更新的服务器地址
	'LIFE_TIME'=>array( //静态时间
		'MAIN'=>array( //主目录
			'INDEX'=>3600, //index.php
			'LIST'=>600, //list.php
			'OTHER'=>3600, //其他
		),
		'BLOG'=>array( //blog目录
			'INDEX'=>3600,
			'LIST'=>600,
			'OTHER'=>3600,
		),
		'BBS'=>array( //bbs目录
			'INDEX'=>3600,
			'LIST'=>600,
			'OTHER'=>3600,
		),
	),
	'RW'=>array( //静态重写规则
		'RULE'=>'_', //连接符
		'END'=>'html', //格式
	),
	'ARGS'=>array( //合法参数
		'MAIN'=>array(
			'INDEX'=>array('id'), //index.php的合法参数是id,其他参数传过来不生成静态
			'LIST'=>array('gid', 'cid'),
		),
		'BLOG'=>array(
			'INDEX'=>array('id'),
			'LIST'=>array('gid', 'cid'),
		),
		'BBS'=>array(
			'INDEX'=>array('id'),
			'LIST'=>array('gid', 'cid'),
		),
	),

);

?>