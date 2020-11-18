<?php

require('./dl.php');

$dl = new Dll();
$var = $dl->run('download', $_GET);