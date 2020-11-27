<?php

require('./dl.php');

$dl = new Dll();

/**
 * param ['id' => 'https://www.youtube.com/watch?v=p8GdpjsGmfg', 'mp4_720']
 * check obj $quality
 * return json
 */

$dl->run('direct_link', $_GET);
