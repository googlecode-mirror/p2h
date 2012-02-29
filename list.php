<?php
require_once './config.php';
require_once './P2H/P2H.php';
load_file('function/string');
exit;
P2H::init();

$gid = get_id('gid');
$cid = get_id('cid');

include './templates/list.html';
P2H::run();
?>