<?php
require_once './P2H.php';
P2H::init();

echo "id:{$_REQUEST['id']}"; //imitate fetch database data

P2H::to_html();
?>