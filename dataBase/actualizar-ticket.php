<?php
require __DIR__ . "/conexion.php";
ini_set('display_errors', 1); error_reporting(E_ALL);

// 1) Tomar datos del POST
$id       = (int)($_POST["id"] ?? 0);
$fecha    = $_POST["fecha"] ?? date("Y-m-d");
$horario  = trim($_POST["horario"] ?? "");

// sillas puede venir como string "1,2" o como array ["1","2"]
$sillasRaw = $_POST["sillas"] ?? "";
if (is_array($sillasRaw)) {
  $sillas = implode(",", array_values(array_filter(array_map('trim', $sillasRaw))));
} else {
  $sillas = trim((string)$sillasRaw);
}

$cantidad = max(1, (int)($_POST["cantidad"] ?? 1));
$costo    = (int)($_POST["costo_unitario"] ?? 0);
$total    = (int)($_POST["total"] ?? 0);
$nombre   = trim($_POST["cliente_nombre"] ?? "");
$cedula   = trim($_POST["cliente_cedula"] ?? "");
$contacto = trim($_POST["cliente_contacto"] ?? "");

// Si el total no viene calculado, lo recalculamos simple
if ($total <= 0) { $total = $costo * $cantidad; }

// 2) Validaciones básicas
if ($id <= 0) {
  die("ID de ticket inválido.");
}
if (!$fecha || !$horario) {
  die("Faltan fecha u horario.");
}

// 3) Preparar UPDATE
$sql = "UPDATE tickets
        SET fecha=?, horario=?, sillas=?, cantidad=?, costo_unitario=?, total=?, 
            cliente_nombre=?, cliente_cedula=?, cliente_contacto=?
        WHERE id=?";

$st = $cn->prepare($sql);
if (!$st) {
  die("Error en prepare(): " . $cn->error);
}

// Tipos: s(fecha) s(horario) s(sillas) i(cantidad) i(costo_unitario) i(total) s(nombre) s(cedula) s(contacto) i(id)
$st->bind_param("sssiiisssi", $fecha, $horario, $sillas, $cantidad, $costo, $total, $nombre, $cedula, $contacto, $id);

// 4) Ejecutar
$ok = $st->execute();
if (!$ok) {
  die("Error al actualizar: " . $st->error);
}
$st->close();
$dest = "../navigation/gestion/tickets/index.php?upd=1";

header("Location: $dest");
exit;
