<?php
require __DIR__ . "/conexion.php";
$r = $cn->query("SELECT COUNT(*) c FROM empresas")->fetch_assoc();
echo "Conexión OK. Empresas: " . $r["c"];
