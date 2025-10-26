<?php
require __DIR__ . "/asientos.php"; // incluye conexion + helpers

ini_set('display_errors', 1); error_reporting(E_ALL);

// 1) Datos del pasajero
$nombre   = trim($_POST["bf-nombre"] ?? "");
$doc      = trim($_POST["bf-doc"] ?? "");
$email    = trim($_POST["bf-email"] ?? "");
$tel      = trim($_POST["bf-tel"] ?? "");
$pago     = trim($_POST["bf-pago"] ?? "Sin definir");
$contacto = $tel ?: $email;

// 2) Datos del viaje (desde hidden JSON)
$raw  = $_POST["bf_data"] ?? "";
$bf   = (is_string($raw) && $raw !== "") ? json_decode($raw, true) : [];
if (!is_array($bf)) $bf = [];

$empresaNombre = $bf["empresa"]  ?? "";
$tipo          = $bf["tipo"]     ?? "";
$origen        = $bf["origen"]   ?? "";
$destino       = $bf["destino"]  ?? "";
$fecha         = $bf["fecha"]    ?? date("Y-m-d");
$horario       = $bf["horario"]  ?? "";

// --- Normalizar sillas (puede venir como string "1,2" o como array ["1","2"]) ---
$sillasInput = $bf["sillas"] ?? [];
if (is_string($sillasInput)) {
  $sillas = array_values(array_filter(array_map('trim', explode(',', $sillasInput))));
} elseif (is_array($sillasInput)) {
  $sillas = array_values(array_filter(array_map('trim', $sillasInput)));
} else {
  $sillas = [];
}

$costoUnit = (int)($bf["costo"] ?? 0);
$total     = (int)($bf["total"] ?? 0);
if ($total <= 0) $total = $costoUnit * max(1, count($sillas));
$cantidad  = max(1, count($sillas));
$sillasStr = implode(",", $sillas);

// 3) Resolver empresa y ruta
$empresa_id = null; $ruta_id = null; $tipoRuta = null;

$st = $cn->prepare("SELECT id FROM empresas WHERE nombre=? LIMIT 1");
$st->bind_param("s", $empresaNombre);
$st->execute(); $st->bind_result($empresa_id); $st->fetch(); $st->close();

$err = "";
if (!$empresa_id) {
  $err = "La empresa seleccionada no existe en la base de datos.";
}

if (!$err) {
  $st = $cn->prepare("SELECT id, tipo_vehiculo FROM rutas WHERE empresa_id=? AND origen=? AND destino=? AND horario=? LIMIT 1");
  $st->bind_param("isss", $empresa_id, $origen, $destino, $horario);
  $st->execute(); $st->bind_result($ruta_id, $tipoRuta); $st->fetch(); $st->close();

  if (!$ruta_id) {
    $err = "La ruta (empresa/origen/destino/horario) no está registrada en la base de datos.";
  } else {
    if (!$tipo && $tipoRuta) $tipo = $tipoRuta;
  }
}

// 4) Inicializar y ocupar asientos
$ok = false; $insert_id = 0;

if (!$err) {
  inicializarAsientosSiNoExisten($cn, (int)$ruta_id, $fecha, $horario, $tipo ?: "AEROVAN");

  $okAsientos = true;
  if ($cantidad > 0 && count($sillas) > 0) {
    $okAsientos = ocuparAsientos($cn, (int)$ruta_id, $fecha, $horario, $sillas);
  }

  if (!$okAsientos) {
    $err = "Alguna de las sillas que seleccionaste ya fue ocupada. Actualiza y vuelve a intentar.";
  } else {
    // 5) Insertar ticket (OJO: 14 placeholders)
    $stmt = $cn->prepare("INSERT INTO tickets
      (empresa_id, ruta_id, tipo_vehiculo, fecha, horario, origen, destino, sillas, cantidad, costo_unitario, total,
       cliente_nombre, cliente_cedula, cliente_contacto)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
      "iissssssiiisss",
      $empresa_id, $ruta_id, $tipo, $fecha, $horario, $origen, $destino,
      $sillasStr, $cantidad, $costoUnit, $total, $nombre, $doc, $contacto
    );

    $ok = $stmt->execute();
    $insert_id = $ok ? $stmt->insert_id : 0;
    $e = $ok ? "" : $stmt->error;
    $stmt->close();

    if (!$ok) { $err = "No fue posible guardar el ticket. $e"; }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Resultado de compra</title>
  <link rel="stylesheet" href="../styles/styles.css" />
</head>
<body>
  <main class="container" style="padding:24px 0;">
    <section class="section">
      <?php if ($ok): ?>
        <h2>¡Compra guardada!</h2>
        <p>Ticket <strong>#<?php echo $insert_id; ?></strong> registrado correctamente.</p>
        <p>Ruta: <strong><?php echo htmlspecialchars("$origen → $destino"); ?></strong> — <?php echo htmlspecialchars($fecha); ?> <?php echo htmlspecialchars($horario); ?></p>
        <p>Sillas: <strong><?php echo htmlspecialchars($sillasStr ?: "Asiento general"); ?></strong></p>
        <p>Total: <strong>$<?php echo number_format($total, 0, ',', '.'); ?></strong></p>
        <p><a class="menu-link" href="../navigation/gestion/tickets/index.php">Ir a gestión de tickets</a></p>
      <?php else: ?>
        <h2>No se pudo completar la compra</h2>
        <p><?php echo htmlspecialchars($err); ?></p>
        <p><a class="menu-link" href="javascript:history.back()">Volver</a></p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
