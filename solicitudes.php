<?php
  /* LEER ESTO





//para poder iniciar la aplicacion abre una terminar y escribe php -S localhost:8000
//luego ahi te dira directamente donde entrar
//realmente no hay usuario o contraseña, cualquiera vale
//y poneme un 100, va? :D






LEE LO DE ARRIBA */

  $ordenes = [
    [
      "cliente" => "Ana Vargas",
      "placa" => "ABC-123",
      "fechaIngreso" => "2025-10-15",
      "tipoServicio" => "Mantenimiento",
      "observaciones" => "Cambio de aceite y filtros.",
      "estadoPago" => "Pendiente",
      "fechaFin" => "",
      "estadoServicio" => "En Proceso"
    ],
    [
      "cliente" => "Luis Mora",
      "placa" => "KLM-456",
      "fechaIngreso" => "2025-10-10",
      "tipoServicio" => "Frenos",
      "observaciones" => "Pastillas y revisión de discos.",
      "estadoPago" => "Pendiente",
      "fechaFin" => "2025-10-18",
      "estadoServicio" => "Finalizada"
    ],
    [
      "cliente" => "María Soto",
      "placa" => "XYZ-789",
      "fechaIngreso" => "2025-10-21",
      "tipoServicio" => "Eléctrico",
      "observaciones" => "Alternador revisado.",
      "estadoPago" => "Pagado",
      "fechaFin" => "2025-10-22",
      "estadoServicio" => "Finalizada"
    ],
    [
      "cliente" => "Carlos Ruiz",
      "placa" => "BCA-222",
      "fechaIngreso" => "2025-10-05",
      "tipoServicio" => "Suspensión",
      "observaciones" => "Amortiguadores delanteros.",
      "estadoPago" => "Pagado",
      "fechaFin" => "2025-10-13",
      "estadoServicio" => "Finalizada"
    ],
    [
      "cliente" => "Andy Rojas",
      "placa" => "BCH-732",
      "fechaIngreso" => "2025-10-01",
      "tipoServicio" => "Transmisión",
      "observaciones" => "Cambio de aceite AT.",
      "estadoPago" => "Pendiente",
      "fechaFin" => "",
      "estadoServicio" => "En Espera"
    ]
  ];

  function claseFila($orden) {
    if (strcasecmp($orden["estadoServicio"], "Finalizada") === 0 && strcasecmp($orden["estadoPago"], "Pendiente") === 0) {
      return "table-danger";
    }
    $ingreso = DateTime::createFromFormat('Y-m-d', $orden["fechaIngreso"]);
    if ($ingreso) {
      $hoy = new DateTime('now');
      $diff = $ingreso->diff($hoy)->days;
      if ($diff > 7) return "table-warning";
    }
    return "";
  }

  function badgePago($estado) {
    $estado = trim($estado);
    if (strcasecmp($estado, "Pagado") === 0) {
      return '<span class="badge text-bg-success">Pagado</span>';
    }
    return '<span class="badge text-bg-warning text-dark">Pendiente</span>';
  }

  function badgeServicio($estado) {
    $e = trim($estado);
    if (strcasecmp($e, "Finalizada") === 0)  return '<span class="badge text-bg-primary">Finalizada</span>';
    if (strcasecmp($e, "En Proceso") === 0) return '<span class="badge text-bg-info text-dark">En Proceso</span>';
    return '<span class="badge text-bg-secondary">En Espera</span>';
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Solicitudes de Servicio | Taller ABC S.A.</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
  <script>
    if (sessionStorage.getItem('auth') !== 'true') {
      window.location.href = 'index.html';
    }
  </script>

  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Barra de navegación">
      <div class="container">
        <a class="navbar-brand" href="#" aria-label="Inicio">Taller ABC S.A.</a>
        <div class="ms-auto">
          <button id="logoutBtn" class="btn btn-outline-light btn-sm" type="button" aria-label="Cerrar sesión">Salir</button>
        </div>
      </div>
    </nav>
  </header>

  <main class="container my-4" role="main">
    <section class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3" aria-label="Acciones y filtros">
      <h1 class="h4 m-0">Solicitudes de Servicio</h1>
      <div class="d-flex align-items-center gap-2">
        <input id="buscarInput" class="form-control" type="search" placeholder="Buscar cliente o placa" aria-label="Buscar por cliente o placa">
        <select id="filtroPago" class="form-select" aria-label="Filtrar por estado de pago">
          <option value="">Pago: todos</option>
          <option value="Pagado">Pagado</option>
          <option value="Pendiente">Pendiente</option>
        </select>
        <select id="filtroServicio" class="form-select" aria-label="Filtrar por estado de servicio">
          <option value="">Servicio: todos</option>
          <option value="En Espera">En Espera</option>
          <option value="En Proceso">En Proceso</option>
          <option value="Finalizada">Finalizada</option>
        </select>
        <button class="btn btn-secondary" id="btnLimpiarFiltros" type="button" aria-label="Limpiar filtros">Limpiar</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ordenModal" id="btnAbrirModal" type="button" aria-label="Abrir formulario de nueva orden">
          Agregar orden
        </button>
      </div>
    </section>

    <section class="row g-3 mb-3" aria-label="Indicadores">
      <div class="col-12 col-md-4">
        <div class="card shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center">
            <span>Total de órdenes</span>
            <strong id="kpiTotal">0</strong>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center">
            <span>Pendientes de pago</span>
            <strong id="kpiPendientes">0</strong>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center">
            <span>Atrasadas (&gt; 7 días)</span>
            <strong id="kpiAtrasadas">0</strong>
          </div>
        </div>
      </div>
    </section>

    <section aria-label="Tabla de órdenes">
      <div class="table-responsive">
        <table id="tablaOrdenes" class="table table-hover table-sm align-middle">
          <thead class="table-light sticky-head">
            <tr>
              <th scope="col">Cliente</th>
              <th scope="col">Placa</th>
              <th scope="col">Fecha ingreso</th>
              <th scope="col">Tipo servicio</th>
              <th scope="col">Observaciones</th>
              <th scope="col">Estado pago</th>
              <th scope="col">Fecha finalización</th>
              <th scope="col">Estado servicio</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ordenes as $o): ?>
              <tr class="<?= claseFila($o) ?>">
                <td><?= htmlspecialchars($o["cliente"]) ?></td>
                <td><?= htmlspecialchars($o["placa"]) ?></td>
                <td><?= htmlspecialchars($o["fechaIngreso"]) ?></td>
                <td><?= htmlspecialchars($o["tipoServicio"]) ?></td>
                <td><?= htmlspecialchars($o["observaciones"]) ?></td>
                <td><?= badgePago($o["estadoPago"]) ?></td>
                <td><?= htmlspecialchars($o["fechaFin"]) ?></td>
                <td><?= badgeServicio($o["estadoServicio"]) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div id="liveRegion" class="visually-hidden" aria-live="polite"></div>
    </section>
  </main>

  <!-- para el orden -->
  <div class="modal fade" id="ordenModal" tabindex="-1" aria-labelledby="tituloModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <form id="ordenForm" novalidate>
          <div class="modal-header">
            <h2 id="tituloModal" class="modal-title h5">Nueva orden de servicio</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div id="formAlert" class="alert d-none" role="alert" aria-live="polite"></div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="cliente">Nombre del cliente</label>
                <input type="text" class="form-control" id="cliente" required>
                <div class="invalid-feedback">Requerido.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="placa">Número de placa</label>
                <input type="text" class="form-control" id="placa" placeholder="ABC-123 o CR-2025" required>
                <div class="invalid-feedback">Formato no válido. Usa letras, números y guión.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="fechaIngreso">Fecha de ingreso</label>
                <input type="date" class="form-control" id="fechaIngreso" required>
                <div class="invalid-feedback">Selecciona una fecha.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="tipoServicio">Tipo de servicio</label>
                <select class="form-select" id="tipoServicio" required>
                  <option value="" selected disabled>Selecciona...</option>
                  <option>Mantenimiento</option>
                  <option>Frenos</option>
                  <option>Eléctrico</option>
                  <option>Suspensión</option>
                  <option>Transmisión</option>
                  <option>Diagnóstico</option>
                </select>
                <div class="invalid-feedback">Selecciona un tipo.</div>
              </div>

              <div class="col-12">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea class="form-control" id="observaciones" rows="2" required></textarea>
                <div class="invalid-feedback">Agrega una nota breve.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="estadoPago">Estado del pago</label>
                <select class="form-select" id="estadoPago" required>
                  <option value="" selected disabled>Selecciona...</option>
                  <option>Pagado</option>
                  <option>Pendiente</option>
                </select>
                <div class="invalid-feedback">Indica el estado de pago.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="fechaFin">Fecha de finalización</label>
                <input type="date" class="form-control" id="fechaFin" aria-describedby="ayuda-fin">
                <div id="ayuda-fin" class="form-text">Si el servicio está finalizado, registra esta fecha.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="estadoServicio">Estado del servicio</label>
                <select class="form-select" id="estadoServicio" required>
                  <option value="" selected disabled>Selecciona...</option>
                  <option>En Espera</option>
                  <option>En Proceso</option>
                  <option>Finalizada</option>
                </select>
                <div class="invalid-feedback">Selecciona el estado.</div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" type="submit">Agregar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- toast -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastOk" class="toast" role="status" aria-live="polite" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">Sistema</strong>
        <small>Ahora</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
      <div class="toast-body">Acción realizada.</div>
    </div>
  </div>

  <footer class="text-center text-muted small py-3">
    <span>hecho por Andy Rojas</span>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/app.js"></script>
  <script>
    document.getElementById('logoutBtn').addEventListener('click', () => {
      sessionStorage.removeItem('auth');
      window.location.href = 'index.html';
    });
  </script>
</body>
</html>
