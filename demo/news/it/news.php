<?php
require_once '../../bootstrap.php';

P2H::init(); 

$var = 'MEIZU MX2即将上市';

include './templates/news.html';
P2H::toHTML();
?>