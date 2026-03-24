<?php include './auth/auth.php'; ?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin Dashboard</title>
    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE v4 | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media='all'"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />
    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
    #customFilters select {
        width: auto;
        min-width: 200px;
    }
    </style>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
          <ul class="navbar-nav ms-auto">
            
      
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="./assets/img/nicola.png"
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline">Nicola Graham</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

                <li class="user-footer">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                  <a href="./api/logout.php" class="btn btn-default btn-flat float-end">Sign out</a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="./index.html" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="./assets/img/bcf.png"
              alt="BCF Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">Admin</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="navigation"
            aria-label="Main navigation"
            data-accordion="false"
            id="navigation"
          >
            <li class="nav-item">
              <a href="./index.php" class="nav-link">
                <i class="nav-icon fa-regular fa-chart-bar"></i>
                <p>Dashboard</p>
              </a>
            </li> 
            <li class="nav-header">SALES</li>
            <li class="nav-item">
              <a href="./sales_pipeline.php" class="nav-link">
                <i class="nav-icon fa-solid fa-filter-circle-dollar"></i>
                <p>Sales & Pipeline</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./marketing.php" class="nav-link">
                <i class="nav-icon fa-solid fa-comments-dollar"></i>
                <p>Marketing</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./finance.php" class="nav-link">
                <i class="nav-icon fa-solid fa-sack-dollar"></i>
                <p>Finance</p>
              </a>
            </li>
            <li class="nav-header">OPERATIONS</li>
            <li class="nav-item">
              <a href="./operations.php" class="nav-link">
                <i class="nav-icon fa-solid fa-gears"></i>
                <p>Operations</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./client_project.php" class="nav-link">
                <i class="nav-icon fa-solid fa-handshake"></i>
                <p>Client Projects</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./staff.php" class="nav-link">
                <i class="nav-icon fa-solid fa-chart-line"></i>
                <p>Staff Performance</p>
              </a>
            </li>
            <li class="nav-header">SYSTEM</li>
            <li class="nav-item">
              <a href="./it_security.php" class="nav-link">
                <i class="nav-icon fa-solid fa-user-shield"></i>
                <p>IT & Security</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./reports.php" class="nav-link active">
                <i class="nav-icon fa-solid fa-newspaper"></i>
                <p>Reports</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./settings.php" class="nav-link">
                <i class="nav-icon fa-solid fa-gear"></i>
                <p>Settings</p>
              </a>
            </li>
          </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Reports & Exports</h3></div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">

            <div class="row mb-3">

              <div class="col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-primary">
                    <i class="fas fa-file-alt"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total Reports</span>
                    <span class="info-box-number" id="kpi-total">0</span>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-success">
                    <i class="fas fa-check-circle"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Completed</span>
                    <span class="info-box-number" id="kpi-completed">0</span>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-warning">
                    <i class="fas fa-clock"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number" id="kpi-pending">0</span>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Overdue</span>
                    <span class="info-box-number" id="kpi-overdue">0</span>
                  </div>
                </div>
              </div>

            </div>

          </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Reports Overview</h3>
                </div>

                <div class="card-body table-responsive">
                 
                 <table id="reports-table" class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Report Name</th>
                      <th>Category</th>
                      <th>Date</th>
                      <th>Owner</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
                </div>
              </div>
            </div>
            
            <!-- /.row (main row) -->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Footer -- -- &nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">BalleyCastleTestUI</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="./js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- 2. DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- 3. Buttons (EXPORT) -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script>
let dataTable = null;
let allData = [];

async function loadReports() {

  const res = await fetch('api/get_reports.php');
  const data = await res.json();

  console.log("REPORTS:", data);

  if (!Array.isArray(data)) return;

  allData = data;

  renderTable(data);
  updateKPIs(data);
  populateOwnerFilter(data);
}

// ================= RENDER TABLE =================
function renderTable(data) {

  const tbody = document.querySelector("#reports-table tbody");
  tbody.innerHTML = "";

  data.forEach(r => {

    let badge = "bg-warning";
    if (r.status === "Completed") badge = "bg-success";
    if (r.status === "Overdue") badge = "bg-danger";

    tbody.innerHTML += `
      <tr>
        <td>${r.name}</td>
        <td>${r.category}</td>
        <td>${r.date}</td>
        <td>${r.owner}</td>
        <td><span class="badge ${badge}">${r.status}</span></td>
      </tr>
    `;
  });

  if (dataTable) dataTable.destroy();

  dataTable = $('#reports-table').DataTable({
    pageLength: 10,
    responsive: true,
    order: [[2, "desc"]],
   dom: "<'row mb-3 align-items-center'\
        <'col-md-8 d-flex gap-2'<'dt-buttons'B><'#customFilters.d-flex gap-2'>>\
        <'col-md-4 text-end'f>\
      >\
      <'row'<'col-12'tr>>\
      <'row mt-2'<'col-md-5'i><'col-md-7'p>>",
    buttons: [
      {
        extend: 'excel',
        text: 'Export Excel'
      },
      {
        extend: 'pdf',
        text: 'Export PDF'
      },
      {
        extend: 'print',
        text: 'Print'
      }
    ]
  });
$('#customFilters').html(`
  <select id="filter-owner" class="form-select form-select-sm">
    <option value="">All Owners</option>
  </select>

  <select id="filter-status" class="form-select form-select-sm">
    <option value="">All Status</option>
    <option value="Completed">Completed</option>
    <option value="Pending">Pending</option>
    <option value="Overdue">Overdue</option>
  </select>
`);
  // 🔥 Listen to filtering changes
  dataTable.on('draw', function () {
    updateKPIsFromTable();
  });
}

// ================= KPI =================
function updateKPIs(data) {
  document.getElementById("kpi-total").innerText = data.length;
  document.getElementById("kpi-completed").innerText =
    data.filter(r => r.status === "Completed").length;
  document.getElementById("kpi-pending").innerText =
    data.filter(r => r.status === "Pending").length;
  document.getElementById("kpi-overdue").innerText =
    data.filter(r => r.status === "Overdue").length;
}

// 🔥 KPI BASED ON FILTERED TABLE
function updateKPIsFromTable() {
  let rows = dataTable.rows({ search: 'applied' }).data().toArray();

  let total = rows.length;
  let completed = 0, pending = 0, overdue = 0;

  rows.forEach(r => {
    let status = $(r[4]).text().trim();

    if (status === "Completed") completed++;
    else if (status === "Pending") pending++;
    else if (status === "Overdue") overdue++;
  });

  document.getElementById("kpi-total").innerText = total;
  document.getElementById("kpi-completed").innerText = completed;
  document.getElementById("kpi-pending").innerText = pending;
  document.getElementById("kpi-overdue").innerText = overdue;
}

// ================= OWNER FILTER =================
function populateOwnerFilter(data) {
  const ownerFilter = document.getElementById("filter-owner");

  ownerFilter.innerHTML = '<option value="">All Owners</option>';

  const owners = [...new Set(data.map(r => r.owner))];

  owners.forEach(owner => {
    ownerFilter.innerHTML += `<option value="${owner}">${owner}</option>`;
  });
}

// ================= FILTER EVENTS =================
$(document).on('change', '#filter-owner', function () {
  dataTable.column(3).search(this.value).draw();
});

$(document).on('change', '#filter-status', function () {
  dataTable.column(4).search(this.value).draw();
});

// ================= INIT =================
document.addEventListener("DOMContentLoaded", function () {
  loadReports();
  setInterval(loadReports, 60000);
});
</script>
  </body>
  <!--end::Body-->
</html>
