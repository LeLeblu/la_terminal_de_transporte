<?php
require __DIR__ . "/asientos.php"; // incluye conexion + helpers

// 1) Datos del pasajero
$nombre  = trim($_POST["bf-nombre"] ?? "");
$doc     = trim($_POST["bf-doc"] ?? "");
$email   = trim($_POST["bf-email"] ?? "");
$tel     = trim($_POST["bf-tel"] ?? "");
$pago    = trim($_POST["bf-pago"] ?? "Sin definir");
$contacto = $tel ?: $email;

// 2) Datos del viaje (desde hidden JSON)
$bf = json_decode($_POST["bf_data"] ?? "{}", true);
if (!is_array($bf)) $bf = [];
$empresaNombre = $bf["empresa"] ?? "";
$tipo          = $bf["tipo"] ?? "";
$origen        = $bf["origen"] ?? "";
$destino       = $bf["destino"] ?? "";
$fecha         = $bf["fecha"] ?? date("Y-m-d");
$horario       = $bf["horario"] ?? "";
$sillas        = array_filter(explode(",", $bf["sillas"] ?? ""));
$costoUnit     = (int)($bf["costo"] ?? 0);
$total         = (int)($bf["total"] ?? 0);
$cantidad      = max(1, count($sillas));

// 3) Resolver empresa_id y ruta_id
$empresa_id = null;
$ruta_id = null;
$st = $cn->prepare("SELECT id FROM empresas WHERE nombre=? LIMIT 1");
$st->bind_param("s", $empresaNombre);
$st->execute();
$st->bind_result($empresa_id);
$st->fetch();
$st->close();

if ($empresa_id) {
    $st = $cn->prepare("SELECT id, tipo_vehiculo FROM rutas WHERE empresa_id=? AND origen=? AND destino=? AND horario=? LIMIT 1");
    $st->bind_param("isss", $empresa_id, $origen, $destino, $horario);
    $st->execute();
    $st->bind_result($ruta_id, $tipoRuta);
    $st->fetch();
    $st->close();
    if (!$tipo && $tipoRuta) $tipo = $tipoRuta; // respaldo
}

// 4) Inicializar asientos del viaje (si no existe la malla) y ocupar seleccionados
$okAsientos = true;
if ($ruta_id) {
    inicializarAsientosSiNoExisten($cn, $ruta_id, $fecha, $horario, $tipo);
    if ($cantidad > 0) $okAsientos = ocuparAsientos($cn, $ruta_id, $fecha, $horario, $sillas);
}

// 5) Insertar ticket si los asientos están OK
$err = "";
if ($okAsientos) {
    $stmt = $cn->prepare("INSERT INTO tickets
    (empresa_id, ruta_id, tipo_vehiculo, fecha, horario, origen, destino, sillas, cantidad, costo_unitario, total,
     cliente_nombre, cliente_cedula, cliente_contacto)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $sillasStr = implode(",", $sillas);
    $stmt->bind_param(
        "iissssssiiisss",
        $empresa_id,
        $ruta_id,
        $tipo,
        $fecha,
        $horario,
        $origen,
        $destino,
        $sillasStr,
        $cantidad,
        $costoUnit,
        $total,
        $nombre,
        $doc,
        $contacto
    );
    $ok = $stmt->execute();
    $insert_id = $ok ? $stmt->insert_id : 0;
    $err = $ok ? "" : $stmt->error;
    $stmt->close();
} else {
    $ok = false;
    $insert_id = 0;
    $err = "Alguna de las sillas ya fue ocupada. Actualiza y vuelve a intentar.";
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
                <p><a class="menu-link" href="../../navigation/gestion/tickets/index.php">Ir a gestión de tickets</a></p>
            <?php else: ?>
                <h2>No se pudo completar la compra</h2>
                <p><?php echo htmlspecialchars($err); ?></p>
                <p><a class="menu-link" href="javascript:history.back()">Volver</a></p>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>