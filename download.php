<?php

require('./dl.php');

$dl = new Dll();

/**
 * param array ['id' => p8GdpjsGmfg]
 * return json
 */

$dl->run('download', $_GET);