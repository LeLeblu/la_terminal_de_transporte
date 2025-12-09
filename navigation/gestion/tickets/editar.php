<?php
require __DIR__ . "/../../../dataBase/conexion.php";
$id = intval($_GET["id"] ?? 0);
$st = $cn->prepare("SELECT * FROM tickets WHERE id=?");
$st->bind_param("i", $id); 
$st->execute();
$t = $st->get_result()->fetch_assoc(); 
$st->close();
if (!$t) { die("Ticket no encontrado"); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>

  <!-- Mejora de usabilidad móvil (no es SEO directo, pero ayuda a la experiencia):
       hace que la página se vea bien en pantallas pequeñas -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- SEO: título descriptivo con palabras clave de gestión/edición de tiquetes -->
  <title>Editar Ticket #<?php echo $id; ?> | Gestión de Tiquetes - La Terminal</title>

  <!-- SEO: meta descripción que explica claramente qué se hace en esta página.
       Aunque sea una sección interna, mantiene la misma estrategia en todo el sitio. -->
  <meta
    name="description"
    content="Editar la información del ticket #<?php echo $id; ?> en el módulo de gestión de tiquetes de La Terminal: fecha, hora, sillas, valor y datos del cliente."
  />

  <link rel="stylesheet" href="../../../styles/styles.css"/>
</head>
<body>
  <header class="site-header">
    <div class="container header-bar">
      <a href="../../../index.html" class="brand">
        <img src="../../../images/logo.png" alt="Logo de la Terminal" class="logo" />
        <span class="site-title">La Terminal</span>
      </a>
      <nav class="site-nav" aria-label="Navegación principal">
        <ul class="menu">
          <li><a href="../../empresas/">Empresas</a></li>
          <li><a href="../../vehiculos/">Vehículos</a></li>
          <li><a href="../../rutas/">Rutas y Horarios</a></li>
          <li><a href="../../compra-tickets/">Compra de Tiquetes</a></li>
          <li><a href="../../gestion/tickets/" class="is-active" aria-current="page">Gestión de Tiquetes</a></li>
        </ul>
      </nav>
    </div>
  </header>

<main class="container" style="padding:24px 0;">
  <section class="section">
    <h2>Editar Ticket #<?php echo $id; ?></h2>
    <form action="../../../dataBase/actualizar-ticket.php" method="post" class="grid-2">
      <input type="hidden" name="id" value="<?php echo $id; ?>"/>
      <label>Fecha <input type="date" name="fecha" value="<?php echo htmlspecialchars($t['fecha']); ?>" required/></label>
      <label>Hora <input name="horario" value="<?php echo htmlspecialchars($t['horario']); ?>" required/></label>
      <label>Sillas <input name="sillas" value="<?php echo htmlspecialchars($t['sillas']); ?>"/></label>
      <label>Cantidad <input type="number" name="cantidad" min="1" value="<?php echo (int)$t['cantidad']; ?>"/></label>
      <label>Costo unitario <input type="number" name="costo_unitario" value="<?php echo (int)$t['costo_unitario']; ?>"/></label>
      <label>Total <input type="number" name="total" value="<?php echo (int)$t['total']; ?>"/></label>
      <label>Nombre cliente <input name="cliente_nombre" value="<?php echo htmlspecialchars($t['cliente_nombre']); ?>" required/></label>
      <label>Cédula <input name="cliente_cedula" value="<?php echo htmlspecialchars($t['cliente_cedula']); ?>" required/></label>
      <label class="span-2">Contacto <input name="cliente_contacto" value="<?php echo htmlspecialchars($t['cliente_contacto']); ?>" required/></label>
      <button class="span-2" type="submit">Actualizar</button>
    </form>
    <p><a class="menu-link" href="index.php">Volver</a></p>
  </section>
</main>

<footer class="site-footer">
  <div class="container">
    <p>&copy; 2025 Terminal de Transporte. Todos los derechos reservados.</p>
  </div>
</footer>
</body>
</html>

