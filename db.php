<?php
// db.php - DB connection shared by other files
$DB_HOST = 'localhost';
$DB_USER = 'ud89fw4spumtd';
$DB_PASS = 'dpnpg9ge2uey';
$DB_NAME = 'db5t9uq9yggxj1';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("DB connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
?>
