<?php
require __DIR__ . "/asientos.php";
$id = (int)($_GET["id"] ?? 0);
if ($id > 0) {
  // obtener datos para liberar
  $st = $cn->prepare("SELECT ruta_id, fecha, horario, sillas FROM tickets WHERE id=?");
  $st->bind_param("i",$id); $st->execute();
  $st->bind_result($ruta_id, $fecha, $horario, $sillasStr);
  $st->fetch(); $st->close();

  if ($ruta_id && $sillasStr) {
    $sillas = array_filter(explode(",", $sillasStr));
    liberarAsientos($cn, (int)$ruta_id, $fecha, $horario, $sillas);
  }
  // borrar ticket
  $st2 = $cn->prepare("DELETE FROM tickets WHERE id=?");
  $st2->bind_param("i",$id); $st2->execute(); $st2->close();
}
header("Location: ../navigation/gestion/tickets/index.php?del=1");
