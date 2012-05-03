<?php
require_once './P2HInit.php';
//var_dump(P2H::getVars());exit;
$time = date('Y-m-d H:i:s');

include './templates/index.html';
P2H::toHTML();