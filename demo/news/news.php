<?php
require_once '../bootstrap.php';

P2H::init(time()); 

$time = date('Y-m-d H:i:s');
include './templates/news.html';
P2H::toHTML();
?>