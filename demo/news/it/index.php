<?php
require_once '../../bootstrap.php';
P2H::init(); 

$var = 'it news';

include './templates/index.html';
P2H::toHTML();
?>