<?php

require('./dl.php');

$dl = new Dll();
$dl->run('delete', $_GET);

// params
// 
