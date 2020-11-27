<?php

require('./dl.php');

$dl = new Dll();

/**
 * param array 'author' => string,
 *             'title' => string,
 *             'filename' => string,
 *             'description' => string,
 *             'album_id' => ?int
 * 
 * return json
 */
$dl->run('upload', $_GET);
