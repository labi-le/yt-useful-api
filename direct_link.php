<?php

require('./dl.php');

$dl = new Dll();
$dl->run('direct_link', $_GET);
