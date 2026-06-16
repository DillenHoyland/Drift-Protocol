<?php include './siteincludes/session_init.php'; ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme=<?=(isset($_COOKIE['darkmode']) && $_COOKIE['darkmode'] == "false") ? "light": "dark"?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Compiled CSS -->
    <link rel="stylesheet" href="styles/site_style.css">
    <!-- CSS Overrides -->
    <link rel="stylesheet" href="styles/changes.css">
    <!-- Global JS Functions -->
    <script src="scripts/functions.js"></script>
    <!-- Icons > Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="styles/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="styles/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="styles/icons/favicon-16x16.png">
    <link rel="manifest" href="styles/icons/site.webmanifest">
    <!-- Icons > Fontawesome -->
    <script src="https://kit.fontawesome.com/b89a958fe1.js" crossorigin="anonymous"></script>
    <!-- Title -->
    <title><?=$pageInfo[0]?></title> <!-- echo $page title -->
    <!-- CookieBar Source: https://cookie-bar.eu -->
    <script src="https://cdn.jsdelivr.net/npm/cookie-bar/cookiebar-latest.min.js?always=1&top=1"></script>
</head>
<body class=<?= (isset($_COOKIE['readmode']) && $_COOKIE['readmode'] === 'true') ? '"acc"' : '"def"'?>>

<?php 


if(!isset($pageInfo) || empty($pageInfo)) $pageInfo = array("Drift Protocol"); 

include './siteincludes/functions.php'; 
include './siteincludes/navbar.php'; 
include './siteincludes/login.php'; 
include './siteincludes/register.php'; 
include './siteincludes/sessions.php';

if($_SERVER['REQUEST_METHOD'] === "GET") formHandler('get'); 
if($_SERVER['REQUEST_METHOD'] === "POST") formHandler('post');
?>

