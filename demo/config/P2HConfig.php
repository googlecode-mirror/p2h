<?php

/**
 * 静态相关
 */
return array(
		//是否生成静态
		'isStatic'=>true,
		
		
		/**
		 * 调试模式:
		* 0关闭调试
		* 1开启调试并把错误打印在屏幕上
		* 2开启调试并把错误保存在文件中
		*/
		'debug'=>1,
		
		//项目路径
		'appPath'=>APP,
		
		//此项只在设置了debug模式为2时起效
		'debugFile'=>APP.DS.'log'.DS.'p2herror.log',

		//p2h路径
		'p2hPath'=>dirname(APP),

		/**
		 * 各页面的配置信息
		* 如果不指定args 将不会按预期重写地址 而是返回index.html
		* 如果不指定timeout 那么会给个默认值3600s
		*/
		'pageInfo'=>array(
				// index.php
				'index'=>array(
						'timeout'=>500,
						'rootURL'=>ROOT,
						'updateURL'=>ROOT,
				),
				'news/index'=>array(
						'args'=>array('pag'),
						'timeout'=>500,
						'rootURL'=>NEWS_ROOT,
						'updateURL'=>NEWS_ROOT,
				),
				// news/news.php
				'news/news'=>array(
						'timeout'=>500,
						'args'=>array('id', 'pag'),
						'rootURL'=>NEWS_ROOT,
						'updateURL'=>NEWS_ROOT,
				),
				// news/it/index.php
				'news/it/index'=>array(
						'timeout'=>500,
						'args'=>array('pag'),
						'rootURL'=>IT_ROOT,
						'updateURL'=>IT_ROOT,
				),
				// news/it/news.php
				'news/it/news'=>array(
						'timeout'=>500,
						'args'=>array('id', 'cid', 'gid', 'pag' ),
						'rootURL'=>IT_ROOT,
						'updateURL'=>IT_ROOT,
				),
				
		),


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