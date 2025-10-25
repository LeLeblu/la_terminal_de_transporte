/* Compra de Tiquetes — Precarga desde la URL + simulación de compra */
(() => {
  "use strict";

  const money = n => "$" + (Math.round(n) || 0).toLocaleString("es-CO");
  const formatISOtoDMY = iso => { if (!iso) return "—"; const [y, m, d] = iso.split("-"); return `${d}/${m}/${y}`; };
  const tipoLabel = t => ({ TAXI: "Taxi", AEROVAN: "Aerovan", BUS_1PISO: "Bus (1 piso)", BUS_2PISOS: "Bus (2 pisos)" }[t] || t);

  // Leer parámetros
  const q = new URLSearchParams(location.search);
  const data = {
    empresa: q.get("empresa") || "",
    tipo: q.get("tipo") || "",
    origen: q.get("origen") || "",
    destino: q.get("destino") || "",
    fecha: q.get("fecha") || "",
    horario: q.get("horario") || "",
    sillas: (q.get("sillas") || "").split(",").filter(Boolean),
    costo: Number(q.get("costo") || 0),
    total: Number(q.get("total") || 0),
  };
  if (!data.total) data.total = data.costo * Math.max(1, data.sillas.length);

  // Pintar resumen
  const $ = sel => document.querySelector(sel);
  $("#ts-ruta").textContent = (data.origen && data.destino) ? `${data.origen} → ${data.destino}` : "—";
  $("#ts-empresa").textContent = data.empresa || "—";
  $("#ts-tipo").textContent = data.tipo ? `Tipo: ${tipoLabel(data.tipo)}` : "—";
  $("#ts-fecha").textContent = data.fecha ? formatISOtoDMY(data.fecha) : "—";
  $("#ts-hora").textContent = data.horario || "—";
  $("#ts-sillas").textContent = data.sillas.length ? data.sillas.join(", ") : "—";
  $("#ts-costo").textContent = money(data.costo || 0);
  $("#ts-total").textContent = money(data.total || 0);

  // Guardar payload en un hidden (útil si luego se envía al backend)
  const hidden = document.getElementById("bf-data");
  hidden.value = JSON.stringify(data);

  // Compra de tickets
  const form = document.getElementById("buy-form");
  const msg = document.getElementById("buy-msg");
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const nombre = document.getElementById("bf-nombre").value.trim();
    const doc = document.getElementById("bf-doc").value.trim();
    const email = document.getElementById("bf-email").value.trim();
    const tel = document.getElementById("bf-tel").value.trim();
    const pago = document.getElementById("bf-pago").value;

    const codigo = "RES-" + Math.random().toString(36).slice(2, 8).toUpperCase();
    msg.style.display = "block";
    msg.innerHTML = `
      <strong>¡Compra realizada con éxtio!</strong><br/>
      Código de reserva: <strong>${codigo}</strong><br/>
      Pasajero: <strong>${nombre}</strong> — Doc: <strong>${doc}</strong><br/>
      Ruta: <strong>${data.origen} → ${data.destino}</strong> | ${formatISOtoDMY(data.fecha)} ${data.horario}<br/>
      Sillas: <strong>${data.sillas.length ? data.sillas.join(", ") : "Asiento general"}</strong><br/>
      Total pagado: <strong>${money(data.total)}</strong><br/>
      Medio de pago: <strong>${pago}</strong>
    `;
    // Desactivar botón
    form.querySelector("button[type=submit]").disabled = true;
  });
})();
