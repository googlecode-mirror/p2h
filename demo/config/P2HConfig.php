<?php
//设置默认时区为中国
date_default_timezone_set('PRC');
/**
 * 静态相关
 */
$P2HConfig = array(
		//是否生成静态
		'isStatic'=>true,
		
		//项目URL
		'rootURL'=>ROOT,
		
		//请求静态更新的URL
		'updateURL'=>ROOT,
		
		/**
		 * 调试模式:
		* 0关闭调试
		* 1开启调试并把错误打印在屏幕上
		* 2开启调试并把错误保存在文件中
		*/
		'debug'=>1,
		
		//项目路径
		'appPath'=>App,
		
		//此项只在设置了debug模式为2时起效
		'debugFile'=>App.DS.'html'.DS.'log'.DS.'p2herror.log',

		//p2h路径
		'p2hPath'=>Bin.'/P2H',

		/**
		 * 各页面的配置信息
		* 如果不指定args 将不会按预期重写地址 而是返回index.html
		* 如果不指定timeout 那么会给个默认值3600s
		*/
		'pageInfo'=>array(
				// index.php
				'index'=>array(
						'timeout'=>500,
				),
				// news.php
				'news'=>array(
						'timeout'=>500,
						'args'=>array('id'),
				),
				// news/it/index.php
				'news/it/index'=>array(
						'timeout'=>500,
				),
				// news/it/news.php
				'news/it/news'=>array(
						'timeout'=>500,
						'args'=>array('id'),
				),
				
		),

		//可注释 jq url js发送ajax请求的时候用到 默认为jq官网链接
		'jqueryURL'=>'http://img2.xda-china.com/lab/static/js/jquery.js',

		//存放html的文件夹的名字 这个文件夹放在app根目录下 默认为html
		//'htmls'=>'html',

		//静态文件扩展名 默认为.html
		//'rwEnd'=>'.html',

		//静态文件名的连接符号 默认为_
		//'rwRule'=>'_',

		//$_REQUEST数组
		'req'=>$_REQUEST,

		//是否压缩
		//'minify'=>false,
);
?>