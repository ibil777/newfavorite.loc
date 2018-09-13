<?php
error_reporting(E_ALL);

define('prdb', 'newnf_');
define('SECRET_CODE', 'aSsLA5fV3&e%^$');

$db_host = "localhost";
$db_name = "newfavoritenew";
$db_charset = "utf8";
$db_user = "root";
$db_pass = "";
$db_time_zone = "+03:00"; // summer

$dsn = "mysql:host=".$db_host.";dbname=".$db_name.";charset=".$db_charset;
$opt = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$db_charset."",
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
$pdo = new PDO($dsn, $db_user, $db_pass, $opt);

$query = "SET time_zone = '".$db_time_zone."'";
$pdo->exec($query);


?>