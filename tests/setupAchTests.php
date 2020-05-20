<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Require this in any files we are goping to be using composer for */
require(__DIR__ . '/../vendor/autoload.php');

use \Nuvei\Nuvei;


//'live' for live environment
//'test' for test environment
$terminal_id = "1064167";
$secret = "Tech#n0l0gy";

$terminal = Nuvei::makeTerminalObject($terminal_id,$secret);
$nuvei = new Nuvei($terminal,'test');
