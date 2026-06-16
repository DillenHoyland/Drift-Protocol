<?php
include '../htdocs/siteincludes/functions.php';

$dbc = dbOpen();
$dbc->begin_transaction();

$query = array();
array_push($query, "drop table if exists `settings`;");
array_push($query, "create table `settings` (`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `admin_email` varchar(80) NOT NULL, `email_validation` tinyint(1) DEFAULT NULL)");
array_push($query, "insert into `settings` (`id`, `admin_email`, `email_validation`) VALUES (1, 'test1@localhost', 0);");

try {
    foreach($query as $q) {
        if(!$dbc->query($q)) throw new Exception($dbc->error);
    }
    $dbc->commit();
    echo "DB update successful.";
} 

catch (Exception $errors) {
    $dbc->rollback();
    exit("DB update failed. Error log: " . $errors->getMessage());
}