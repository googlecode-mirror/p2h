<?php
require_once './P2HInit.php';
$time = date('Y-m-d H:i:s');
$var = array('sdf');
P2H::checkVar($var);
include './templates/list.html';
P2H::toHTML();