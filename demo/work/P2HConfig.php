<?php
error_reporting(-1);
define('M5CPL', 'just a test');
date_default_timezone_set('PRC');
define('DS', DIRECTORY_SEPARATOR);
$P2HConfig = array(
	//是否生成静态
	'isStatic'=>true, 
	
	/**
	 * 调试模式:
	 * 0关闭调试
	 * 1开启调试并把错误打印在屏幕上
	 * 2开启调试并把错误保存在文件中 文件保存在log/p2herror.txt
	 */
	'debug'=>1,
	
	//此项只在设置了debug模式为2时起效
	'debugFile'=>dirname(__FILE__).DS.'log'.DS.'p2herror.log', 
		
	//项目路径
	'appPath'=>dirname(__FILE__), 
		
	//p2h路径
	'p2hPath'=>dirname(dirname(dirname(__FILE__))),
		
	//项目URL 如:http://unbox.xda.cn
	'rootURL'=>'http://localhost/p2h/demo/work/', 
	
	//请求静态更新的URL 如果和rootURL地址一样可注释
	//'updateURL'=>'http://localhost/p2h/demo/', 

	/**
	 * 各页面的配置信息
	 * 如果不指定args 将不会按预期重写地址 而是返回index.html
	 * 如果不指定timeout 那么会给个默认值3600s
	 */
	'pageInfo'=>array(
			//index.php
			'index'=>array(
					'timeout'=>20,
					//'args'=>array('cid', 'pag'),
			),
			//list.php
			'list'=>array(
					'timeout'=>10,
					'args'=>array('cid', 'gid', 'pag'),
			),
	),

	//jq url js发送ajax请求的时候用到 默认为jq官网链接
	'jqueryURL'=>'http://localhost/p2h/plugin/jquery-1.2.min.js', 
		
	//存放html的文件夹的名字 这个文件夹放在app根目录下 默认为html
	//'htmls'=>'html', 

	//静态文件扩展名 默认为.html
	//'rwEnd'=>'.html', 

	//静态文件名的连接符号 默认为_
	//'rwRule'=>'_', 

	//$_REQUEST数组
	'req'=>$_REQUEST, 
);
?>