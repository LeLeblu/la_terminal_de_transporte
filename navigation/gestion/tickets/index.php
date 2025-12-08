<?php
require __DIR__ . "/../../../dataBase/conexion.php";

/* Insertar rápido  */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $empresa_id = intval($_POST["empresa_id"] ?? 0);
  $tipo = trim($_POST["tipo_vehiculo"] ?? "TAXI");
  $fecha = $_POST["fecha"] ?? date("Y-m-d");
  $horario = trim($_POST["horario"] ?? "00:00");
  $origen = trim($_POST["origen"] ?? "");
  $destino = trim($_POST["destino"] ?? "");
  $sillas = trim($_POST["sillas"] ?? "");
  $cantidad = max(1, intval($_POST["cantidad"] ?? 1));
  $costo = intval($_POST["costo_unitario"] ?? 0);
  $total = intval($_POST["total"] ?? ($costo * $cantidad));
  $nombre = trim($_POST["cliente_nombre"] ?? "");
  $cedula = trim($_POST["cliente_cedula"] ?? "");
  $contacto = trim($_POST["cliente_contacto"] ?? "");

  $stmt = $cn->prepare("INSERT INTO tickets
    (empresa_id, tipo_vehiculo, fecha, horario, origen, destino, sillas, cantidad, costo_unitario, total,
     cliente_nombre, cliente_cedula, cliente_contacto)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $stmt->bind_param(
    "issssssiissss",
    $empresa_id,
    $tipo,
    $fecha,
    $horario,
    $origen,
    $destino,
    $sillas,
    $cantidad,
    $costo,
    $total,
    $nombre,
    $cedula,
    $contacto
  );
  $stmt->execute();
  $stmt->close();
  header("Location: index.php?ok=1");
  exit;
}

/* Datos para combos */
$empresas = $cn->query("SELECT id, nombre FROM empresas ORDER BY nombre ASC");

/* Filtro simple por fecha */
$f = $_GET["f"] ?? "";
$w = $f ? "WHERE fecha='$f'" : "";
$tickets = $cn->query("SELECT t.*, e.nombre AS empresa
                       FROM tickets t
                       JOIN empresas e ON e.id=t.empresa_id
                       $w
                       ORDER BY t.creado_en DESC");
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>Gestión de Tickets</title>
  <link rel="stylesheet" href="../../../styles/styles.css" />
</head>

<body>
  <header class="site-header">
    <div class="container header-bar">
      <a href="../../../index.html" class="brand">
        <img src="../../../images/Logo.png" alt="Logo de la Terminal" class="logo" />
        <span class="site-title">La Terminal</span>
      </a>
      <nav class="site-nav" aria-label="Navegación principal">
        <ul class="menu">
          <li><a href="../../empresas/">Empresas</a></li>
          <li>
            <a href="../../vehiculos/">Vehículos</a>
          </li>
          <li>
            <a href="../../rutas/">Rutas y Horarios</a>
          </li>
          <li><a href="../../compra-tickets/">Compra de Tiquetes</a></li>
          <li><a href="../../gestion/tickets/" class="is-active" aria-current="page">Gestión de Tiquetes</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container" style="padding:24px 0;">
    <section class="section">
      <h2>Gestión de Tickets</h2>
      <form action="" method="get" class="quote-search" style="margin-bottom:12px;">
        <label>Filtrar por fecha
          <input type="date" name="f" value="<?php echo htmlspecialchars($f); ?>" />
        </label>
        <button type="submit">Aplicar</button>
        <a class="menu-link" href="index.php">Quitar filtro</a>
      </form>

      <div class="results" style="grid-template-columns:1fr;">
        <table class="routes-table" style="width:100%;">
          <thead>
            <tr>
              <th>#</th>
              <th>Empresa</th>
              <th>Tipo</th>
              <th>Ruta</th>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Sillas</th>
              <th>Cant</th>
              <th>Total</th>
              <th>Cliente</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $tickets->fetch_assoc()): ?>
              <tr>
                <td><?php echo $r["id"]; ?></td>
                <td><?php echo htmlspecialchars($r["empresa"]); ?></td>
                <td><?php echo htmlspecialchars($r["tipo_vehiculo"]); ?></td>
                <td><?php echo htmlspecialchars($r["origen"] . " → " . $r["destino"]); ?></td>
                <td><?php echo htmlspecialchars($r["fecha"]); ?></td>
                <td><?php echo htmlspecialchars($r["horario"]); ?></td>
                <td><?php echo htmlspecialchars($r["sillas"]); ?></td>
                <td><?php echo (int) $r["cantidad"]; ?></td>
                <td>$<?php echo number_format($r["total"], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($r["cliente_nombre"]); ?></td>
                <td>
                  <a class="menu-link" href="editar.php?id=<?php echo $r['id']; ?>">Editar</a> |
                  <a class="menu-link" href="../../../dataBase/eliminar-ticket.php?id=<?php echo $r['id']; ?>"
                    onclick="return confirm('¿Eliminar el ticket #<?php echo $r['id']; ?>?');">Eliminar</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section">
      <h3>Agregar nuevo ticket</h3>
      <form method="post" class="grid-2">
        <label>Empresa
          <select name="empresa_id" required>
            <option value="">Seleccione</option>
            <?php while ($e = $empresas->fetch_assoc()): ?>
              <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option>
            <?php endwhile; ?>
          </select>
        </label>
        <label>Tipo de vehículo
          <select name="tipo_vehiculo">
            <option>TAXI</option>
            <option>AEROVAN</option>
            <option>BUS_1PISO</option>
            <option>BUS_2PISOS</option>
          </select>
        </label>
        <label>Origen <input name="origen" required /></label>
        <label>Destino <input name="destino" required /></label>
        <label>Fecha <input type="date" name="fecha" required /></label>
        <label>Hora <input name="horario" placeholder="08:00 am" required /></label>
        <label>Sillas <input name="sillas" placeholder="1,2 o Piso 1 10" /></label>
        <label>Cantidad <input type="number" name="cantidad" min="1" value="1" /></label>
        <label>Costo unitario <input type="number" name="costo_unitario" value="0" /></label>
        <label>Total <input type="number" name="total" value="0" /></label>
        <label>Nombre cliente <input name="cliente_nombre" required /></label>
        <label>Cédula <input name="cliente_cedula" required /></label>
        <label class="span-2">Contacto (tel o correo) <input name="cliente_contacto" required /></label>
        <button class="span-2" type="submit">Guardar ticket</button>
      </form>
    </section>
  </main>
  <footer class="site-footer">
    <div class="container">
      <p>&copy; 2025 Terminal de Transporte. Todos los derechos reservados.</p>
    </div>
  </footer>
</body>

</html>