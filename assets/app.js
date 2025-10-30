// LEER ESTO





//para poder iniciar la aplicacion abre una terminar y escribe php -S localhost:8000
//luego ahi te dira directamente donde entrar
//realmente no hay usuario o contraseña, cualquiera vale
//y poneme un 100, va? :D






//LEE LO DE ARRIBA

const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

function showToast(message = 'Acción realizada.') {
  const toastEl = $('#toastOk');
  toastEl.querySelector('.toast-body').textContent = message;
  const t = new bootstrap.Toast(toastEl);
  t.show();
}

function escapeHtml(s) {
  return s
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

// fechas y reglas
function isValidDateStr(s) {
  if (!s) return false;
  const d = new Date(s);
  return !isNaN(d.getTime());
}

function daysSince(dateStr) {
  if (!isValidDateStr(dateStr)) return 0;
  const d = new Date(dateStr);
  const now = new Date();
  const ms = now - d;
  return Math.floor(ms / (1000 * 60 * 60 * 24));
}

function claseFilaJS(estadoServicio, estadoPago, fechaIngreso) {
  if (estadoServicio.toLowerCase() === 'finalizada' && estadoPago.toLowerCase() === 'pendiente') {
    return 'table-danger';
  }
  if (daysSince(fechaIngreso) > 7) {
    return 'table-warning';
  }
  return '';
}

function badgePago(estado) {
  return (estado.toLowerCase() === 'pagado')
    ? '<span class="badge text-bg-success">Pagado</span>'
    : '<span class="badge text-bg-warning text-dark">Pendiente</span>';
}
function badgeServicio(estado) {
  const e = estado.toLowerCase();
  if (e === 'finalizada')  return '<span class="badge text-bg-primary">Finalizada</span>';
  if (e === 'en proceso')  return '<span class="badge text-bg-info text-dark">En Proceso</span>';
  return '<span class="badge text-bg-secondary">En Espera</span>';
}

function actualizarKPIs() {
  const filas = $$('#tablaOrdenes tbody tr');
  const visibles = filas.filter(tr => !tr.classList.contains('d-none'));
  const total = visibles.length;

  let pendientes = 0;
  let atrasadas = 0;
  visibles.forEach(tr => {
    const pago = tr.querySelector('td:nth-child(6)').innerText.trim();
    const fechaIng = tr.querySelector('td:nth-child(3)').innerText.trim();
    if (pago.includes('Pendiente')) pendientes++;
    if (daysSince(fechaIng) > 7) atrasadas++;
  });

  $('#kpiTotal').textContent = total;
  $('#kpiPendientes').textContent = pendientes;
  $('#kpiAtrasadas').textContent = atrasadas;
}

// Filtros
function aplicarFiltros() {
  const q = $('#buscarInput').value.trim().toLowerCase();
  const fPago = $('#filtroPago').value;
  const fServ = $('#filtroServicio').value;

  $$('#tablaOrdenes tbody tr').forEach(tr => {
    const cliente = tr.children[0].innerText.toLowerCase();
    const placa   = tr.children[1].innerText.toLowerCase();
    const pagoTxt = tr.children[5].innerText.trim();
    const servTxt = tr.children[7].innerText.trim();

    let match = true;
    if (q && !(cliente.includes(q) || placa.includes(q))) match = false;
    if (fPago && !pagoTxt.includes(fPago)) match = false;
    if (fServ && !servTxt.includes(fServ)) match = false;

    tr.classList.toggle('d-none', !match);
  });

  actualizarKPIs();
}

// formulario
function validateForm() {
  let ok = true;
  const reqIds = ['cliente', 'placa', 'fechaIngreso', 'tipoServicio', 'observaciones', 'estadoPago', 'estadoServicio'];
  reqIds.forEach(id => {
    const el = document.getElementById(id);
    const val = el.value.trim();
    if (!val || (el.tagName === 'SELECT' && el.selectedIndex === 0)) {
      el.classList.add('is-invalid');
      ok = false;
    } else {
      el.classList.remove('is-invalid');
    }
  });

  // placa
  const placa = document.getElementById('placa');
  if (!/^[A-Z0-9-]{3,10}$/i.test(placa.value.trim())) {
    placa.classList.add('is-invalid');
    ok = false;
  }

  // fechas
  const fin = document.getElementById('fechaFin').value.trim();
  const ingreso = document.getElementById('fechaIngreso').value.trim();
  if (fin && ingreso && new Date(fin) < new Date(ingreso)) {
    const alert = $('#formAlert');
    alert.className = 'alert alert-danger';
    alert.textContent = 'La fecha de finalización no puede ser menor que la de ingreso.';
    alert.classList.remove('d-none');
    ok = false;
  }

  return ok;
}


(function init() {
  const form = document.getElementById('ordenForm');
  const alertBox = document.getElementById('formAlert');

  // Filtros
  $('#buscarInput').addEventListener('input', aplicarFiltros);
  $('#filtroPago').addEventListener('change', aplicarFiltros);
  $('#filtroServicio').addEventListener('change', aplicarFiltros);
  $('#btnLimpiarFiltros').addEventListener('click', () => {
    $('#buscarInput').value = '';
    $('#filtroPago').value = '';
    $('#filtroServicio').value = '';
    aplicarFiltros();
  });

  actualizarKPIs();

  const modalEl = document.getElementById('ordenModal');
  modalEl.addEventListener('shown.bs.modal', () => document.getElementById('cliente').focus());
  modalEl.addEventListener('hidden.bs.modal', () => $('#btnAbrirModal').focus());

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    alertBox.classList.add('d-none');

    if (!validateForm()) {
      alertBox.className = 'alert alert-danger';
      alertBox.textContent = 'Corrige los campos marcados.';
      alertBox.classList.remove('d-none');
      return;
    }

    const o = {
      cliente: document.getElementById('cliente').value.trim(),
      placa: document.getElementById('placa').value.trim().toUpperCase(),
      fechaIngreso: document.getElementById('fechaIngreso').value.trim(),
      tipoServicio: document.getElementById('tipoServicio').value.trim(),
      observaciones: document.getElementById('observaciones').value.trim(),
      estadoPago: document.getElementById('estadoPago').value.trim(),
      fechaFin: document.getElementById('fechaFin').value.trim(),
      estadoServicio: document.getElementById('estadoServicio').value.trim()
    };

    // Aviso si Finalizada + Pendiente
    if (o.estadoServicio === 'Finalizada' && o.estadoPago === 'Pendiente') {
      alertBox.className = 'alert alert-warning';
      alertBox.textContent = 'Advertencia: servicio finalizado con pago pendiente.';
      alertBox.classList.remove('d-none');
    }

    const tr = document.createElement('tr');
    tr.className = claseFilaJS(o.estadoServicio, o.estadoPago, o.fechaIngreso);

    tr.innerHTML = `
      <td>${escapeHtml(o.cliente)}</td>
      <td>${escapeHtml(o.placa)}</td>
      <td>${escapeHtml(o.fechaIngreso)}</td>
      <td>${escapeHtml(o.tipoServicio)}</td>
      <td>${escapeHtml(o.observaciones)}</td>
      <td>${badgePago(o.estadoPago)}</td>
      <td>${escapeHtml(o.fechaFin)}</td>
      <td>${badgeServicio(o.estadoServicio)}</td>
    `;

    const tbody = $('#tablaOrdenes tbody');
    tbody.prepend(tr);

    showToast('Orden agregada correctamente.');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();
    form.reset();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    tr.classList.remove('d-none');
    tr.scrollIntoView({ behavior: 'smooth', block: 'center' });

    aplicarFiltros();
  });
})();
