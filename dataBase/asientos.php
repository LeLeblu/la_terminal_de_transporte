<?php
// dataBase/asientos.php
// Incluye conexión
require __DIR__ . "/conexion.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

function obtenerPlantillasPorTipo(mysqli $cn, string $tipo): array {
  $st = $cn->prepare("SELECT piso, filas, columnas FROM plantillas_asientos WHERE tipo_vehiculo=? ORDER BY piso");
  $st->bind_param("s", $tipo);
  $st->execute();
  $st->bind_result($piso, $filas, $columnas);
  $rows = [];
  while ($st->fetch()) {
    $rows[] = ["piso" => (int)$piso, "filas" => (int)$filas, "columnas" => (int)$columnas];
  }
  $st->close();
  return $rows;
}


/** Crea los asientos si no existen aun */
function inicializarAsientosSiNoExisten(mysqli $cn, int $ruta_id, string $fecha, string $horario, string $tipo): void {
  // ¿ya existe malla?
  $st = $cn->prepare("SELECT COUNT(*) FROM asientos_viaje WHERE ruta_id=? AND fecha=? AND horario=?");
  $st->bind_param("iss", $ruta_id, $fecha, $horario);
  $st->execute(); $st->bind_result($c); $st->fetch(); $st->close();
  if ((int)$c > 0) return;

  $plantillas = obtenerPlantillasPorTipo($cn, $tipo);
  foreach ($plantillas as $p) {
    $piso = (int)$p["piso"]; $filas = (int)$p["filas"]; $cols = (int)$p["columnas"];
    $total = $filas * $cols;
    for ($n = 1; $n <= $total; $n++) {
      // ~20% ocupados de ejemplo (igual que tu JS)
      $estado = ($n % 5 === 0) ? "OCUPADO" : "DISPONIBLE";
      $st2 = $cn->prepare("INSERT INTO asientos_viaje (ruta_id, fecha, horario, piso, asiento_numero, estado) VALUES (?,?,?,?,?,?)");
      $st2->bind_param("isssis", $ruta_id, $fecha, $horario, $piso, $n, $estado);
      $st2->execute(); $st2->close();
    }
  }
}

function ocuparAsientos(mysqli $cn, int $ruta_id, string $fecha, string $horario, array $asientos): bool {
  foreach ($asientos as $seat) {
    $piso = 1; $num = 0;
    if (preg_match('/Piso\s*(\d+)\s*(\d+)/i', $seat, $m)) {
      $piso = (int)$m[1]; $num = (int)$m[2];
    } else {
      $num = (int)$seat;
    }
    $st = $cn->prepare("UPDATE asientos_viaje SET estado='OCUPADO'
                        WHERE ruta_id=? AND fecha=? AND horario=? AND piso=? AND asiento_numero=? AND estado='DISPONIBLE'");
    $st->bind_param("issii", $ruta_id, $fecha, $horario, $piso, $num);
    $st->execute();
    if ($st->affected_rows === 0) { $st->close(); return false; } 
    $st->close();
  }
  return true;
}

/** Libera asientos */
function liberarAsientos(mysqli $cn, int $ruta_id, string $fecha, string $horario, array $asientos): void {
  foreach ($asientos as $seat) {
    $piso = 1; $num = 0;
    if (preg_match('/Piso\s*(\d+)\s*(\d+)/i', $seat, $m)) {
      $piso = (int)$m[1]; $num = (int)$m[2];
    } else {
      $num = (int)$seat;
    }
    $st = $cn->prepare("UPDATE asientos_viaje SET estado='DISPONIBLE'
                        WHERE ruta_id=? AND fecha=? AND horario=? AND piso=? AND asiento_numero=?");
    $st->bind_param("issii", $ruta_id, $fecha, $horario, $piso, $num);
    $st->execute(); $st->close();
  }
}
