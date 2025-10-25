<?php
require __DIR__ . "../conexion.php";

/* Devuelve filas/columnas por piso para un tipo */
function obtenerPlantillasPorTipo(mysqli $cn, string $tipo): array {
  $st = $cn->prepare("SELECT piso, filas, columnas FROM plantillas_asientos WHERE tipo_vehiculo=? ORDER BY piso");
  $st->bind_param("s", $tipo); $st->execute();
  $res = $st->get_result(); $rows=[]; while($r=$res->fetch_assoc()) $rows[]=$r; $st->close();
  return $rows;
}

/* Crea todos los asientos de un viaje si aún no existen (algunos ocupados aleatoriamente para demo) */
function inicializarAsientosSiNoExisten(mysqli $cn, int $ruta_id, string $fecha, string $horario, string $tipo): void {
  $st = $cn->prepare("SELECT COUNT(*) FROM asientos_viaje WHERE ruta_id=? AND fecha=? AND horario=?");
  $st->bind_param("iss", $ruta_id, $fecha, $horario);
  $st->execute(); $st->bind_result($cnt); $st->fetch(); $st->close();
  if ($cnt > 0) return;

  $plantillas = obtenerPlantillasPorTipo($cn, $tipo);
  foreach ($plantillas as $p) {
    $piso = (int)$p["piso"]; $filas = (int)$p["filas"]; $cols = (int)$p["columnas"];
    $total = $filas * $cols;
    for ($n=1; $n <= $total; $n++) {
      $estado = ($n % 5 === 0) ? "OCUPADO" : "DISPONIBLE"; // ~20% ocupado para ejemplo
      $st2 = $cn->prepare("INSERT INTO asientos_viaje (ruta_id, fecha, horario, piso, asiento_numero, estado) VALUES (?,?,?,?,?,?)");
      $st2->bind_param("isssis", $ruta_id, $fecha, $horario, $piso, $n, $estado);
      $st2->execute(); $st2->close();
    }
  }
}

/* Marca asientos como OCUPADO; devuelve true si todos se pudieron ocupar */
function ocuparAsientos(mysqli $cn, int $ruta_id, string $fecha, string $horario, array $asientos): bool {
  foreach ($asientos as $seat) {
    $piso = 1; $num = 0;
    if (preg_match('/Piso\s*(\d+)\s*(\d+)/i', $seat, $m)) { $piso = (int)$m[1]; $num = (int)$m[2]; }
    else { $num = (int)$seat; }
    $st = $cn->prepare("UPDATE asientos_viaje SET estado='OCUPADO'
                        WHERE ruta_id=? AND fecha=? AND horario=? AND piso=? AND asiento_numero=? AND estado='DISPONIBLE'");
    $st->bind_param("issii", $ruta_id, $fecha, $horario, $piso, $num);
    $st->execute();
    if ($st->affected_rows === 0) { $st->close(); return false; } // ya ocupado
    $st->close();
  }
  return true;
}

/* Libera asientos (al borrar ticket) */
function liberarAsientos(mysqli $cn, int $ruta_id, string $fecha, string $horario, array $asientos): void {
  foreach ($asientos as $seat) {
    $piso = 1; $num = 0;
    if (preg_match('/Piso\s*(\d+)\s*(\d+)/i', $seat, $m)) { $piso = (int)$m[1]; $num = (int)$m[2]; }
    else { $num = (int)$seat; }
    $st = $cn->prepare("UPDATE asientos_viaje SET estado='DISPONIBLE'
                        WHERE ruta_id=? AND fecha=? AND horario=? AND piso=? AND asiento_numero=?");
    $st->bind_param("issii", $ruta_id, $fecha, $horario, $piso, $num);
    $st->execute(); $st->close();
  }
}
