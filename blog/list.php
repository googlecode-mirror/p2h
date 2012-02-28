<?php
require_once '../function.php';
require_once '../P2H/P2H.php';

P2H::init();

$gid = get_id('gid');
$cid = get_id('cid');

include './templates/list.html';
P2H::run();
?>