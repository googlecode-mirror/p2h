<?php
define("M5CPL", "d8a3d75ac2f6b243d869f1f2927bbcb6");
define('App', __DIR__);
define('ROOT', 'http://localhost/zhupp_google/p2h/demo/');
require_once App.'/config/P2HConfig.php';
require_once dirname(App).'/P2H.php';
//载入静态配置
P2H::initConfig($P2HConfig);

function D($var){
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
	exit;
}
?>