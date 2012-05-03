<?php
$P2HConfig = array(
	'isStatic'=>true, //是否生成静态
	'debug'=>true, //是否开启调试 开启则会把错误打印在屏幕上
	//各页面的配置信息
	'pageInfo'=>array(
			//index.php
			'index'=>array(
					'timeout'=>60,
					'args'=>array('cid', 'pag'),
			),
			//list.php
			'list'=>array(
					'timeout'=>20,
					'args'=>array('cid', 'gid', 'pag'),
			),
	),
	'rootURL'=>'http://localhost/zhupp_google/p2h/demo/', //项目URL 如:http://unbox.xda.cn
	'updateURL'=>'http://localhost/zhupp_google/p2h/demo/', //静态更新请求的URL
	'p2hPath'=>dirname(dirname(__FILE__)).'/', 
	'jqueryURL'=>'http://localhost/zhupp_google/p2h/jquery-1.2.min.js', //jq url
	'P2HJSURL'=>'http://localhost/zhupp_google/p2h/P2H.js', //更新静态页的JSURL
	'htmlPath'=>dirname(__FILE__).'/html', //存放html的目录路径 如:D:/www/index/html/
	//'rwEnd'=>'.html', //静态文件扩展名
	//'rwRule'=>'_', //静态文件名的连接符号
	'req'=>$_REQUEST, //$_REQUEST数组
);
?>