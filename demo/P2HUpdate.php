<?php
define('M5CPL', 'just a test');
require_once './P2HConfig.php';
require_once '../P2H.php';
P2H::initConfig($P2HConfig);
unset($P2HCofig);

if(!isset($_REQUEST['location']) || empty($_REQUEST['location']))
	die(json_encode(array('status'=>0)));

fopen(P2H::UnRWURL($_REQUEST['location']), 'r');
?>