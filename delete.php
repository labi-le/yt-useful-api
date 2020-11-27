<?php

require('./dl.php');

$dl = new Dll();

/**
 * param ['filename' = 'filename.mp4']
 * return bool
 */

$dl->run('delete', $_GET);
