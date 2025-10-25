/* Cotizaciones — Búsqueda + Disponibilidad de sillas por ruta
   - Botón "Ver sillas" por resultado
   - Mapas por tipo: TAXI(2x2), AEROVAN(3x4), BUS_1PISO(10x4), BUS_2PISOS(8x4 x 2)
   - Verde: libre | Rojo: ocupada | Azul: seleccionada
   - "Continuar con la compra" → redirige a Compra de Tiquetes con la selección
*/
(() => {
  "use strict";

  /* ========= Datos base (cada ruta con su TIPO) ========= */
  const RUTAS = [
    { empresa: "Transportico SAS", tipo: "TAXI", origen: "Marinilla", destino: "Medellín", horario: "04:00 am", costo: 100000 },
    { empresa: "Transportico SAS", tipo: "AEROVAN", origen: "Marinilla", destino: "Medellín", horario: "05:00 am", costo: 100000 },
    { empresa: "Transportico SAS", tipo: "TAXI", origen: "Marinilla", destino: "Medellín", horario: "06:00 am", costo: 100000 },
    { empresa: "Transportico SAS", tipo: "AEROVAN", origen: "Marinilla", destino: "Medellín", horario: "08:00 am", costo: 100000 },
    { empresa: "Transportico SAS", tipo: "TAXI", origen: "Marinilla", destino: "Medellín", horario: "09:00 am", costo: 100000 },

    { empresa: "Trans Vanegas", tipo: "BUS_2PISOS", origen: "Marinilla", destino: "Manizales", horario: "07:00 am", costo: 110000 },
    { empresa: "El Dorado", tipo: "BUS_2PISOS", origen: "Marinilla", destino: "Cali", horario: "12:00 pm", costo: 140000 },
    { empresa: "Servi Rutas Ltda.", tipo: "BUS_1PISO", origen: "Marinilla", destino: "Barranquilla", horario: "07:00 pm", costo: 200000 },
    { empresa: "Trans Volver", tipo: "AEROVAN", origen: "Marinilla", destino: "Rionegro", horario: "08:00 pm", costo: 10000 },
    { empresa: "Trans Vanegas", tipo: "BUS_2PISOS", origen: "Marinilla", destino: "Bogotá", horario: "08:30 am", costo: 100000 },

    { empresa: "Transportico SAS", tipo: "TAXI", origen: "Rionegro", destino: "Marinilla", horario: "06:00 am", costo: 10000 },
    { empresa: "Trans Vanegas", tipo: "BUS_2PISOS", origen: "Cali", destino: "Marinilla", horario: "07:00 am", costo: 140000 },
    { empresa: "El Dorado", tipo: "BUS_2PISOS", origen: "Barranquilla", destino: "Marinilla", horario: "12:00 pm", costo: 200000 },
    { empresa: "Servi Rutas Ltda.", tipo: "BUS_1PISO", origen: "Bogotá", destino: "Marinilla", horario: "07:00 pm", costo: 120000 },
    { empresa: "Trans Volver", tipo: "AEROVAN", origen: "Manizales", destino: "Marinilla", horario: "08:00 pm", costo: 80000 },
  ];

  /* ========= Utilidades ========= */
  const money = n => "$" + (Math.round(n) || 0).toLocaleString("es-CO");
  const tipoLabel = t =>
    ({ TAXI: "Taxi", AEROVAN: "Aerovan", BUS_1PISO: "Bus (1 piso)", BUS_2PISOS: "Bus (2 pisos)" }[t] || t);
  const formatISOtoDMY = iso => { if (!iso) return "—"; const [y, m, d] = iso.split("-"); return `${d}/${m}/${y}`; };
  const todayISO = () => { const d = new Date(); const mm = String(d.getMonth() + 1).padStart(2, "0"); const dd = String(d.getDate()).padStart(2, "0"); return `${d.getFullYear()}-${mm}-${dd}`; };
  const hash = str => Array.from(str).reduce((a, c) => a + c.charCodeAt(0), 0);

  /* ========= DOM ========= */
  const $origen = document.getElementById("origen");
  const $destino = document.getElementById("destino");
  const $fecha = document.getElementById("fecha");
  const $form = document.getElementById("search-form");
  const $res = document.getElementById("resultados");
  const $msg = document.getElementById("msg");

  function fillCities() {
    const ciudades = new Set();
    RUTAS.forEach(r => { ciudades.add(r.origen); ciudades.add(r.destino); });
    [...ciudades].sort((a, b) => a.localeCompare(b, "es")).forEach(c => {
      $origen.insertAdjacentHTML("beforeend", `<option>${c}</option>`);
      $destino.insertAdjacentHTML("beforeend", `<option>${c}</option>`);
    });
  }

  /* ========= Construcción de mapas ========= */
  function createSeatGrid({ deckTitle, rows, cols, routeKey, seed, parent }) {
    const deck = document.createElement("div");
    deck.className = "seat-deck";
    if (deckTitle) deck.insertAdjacentHTML("afterbegin", `<h5>${deckTitle}</h5>`);

    const grid = document.createElement("div");
    grid.className = "smap";
    grid.style.gridTemplateColumns = `repeat(${cols}, 48px)`;
    deck.appendChild(grid);

    const total = rows * cols;
    const isTaken = n => ((n + seed) % 5) === 0; // ~20% ocupados

    for (let n = 1; n <= total; n++) {
      const seatId = `${routeKey}-s${n}-${deckTitle || 1}`;
      const wrap = document.createElement("div"); wrap.className = "sseat";
      const input = document.createElement("input"); input.type = "checkbox"; input.id = seatId;
      const label = document.createElement("label"); label.htmlFor = seatId; label.textContent = n;
      if (isTaken(n)) { wrap.classList.add("taken"); input.disabled = true; }
      grid.appendChild(wrap); wrap.appendChild(input); wrap.appendChild(label);
    }
    parent.appendChild(deck);
  }

  function buildSeatSection(route, container, fechaISO) {
    const routeKey = `${route.empresa}-${route.tipo}-${route.origen}-${route.destino}-${route.horario}`.replace(/\s+/g, "_");
    const seed = hash(routeKey) % 17;

    const panel = document.createElement("div");
    panel.className = "seat-panel"; panel.hidden = true;

    const summary = document.createElement("div");
    summary.className = "seat-summary";
    summary.innerHTML = `
      <div class="legend">
        <span class="chip chip-free">Libre</span>
        <span class="chip chip-selected">Seleccionada</span>
        <span class="chip chip-taken">Ocupada</span>
      </div>
      <div class="totals">
        <span>Seleccionadas: <strong class="count-selected">0</strong></span>
        <span>Total: <strong class="sum-total">${money(0)}</strong></span>
      </div>
      <button class="btn-continue" disabled>Continuar con la compra</button>
    `;
    panel.appendChild(summary);

    const maps = document.createElement("div");
    maps.className = "seat-maps"; panel.appendChild(maps);

    // Layout por tipo
    switch (route.tipo) {
      case "TAXI": createSeatGrid({ deckTitle: "", rows: 2, cols: 2, routeKey, seed, parent: maps }); break;
      case "AEROVAN": createSeatGrid({ deckTitle: "", rows: 4, cols: 3, routeKey, seed, parent: maps }); break;
      case "BUS_1PISO": createSeatGrid({ deckTitle: "", rows: 10, cols: 4, routeKey, seed, parent: maps }); break;
      case "BUS_2PISOS":
      default:
        createSeatGrid({ deckTitle: "Piso 1", rows: 8, cols: 4, routeKey, seed: seed + 3, parent: maps });
        createSeatGrid({ deckTitle: "Piso 2", rows: 8, cols: 4, routeKey, seed: seed + 7, parent: maps });
        break;
    }

    // Totales
    function updateTotals() {
      const boxes = panel.querySelectorAll(".sseat input[type=checkbox]");
      let selected = 0; boxes.forEach(b => { if (b.checked) selected++; });
      panel.querySelector(".count-selected").textContent = selected;
      panel.querySelector(".sum-total").textContent = money(selected * route.costo);
      panel.querySelector(".btn-continue").disabled = selected === 0;
    }
    panel.addEventListener("change", e => {
      if (e.target.matches(".sseat input[type=checkbox]")) updateTotals();
    });

    // Continuar → redirigir con datos
    const btn = summary.querySelector(".btn-continue");
    btn.addEventListener("click", () => {
      const seats = [];
      panel.querySelectorAll(".sseat input:checked").forEach(cb => {
        const lbl = cb.nextElementSibling;
        const deck = cb.closest(".seat-deck")?.querySelector("h5")?.textContent?.trim();
        const seatName = deck ? `${deck} ${lbl.textContent}` : lbl.textContent;
        seats.push(seatName);
      });
      if (!seats.length) return;

      const url = new URL("../facturacion/index.html", window.location.href);
      url.search = new URLSearchParams({
        empresa: route.empresa,
        tipo: route.tipo,
        origen: route.origen,
        destino: route.destino,
        horario: route.horario,
        fecha: fechaISO || "",
        sillas: seats.join(","),
        costo: String(route.costo),
        total: String(route.costo * seats.length)
      }).toString();
      window.location.href = url.toString();
    });

    container.appendChild(panel);
    return panel;
  }

  /* ========= Render ========= */
  function renderResults(items, fechaISO) {
    const fechaTexto = formatISOtoDMY(fechaISO);
    $res.innerHTML = "";
    if (!items.length) {
      $res.innerHTML = `<p class="note">No encontramos rutas para esa combinación. Intenta con otra ciudad.</p>`;
      return;
    }
    items.forEach(item => {
      const card = document.createElement("article");
      card.className = "result-card";
      card.innerHTML = `
        <div class="result-head">
          <span class="chip">${fechaTexto}</span>
          <h4>🏢 ${item.empresa}</h4>
        </div>
        <div class="result-body">
          <p>🧭 <strong>${item.origen}</strong> → <strong>${item.destino}</strong></p>
          <p>🕒 <strong>${item.horario}</strong></p>
          <p>🚘 <strong>Tipo:</strong> ${tipoLabel(item.tipo)}</p>
          <p>💲 <strong>${money(item.costo)}</strong></p>
        </div>
        <div class="result-actions">
          <button class="seat-toggle" aria-expanded="false">Ver sillas</button>
        </div>`;
      $res.appendChild(card);

      const seatPanel = buildSeatSection(item, card, fechaISO);
      const toggle = card.querySelector(".seat-toggle");
      toggle.addEventListener("click", () => {
        seatPanel.hidden = !seatPanel.hidden;
        toggle.textContent = seatPanel.hidden ? "Ver sillas" : "Ocultar sillas";
        toggle.setAttribute("aria-expanded", String(!seatPanel.hidden));
      });
    });
  }

  /* ========= Búsqueda ========= */
  function onSearch(e) {
    e.preventDefault();
    $msg.style.display = "none";
    const origen = $origen.value, destino = $destino.value, fechaISO = $fecha.value;
    if (!fechaISO) { $res.innerHTML = ""; $msg.textContent = "Selecciona una fecha válida."; $msg.style.display = "block"; return; }
    if (origen === destino) { $res.innerHTML = ""; $msg.textContent = "El origen y el destino no pueden ser iguales."; $msg.style.display = "block"; return; }
    const matches = RUTAS.filter(r => r.origen === origen && r.destino === destino);
    renderResults(matches, fechaISO);
  }

  fillCities();
  if ($fecha) $fecha.min = todayISO();
  $form.addEventListener("submit", onSearch);
})();
