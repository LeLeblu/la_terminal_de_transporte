<?php
require __DIR__ . "/conexion.php";
$q = $cn->query("SELECT COUNT(*) AS total_empresas FROM empresas");
$row = $q ? $q->fetch_assoc() : ["total_empresas"=>0];
echo "Conexión OK. Empresas registradas: " . $row["total_empresas"];
