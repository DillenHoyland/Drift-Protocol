<?php
// Include enum classes before session start so stored objects can be deserialised
set_include_path(dirname(__DIR__) . '/includes');
include_once 'RiskProfileEnum.php';
include_once 'StatsEnum.php';
include_once 'ModifiersEnum.php';

if(session_status() === PHP_SESSION_NONE) {
    session_name("drift-protocol");
	session_start();
}
if (!isset($_SESSION["user_id"])) {
    $_SESSION["user_id"] = 1;
}
if (!isset($_SESSION["session_id"])) {
    $_SESSION["session_id"] = 1;
}
