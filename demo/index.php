<?php

require_once './bootstrap.php';
P2H::init();

$time = date('Y-m-d H:i:s');

include './templates/index.html';

P2H::toHTML();