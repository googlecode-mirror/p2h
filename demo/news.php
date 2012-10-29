<?php
require_once './bootstrap.php';

P2H::init(); 

$var = 'news';
include './templates/news.html';
P2H::toHTML();
?>