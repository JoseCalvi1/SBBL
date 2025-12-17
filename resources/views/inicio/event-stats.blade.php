@extends('layouts.app')

@section('content')
<div class="container-fluid bg-dark text-light py-4 min-vh-100 d-flex flex-column align-items-center">

  <h1 class="mb-4 text-center fw-semibold display-6">📊 Registro de Combates</h1>

  <div id="combos-container" class="w-100" style="max-width: 550px;"></div>

  <div id="resumen" class="text-center mt-3 fs-5 fw-semibold"></div>

  <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
    <button id="descargar-btn" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
      ⬇️ Descargar CSV
    </button>
    <button id="reset-btn" class="btn btn-outline-light px-4 py-2 fw-semibold shadow-sm">
      ♻️ Resetear
    </button>
  </div>

</div>

<div id="toast" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 px-4 py-2 rounded text-white fw-semibold shadow"
  style="display:none; background: linear-gradient(90deg, #0d6efd, #6610f2); z-index: 1055;">
</div>
@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('combos-container');
  const toast = document.getElementById('toast');
  const nombresCombos = ["Combo 1", "Combo 2", "Combo 3"];
  const tiposPuntos = ["Spin", "Over", "Burst", "Xtreme"];
  const valoresPuntos = [1, 2, 2, 3];

  const initialCounts = {
    spinCounts: { win: 0, loss: 0 },
    overCounts: { win: 0, loss: 0 },
    burstCounts: { win: 0, loss: 0 },
    xtremeCounts: { win: 0, loss: 0 }
  };
  const countKeys = ['spinCounts', 'overCounts', 'burstCounts', 'xtremeCounts'];

  let datos = nombresCombos.map(nombre => ({
    nombre,
    victorias: 0,
    derrotas: 0,
    puntosGanados: 0,
    puntosPerdidos: 0,
    ...JSON.parse(JSON.stringify(initialCounts))
  }));

  function render() {
    container.innerHTML = '';
    datos.forEach((combo, i) => {
      const card = document.createElement('div');
      card.className = 'card bg-secondary text-light mb-4 border-0 shadow-lg rounded-4';
      card.innerHTML = `
        <div class="card-body p-3">
          <input type="text"
            value="${combo.nombre}"
            class="form-control form-control-lg mb-4 bg-dark text-light border-0 text-center rounded-3 shadow-sm fw-semibold"
            onchange="actualizarNombre(${i}, this.value)"
            placeholder="Nombre del combo">

          <div class="row g-2 mb-3">
            ${tiposPuntos.map((t, j) => `
              <div class="col-6">
                ${renderBotonPunto(i, j, t, combo[countKeys[j]].win, true, 'btn-success')}
              </div>
            `).join('')}
          </div>

          <div class="row g-2">
            ${tiposPuntos.map((t, j) => `
              <div class="col-6">
                ${renderBotonPunto(i, j, t, combo[countKeys[j]].loss, false, 'btn-danger')}
              </div>
            `).join('')}
          </div>

          <div class="row text-center mt-3">
            <div class="col-6 col-sm-3">
                <small>🏆 Victorias</small><br>
                <b>${combo.victorias}</b>
            </div>
            <div class="col-6 col-sm-3">
                <small>🔹 Pts +</small><br>
                <b>${combo.puntosGanados}</b>
            </div>
            <div class="col-6 col-sm-3">
                <small>💀 Derrotas</small><br>
                <b>${combo.derrotas}</b>
            </div>
            <div class="col-6 col-sm-3">
                <small>🔻 Pts -</small><br>
                <b>${combo.puntosPerdidos}</b>
            </div>

            <!-- Nuevo: Diferencia de victorias -->
            <div class="col-6 col-sm-3">
                <small>⚖️ Balance W/L</small><br>
                <b style="color:${combo.victorias - combo.derrotas >= 0 ? 'limegreen' : 'red'};">
                    ${combo.victorias - combo.derrotas}
                </b>
            </div>

            <!-- Nuevo: Diferencia de puntos -->
            <div class="col-6 col-sm-3">
                <small>📈 Balance Pts</small><br>
                <b style="color:${combo.puntosGanados - combo.puntosPerdidos >= 0 ? 'limegreen' : 'red'};">
                    ${combo.puntosGanados - combo.puntosPerdidos}
                </b>
            </div>
        </div>

        </div>
      `;
      container.appendChild(card);
    });
    actualizarResumen();
  }

  function renderBotonPunto(comboIndex, tipoIndex, texto, count, esVictoria, btnClass) {
    const color = esVictoria ? 'success' : 'danger';
    return `
      <div class="d-flex align-items-center gap-1">
        <button class="btn btn-outline-${color} btn-sm flex-shrink-0"
          onclick="restarPunto(${comboIndex}, ${tipoIndex}, ${esVictoria})" title="Restar 1">
          <i class="fas fa-minus"></i>
        </button>
        <button class="btn ${btnClass} flex-grow-1 fw-semibold py-2 d-flex justify-content-between align-items-center"
          onclick="sumarPunto(${comboIndex}, ${tipoIndex}, ${esVictoria})">
          <span>${texto}</span>
          <span class="badge bg-light text-dark fw-bold">${count}</span>
        </button>
        <button class="btn btn-outline-${color} btn-sm flex-shrink-0"
          onclick="sumarPunto(${comboIndex}, ${tipoIndex}, ${esVictoria})" title="Añadir 1">
          <i class="fas fa-plus"></i>
        </button>
      </div>
    `;
  }

  window.actualizarNombre = (i, nombre) => {
    datos[i].nombre = nombre;
    guardarLocal();
  };

  window.sumarPunto = (i, tipo, esVictoria) => {
    const puntos = valoresPuntos[tipo];
    const countKey = countKeys[tipo];
    const counter = esVictoria ? 'win' : 'loss';
    datos[i][countKey][counter]++;
    if (esVictoria) {
      datos[i].victorias++;
      datos[i].puntosGanados += puntos;
    } else {
      datos[i].derrotas++;
      datos[i].puntosPerdidos += puntos;
    }
    guardarLocal();
    render();
    mostrarToast(esVictoria ? "✅ Victoria registrada" : "❌ Derrota registrada");
  };

  window.restarPunto = (i, tipo, esVictoria) => {
    const puntos = valoresPuntos[tipo];
    const countKey = countKeys[tipo];
    const counter = esVictoria ? 'win' : 'loss';
    if (datos[i][countKey][counter] > 0) {
      datos[i][countKey][counter]--;
      if (esVictoria) {
        datos[i].victorias--;
        datos[i].puntosGanados -= puntos;
      } else {
        datos[i].derrotas--;
        datos[i].puntosPerdidos -= puntos;
      }
      guardarLocal();
      render();
      mostrarToast("➖ Acción deshecha");
    } else {
      mostrarToast("⚠️ El contador ya está en cero", '#dc3545');
    }
  };

  function actualizarResumen() {
    const totalV = datos.reduce((a,b)=>a+b.victorias,0);
    const totalD = datos.reduce((a,b)=>a+b.derrotas,0);
    const totalPG = datos.reduce((a,b)=>a+b.puntosGanados,0);
    const totalPP = datos.reduce((a,b)=>a+b.puntosPerdidos,0);
    const totalRounds = totalV + totalD;
    const winrate = totalRounds > 0 ? Math.round((totalV/totalRounds)*100) : 0;
    document.getElementById('resumen').innerHTML = `
      🏆 <span class="text-success">${totalV}</span> victorias —
      💀 <span class="text-danger">${totalD}</span> derrotas —
      🔹 <span class="text-info">${totalPG}</span> pts + —
      🔻 <span class="text-warning">${totalPP}</span> pts -<br>
      📈 Winrate: <span class="text-primary">${winrate}%</span> (${totalRounds} rondas)
    `;
  }

  function mostrarToast(texto, colorFondo = 'linear-gradient(90deg, #0d6efd, #6610f2)') {
    toast.textContent = texto;
    toast.style.background = colorFondo;
    toast.style.display = 'block';
    toast.style.opacity = '1';
    setTimeout(() => { toast.style.opacity = '0'; }, 1500);
    setTimeout(() => { toast.style.display = 'none'; }, 2000);
  }

  function guardarLocal() { localStorage.setItem('combosDatos', JSON.stringify(datos)); }
  function cargarLocal() {
    const saved = localStorage.getItem('combosDatos');
    if (saved) {
      const savedData = JSON.parse(saved);
      datos = savedData.map(combo => ({
        ...combo,
        ...Object.keys(initialCounts).reduce((acc, key) => {
          acc[key] = combo[key] || initialCounts[key];
          return acc;
        }, {})
      }));
    }
  }

  function descargarCSV() {
    const encabezado = [
        "Combo",
        "Victorias",
        "Derrotas",
        "Puntos Ganados",
        "Puntos Perdidos",
        "Balance W/L",
        "Balance Pts",
        "Spin Win",
        "Spin Loss",
        "Over Win",
        "Over Loss",
        "Burst Win",
        "Burst Loss",
        "Xtreme Win",
        "Xtreme Loss"
    ].join(",") + "\n";

    const filas = datos.map(d => [
        d.nombre,
        d.victorias,
        d.derrotas,
        d.puntosGanados,
        d.puntosPerdidos,
        d.victorias - d.derrotas,
        d.puntosGanados - d.puntosPerdidos,
        d.spinCounts.win,
        d.spinCounts.loss,
        d.overCounts.win,
        d.overCounts.loss,
        d.burstCounts.win,
        d.burstCounts.loss,
        d.xtremeCounts.win,
        d.xtremeCounts.loss
    ].join(",")).join("\n");

    const blob = new Blob([encabezado + filas], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = "estadisticas_beyblade_completa.csv";
    a.click();
    mostrarToast("📂 CSV completo descargado");
    }


  function resetear() {
    if (confirm("¿Seguro que quieres resetear TODOS los datos de combate?")) {
      datos = nombresCombos.map(nombre => ({
        nombre,
        victorias: 0,
        derrotas: 0,
        puntosGanados: 0,
        puntosPerdidos: 0,
        spinCounts: { win: 0, loss: 0 },
        overCounts: { win: 0, loss: 0 },
        burstCounts: { win: 0, loss: 0 },
        xtremeCounts: { win: 0, loss: 0 }
      }));
      guardarLocal();
      render();
      mostrarToast("🔄 Datos reseteados");
    }
  }

  document.getElementById('descargar-btn').addEventListener('click', descargarCSV);
  document.getElementById('reset-btn').addEventListener('click', resetear);

  cargarLocal();
  render();
});
</script>

<style>
body { background-color: #121212 !important; color: #f8f9fa; }
.card { transition: all 0.3s ease; background-color: #1e1e1e !important; }
.card:hover { }
input::placeholder { color: #aaa; }
#toast { transition: opacity 0.4s ease; }
.btn-success, .btn-danger {  }
.btn-success:hover, .btn-danger:hover { box-shadow: 0 0 10px rgba(255,255,255,0.15); }
</style>
@endsection
