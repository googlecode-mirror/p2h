<?php
define('APP_ROOT', dirname(__FILE__));
require_once APP_ROOT.'/P2HConfig.php';

require_once dirname(dirname(APP_ROOT)).'/P2H.php';

P2H::init($P2HConfig);

//var_dump(P2H::getVars());exit;
unset($P2HConfig);
?>