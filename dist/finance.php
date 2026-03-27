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
              <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 180px;">
              <li>
                <a href="./api/logout.php" class="dropdown-item text-danger d-flex align-items-center">
                  <i class="bi bi-box-arrow-right me-2"></i>
                  Sign out
                </a>
              </li>
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
            <img id="accountLogo" src="./assets/img/bcf.png" alt="Logo" class="brand-image">
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span id="accountName" class="brand-text fw-light">BCF</span>
            
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
              <a href="./finance.php" class="nav-link active">
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
              <a href="./reports.php" class="nav-link">
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
              <div class="col-sm-6"><h3 class="mb-0">Finance</h3></div>
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

          <!-- Cash In -->
          <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box">
              <span class="info-box-icon text-bg-success shadow-sm">
                <i class="fas fa-wallet"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Cash In</span>
                <span class="info-box-number" id="kpi-cashin">£0</span>
              </div>
            </div>
          </div>

          <!-- Cash Out -->
          <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box">
              <span class="info-box-icon text-bg-danger shadow-sm">
                <i class="fas fa-money-bill-wave"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Cash Out</span>
                <span class="info-box-number" id="kpi-cashout">£0</span>
              </div>
            </div>
          </div>

          <!-- Outstanding -->
          <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box">
              <span class="info-box-icon text-bg-warning shadow-sm">
                <i class="fas fa-file-invoice"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Outstanding</span>
                <span class="info-box-number" id="kpi-outstanding">£0</span>
              </div>
            </div>
          </div>

          <!-- Upcoming -->
          <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box">
              <span class="info-box-icon text-bg-primary shadow-sm">
                <i class="fas fa-coins"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Upcoming</span>
                <span class="info-box-number" id="kpi-upcoming">£0</span>
              </div>
            </div>
          </div>

          <!-- Revenue -->
          <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box">
              <span class="info-box-icon text-bg-info shadow-sm">
                <i class="fas fa-chart-line"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Revenue</span>
                <span class="info-box-number" id="kpi-revenue">£0</span>
              </div>
            </div>
          </div>

        </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
              <div class="col-md-6">
              <div class="card mb-4">
                <div class="card-header">
                  <h3 class="card-title">Cashflow Trend</h3>
                </div>
                <div class="card-body">
                  <div id="cashflow-chart"></div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">Revenue vs Expenses</h3>
              </div>
              <div class="card-body">
                <div id="revenue-expense-chart"></div>
              </div>
            </div>
          </div>
            </div>
            <div class="card">
        <div class="card-header">
          <h3 class="card-title">Overdue Invoices</h3>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Client</th>
                <th>Invoice #</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Smith Family</td>
                <td>INV-1001</td>
                <td>£4,500</td>
                <td>10 Mar</td>
                <td><span class="badge bg-danger">Overdue</span></td>
              </tr>
              <tr>
                <td>Brown Ltd</td>
                <td>INV-1002</td>
                <td>£2,100</td>
                <td>14 Mar</td>
                <td><span class="badge bg-warning">Pending</span></td>
              </tr>
              <tr>
                <td>Wilson Group</td>
                <td>INV-1003</td>
                <td>£1,800</td>
                <td>18 Mar</td>
                <td><span class="badge bg-warning">Pending</span></td>
              </tr>
            </tbody>
          </table>
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
        <!-- Right side -->
        <div class="float-end d-none d-sm-inline">
          v1.0
        </div>

        <!-- Left side -->
        <strong>
          © <?php echo date('Y'); ?>
          <a href="#" class="text-decoration-none">Ballycastle Admin Dashboard</a>.
        </strong>
        All rights reserved.
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
    
    <!--end::OverlayScrollbars Configure-->
    <!-- OPTIONAL SCRIPTS -->
    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>
    <!-- sortablejs -->

    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>
    <script src="./js/account_ui.js"></script>
    <!-- ChartJS -->
    <!-- jsvectormap -->
    <script>
// ==============================
// 🔥 MAIN REFRESH FUNCTION
// ==============================
function refreshPageData() {
  loadFinanceData();
}

// ==============================
// ✅ ACCOUNT HELPER
// ==============================
function getAccount() {
  return localStorage.getItem('account') || 'bcf';
}

// ==============================
// 📊 FINANCE LOGIC
// ==============================
let chartCashflow, chartRevenue;
let isLoading = false;

async function loadFinanceData() {
  if (isLoading) return;
  isLoading = true;

  try {
    const res = await fetch(
      `api/get_finance.php?account=${getAccount()}&_=` + Date.now()
    );

    const data = await res.json();

    console.log("FINANCE API:", data);

    const setText = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.innerText = value;
    };

    // ================= KPI =================
    setText('kpi-cashin', "£" + Number(data.cash_in || 0).toLocaleString());
    setText('kpi-cashout', "£" + Number(data.cash_out || 0).toLocaleString());
    setText('kpi-outstanding', "£" + Number(data.outstanding || 0).toLocaleString());
    setText('kpi-upcoming', "£" + Number(data.upcoming || 0).toLocaleString());
    setText('kpi-revenue', "£" + Number(data.revenue || 0).toLocaleString());

    // ================= CASHFLOW =================
    const weeks = ["Week 1", "Week 2", "Week 3", "Week 4"];

    const cashInTrend = [
      data.cash_in * 0.4,
      data.cash_in * 0.6,
      data.cash_in * 0.8,
      data.cash_in
    ];

    const cashOutTrend = [
      data.cash_out * 0.4,
      data.cash_out * 0.6,
      data.cash_out * 0.8,
      data.cash_out
    ];

    const cashflowEl = document.querySelector("#cashflow-chart");

    if (cashflowEl) {
      if (chartCashflow) chartCashflow.destroy();

      chartCashflow = new ApexCharts(cashflowEl, {
        chart: { type: "line", height: 300 },
        series: [
          { name: "Cash In", data: cashInTrend },
          { name: "Cash Out", data: cashOutTrend }
        ],
        colors: ['#198754', '#dc3545'],
        stroke: { curve: "smooth" },
        xaxis: { categories: weeks },
        tooltip: {
          y: { formatter: val => "£" + val.toLocaleString() }
        }
      });

      chartCashflow.render();
    }

    // ================= REVENUE VS EXPENSE =================
    const months = ["Jan", "Feb", "Mar", "Apr"];

    const revenueEl = document.querySelector("#revenue-expense-chart");

    if (revenueEl) {
      if (chartRevenue) chartRevenue.destroy();

      chartRevenue = new ApexCharts(revenueEl, {
        chart: { type: "bar", height: 300 },
        series: [
          {
            name: "Revenue",
            data: [
              data.revenue * 0.6,
              data.revenue * 0.75,
              data.revenue * 0.85,
              data.revenue
            ]
          },
          {
            name: "Expenses",
            data: [
              data.cash_out * 0.7,
              data.cash_out * 0.8,
              data.cash_out * 0.9,
              data.cash_out
            ]
          }
        ],
        colors: ['#0d6efd', '#dc3545'],
        xaxis: { categories: months },
        plotOptions: {
          bar: { borderRadius: 6, columnWidth: "45%" }
        },
        tooltip: {
          y: { formatter: val => "£" + val.toLocaleString() }
        }
      });

      chartRevenue.render();
    }

  } catch (err) {
    console.error("FINANCE ERROR:", err);
  }

  isLoading = false;
}

// ==============================
// 🔥 LOAD + AUTO REFRESH
// ==============================
document.addEventListener("DOMContentLoaded", () => {
  refreshPageData();
  setInterval(refreshPageData, 60000);
});
</script>

  </body>
  <!--end::Body-->
</html>
