<?php

require('./dl.php');

$dl = new Dll();
$dl->run('upload', $_GET);