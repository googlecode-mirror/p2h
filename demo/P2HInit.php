<?php
require_once './P2HConfig.php';
date_default_timezone_set('PRC');

require_once '../P2H.php';
P2H::init($P2HConfig);
unset($P2HConfig);
?>