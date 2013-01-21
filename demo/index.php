<?php
require_once './bootstrap.php';
P2H::init();
/*
echo P2H::RW('news/news.php?id=3', time());exit;

echo P2H::RW('news/it/news.php?id=3&pag=0&cid=0&gid=2', time());
echo P2H::RW('./news/news.php?id=3&gid=1&pag=11', time());
exit;

echo P2HPath::RWPath('news/news.php?id=3&gid=1&pag=11', time());echo "<br />";
echo P2HPath::RWPath('index.php?pag=3');echo "<br />";
echo P2HPath::RWPath('news/news.php?id=3', time());echo "<br />";
echo P2HPath::RWPath('news/it/news.php?id=3&cid=2&pag=1', time());echo "<br />";




echo P2H::RWURL(NEWS_ROOT.'news.php?id=3', time());echo "<br />";
echo P2H::RWURL(NEWS_ROOT.'index.php?pag=3', time());echo "<br />";
echo P2H::RWURL('news/it/news.php?id=3&pag=0&cid=0&gid=2', time());
echo "<hr>";

echo P2HPath::RWPath('index.php?pag=3', time());
echo "<br />";
echo P2HPath::RWPath('./news/news.php?id=3', time());echo "<br />";
echo P2HPath::RWPath('news/index.php?pag=3', time());echo "<br />";
echo P2HPath::RWPath('news/it/news.php?id=3&pag=0&cid=0&gid=2', time());
*/

//P2H::check($id>0); //检测无效不更新
$time = date('Y-m-d H:i:s');

include './templates/index.html';

P2H::toHTML();