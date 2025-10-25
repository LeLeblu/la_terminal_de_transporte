<?php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "root";
$DB_NAME = "terminal";

$cn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($cn->connect_errno) {
  die("Error de conexión MySQL: " . $cn->connect_error);
}
$cn->set_charset("utf8mb4");
?>
