<?php
require_once './P2HConfig.php';

require_once '../../P2H.php';

P2H::init($P2HConfig);

//var_dump(P2H::getVars());exit;
unset($P2HConfig);
?>