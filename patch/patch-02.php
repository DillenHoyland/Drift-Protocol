<?php
include '../htdocs/siteincludes/functions.php';

$dp_sql = file_get_contents('../htdocs/db/drift_protocol.sql');
if(!$dp_sql) exit("Error: unable to access file drift_protocol.sql");

$dbc = dbOpen();

$query = "drop database if exists `drift_protocol`;create database `drift_protocol`;use `drift_protocol`;";
$query .= $dp_sql;


try {
    if (!$dbc->multi_query($query)) throw new Exception($dbc->error);

    do {
        if ($result = $dbc->store_result()) $result->free();
        if ($dbc->errno) throw new Exception($dbc->error);
    } 
    while ($dbc->more_results() && $dbc->next_result());

    echo "DB update successful.";
} 
catch (Exception $errors) {
    exit("DB update failed. Error log: " . $errors->getMessage());
}