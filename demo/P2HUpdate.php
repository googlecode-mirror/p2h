<?php
define('M5CPL', 'just a test');
require_once './P2HConfig.php';
require_once '../P2H.php';

P2H::initConfig($P2HConfig);
P2H::update();

?>