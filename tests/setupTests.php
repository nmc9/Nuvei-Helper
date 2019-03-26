<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Require this in any files we are goping to be using composer for */
require(__DIR__ . '/../vendor/autoload.php');

use \Nuvei\Nuvei;


//'live' for live environment
//'test' for test environment
$terminal_id = "33001";
$secret = "SandboxSecret001";

$terminal = Nuvei::makeTerminalObject($terminal_id,$secret);
$nuvei = new Nuvei($terminal,'test');
