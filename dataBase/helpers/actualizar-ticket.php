<?php
require __DIR__ . "../conexion.php";
$id = intval($_POST["id"] ?? 0);
$fecha = $_POST["fecha"] ?? date("Y-m-d");
$horario = trim($_POST["horario"] ?? "");
$sillas = trim($_POST["sillas"] ?? "");
$cantidad = max(1, intval($_POST["cantidad"] ?? 1));
$costo = intval($_POST["costo_unitario"] ?? 0);
$total = intval($_POST["total"] ?? 0);
$nombre = trim($_POST["cliente_nombre"] ?? "");
$cedula = trim($_POST["cliente_cedula"] ?? "");
$contacto = trim($_POST["cliente_contacto"] ?? "");

$st = $cn->prepare("UPDATE tickets SET fecha=?, horario=?, sillas=?, cantidad=?, costo_unitario=?, total=?, cliente_nombre=?, cliente_cedula=?, cliente_contacto=? WHERE id=?");
$st->bind_param("sssiiisssi", $fecha, $horario, $sillas, $cantidad, $costo, $total, $nombre, $cedula, $contacto, $id);
$ok = $st->execute(); $st->close();

header("Location: ../../navigation/gestion/tickets/index.php?upd=".($ok?1:0));
