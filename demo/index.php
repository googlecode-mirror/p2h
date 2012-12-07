<?php
require_once './bootstrap.php';
P2H::init();

//P2H::RWURL('index.php?pag=3', time());
//P2H::RWURL('news/news.php?id=3', time());
//P2H::RWURL('news/index.php?pag=3', time());
P2H::RWURL('news/it/news.php?id=3&pag=0&cid=0&gid=2', time());
$id = 3;
P2H::check($id>0); //检测无效不更新
$time = date('Y-m-d H:i:s');

include './templates/index.html';

P2H::toHTML();