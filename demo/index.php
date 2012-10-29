<?php
require_once './bootstrap.php';

P2H::init(); 

$var = 'index';
include './templates/index.html';
P2H::toHTML();
?>