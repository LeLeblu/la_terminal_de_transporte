/* Compra de Tiquetes — Precarga desde la URL + envío al PHP */
(() => {
  "use strict";

  // Utilidades básicas para mostrar resumen
  const money = n => "$" + (Math.round(n) || 0).toLocaleString("es-CO");
  const formatISOtoDMY = iso => {
    if (!iso) return "—";
    const [y, m, d] = iso.split("-");
    return `${d}/${m}/${y}`;
  };
  const tipoLabel = t => (
    { TAXI: "Taxi", AEROVAN: "Aerovan", BUS_1PISO: "Bus (1 piso)", BUS_2PISOS: "Bus (2 pisos)" }[t] || t
  );

  // 1) Leer parámetros de la URL
  const q = new URLSearchParams(window.location.search);
  const data = {
    empresa: q.get("empresa") || "",
    tipo:    q.get("tipo")    || "",
    origen:  q.get("origen")  || "",
    destino: q.get("destino") || "",
    fecha:   q.get("fecha")   || "",
    horario: q.get("horario") || "",
    sillas:  (q.get("sillas") || "").split(",").filter(Boolean),
    costo:   Number(q.get("costo") || 0),
    total:   Number(q.get("total") || 0),
  };
  if (!data.total) data.total = data.costo * Math.max(1, data.sillas.length);

  // 2) Pintar el resumen en la UI (si existen esos elementos)
  const $ = sel => document.querySelector(sel);
  const setText = (sel, txt) => { const el = $(sel); if (el) el.textContent = txt; };

  setText("#ts-ruta",     (data.origen && data.destino) ? `${data.origen} → ${data.destino}` : "—");
  setText("#ts-empresa",  data.empresa || "—");
  setText("#ts-tipo",     data.tipo ? `Tipo: ${tipoLabel(data.tipo)}` : "—");
  setText("#ts-fecha",    data.fecha ? formatISOtoDMY(data.fecha) : "—");
  setText("#ts-hora",     data.horario || "—");
  setText("#ts-sillas",   data.sillas.length ? data.sillas.join(", ") : "—");
  setText("#ts-costo",    money(data.costo || 0));
  setText("#ts-total",    money(data.total || 0));

  // 3) Pasar el payload completo al hidden que consume PHP
  const hidden = document.getElementById("bf-data");
  if (hidden) hidden.value = JSON.stringify(data);

  // 4) Envío del formulario (SIN preventDefault). Solo evitamos doble clic.
  const form = document.getElementById("buy-form");
  if (form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    form.addEventListener("submit", () => {
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Procesando...";
      }
      // importante: no usamos preventDefault; el form se va al PHP (crear-ticket.php)
    });
  }
})();
