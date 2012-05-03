<?php
error_reporting(-1);
define('M5CPL', 'just a test');
$P2HConfig = array(
	'isStatic'=>true, //是否生成静态
	'debug'=>true, //是否开启调试 开启则会把错误打印在屏幕上
	//各页面的配置信息
	'pageInfo'=>array(
			//index.php
			'index'=>array(
					'timeout'=>10,
					'args'=>array('cid', 'pag'),
			),
			//list.php
			'list'=>array(
					'timeout'=>20,
					'args'=>array('cid', 'gid', 'pag'),
			),
	),
	'rootURL'=>'http://localhost/p2h/demo/', //项目URL 如:http://unbox.xda.cn
	//'updateURL'=>'http://localhost/p2h/demo/', //静态更新请求的URL
	'appPath'=>dirname(__FILE__), //项目路径
	'p2hPath'=>dirname(dirname(__FILE__)).'/', 
	'jqueryURL'=>'http://localhost/p2h/jquery-1.2.min.js', //jq url js发送ajax请求的时候用到
	'htmls'=>'html', //存放html的文件夹的名字 这个文件夹放在app根目录下
	//'rwEnd'=>'.html', //静态文件扩展名
	//'rwRule'=>'_', //静态文件名的连接符号
	'req'=>$_REQUEST, //$_REQUEST数组
);
?>