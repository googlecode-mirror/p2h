<?php
require_once './bootstrap.php';
P2H::init();

$time = date('Y-m-d H:i:s');
//$var = '如果是字符串 不更新哦亲';
//P2H::checkVar($var);
include './templates/list.html';
P2H::toHTML();