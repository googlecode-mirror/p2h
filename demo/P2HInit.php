<?php
error_reporting(-1);

define('M5CPL', 'just a test');

date_default_timezone_set('PRC');

$P2HConfig = array(
	'isStatic'=>true, //是否生成静态
	'debug'=>true, //是否开启调试 开启则会把错误打印在屏幕上
	//各页面的配置信息
	'pageInfo'=>array(
			//index.php
			'index'=>array(
					'timeout'=>3600,
					'args'=>array('id', 'cid'),
			),
			//list.php
			'list'=>array(
					'timeout'=>3600,
					'args'=>array('id'),
			),
	),
	'rootURL'=>'http://localhost/p2h/demo/', //项目URL 如:http://unbox.xda.cn
	'updateURL'=>'http://localhost/p2h/demo/', //静态更新请求的URL
	'p2hPath'=>dirname(dirname(__FILE__)).'/', 
	'jqueryURL'=>'http://localhost/p2h/jquery-1.2.min.js', //jq url
	'htmlPath'=>dirname(__FILE__).'/html', //存放html的目录路径 如:D:/www/index/html/
	//'rwEnd'=>'.html', //静态文件扩展名
	//'rwRule'=>'_', //静态文件名的连接符号
	'req'=>$_REQUEST, //$_REQUEST数组
);
require_once '../P2H.php';
P2H::init($P2HConfig);
unset($P2HConfig);
?>