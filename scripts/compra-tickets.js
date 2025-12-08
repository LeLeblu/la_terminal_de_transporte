/* Compra de Tiquetes — Precarga desde la URL + envío al PHP */
(() => {
  "use strict";

  const money = n => "$" + (Math.round(n) || 0).toLocaleString("es-CO");
  const formatISOtoDMY = iso => {
    if (!iso) return "—";
    const [y, m, d] = iso.split("-");
    return `${d}/${m}/${y}`;
  };
  const tipoLabel = t => (
    { TAXI: "Taxi", AEROVAN: "Aerovan", BUS_1PISO: "Bus (1 piso)", BUS_2PISOS: "Bus (2 pisos)" }[t] || t
  );

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

  const hidden = document.getElementById("bf-data");
  if (hidden) hidden.value = JSON.stringify(data);

  // Envío del formulario  
  const form = document.getElementById("buy-form");
  if (form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    form.addEventListener("submit", () => {
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Procesando...";
      }
      
    });
  }
})();
