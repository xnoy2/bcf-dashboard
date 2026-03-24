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
              <a href="./sales_pipeline.php" class="nav-link active">
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
              <div class="col-sm-6"><h3 class="mb-0">Sales & Pipeline</h3></div>
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
              <!--begin::Col-->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-success shadow-sm">
                    <i class="fa fa-users"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">New Leads</span>
                    <span class="info-box-number" id="kpi-leads">
                      0
                    </span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-warning shadow-sm">
                    <i class="fa-solid fa-envelope-open-text"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Quotes Issued</span>
                    <span class="info-box-number" id="kpi-quotes">
                      0
                    </span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-primary shadow-sm">
                    <i class="fa fa-handshake"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Sales Closed</span>
                    <span class="info-box-number" id="kpi-closed">
                      0
                    </span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-info shadow-sm">
                    <i class="fa-solid fa-scale-balanced"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Pipeline Value</span>
                    <span class="info-box-number" id="kpi-pipeline">
                      0
                    </span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
           
              
              <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
              <!-- Start col -->
              <div class="col-lg-4 connectedSortable">
                <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Leads Trend by Week</h3>
                </div>
                <div class="card-body">
                    <div id="revenue-chart"></div>
                </div>
                </div>
                <!-- /.card -->                
              </div>
              <!-- /.Start col -->
              <!-- Start col -->
              <div class="col-lg-4 connectedSortable">
                <div class="card mb-4">
                  <div class="card-header border-0">
                  <div class="d-flex justify-content-between">
                    <h3 class="card-title">Pipeline by Stage</h3>
                    <a
                      href="https://app.gohighlevel.com/v2/location/GSxspezlKiWYWE604ot9/dashboard" target="_blank"
                      class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                    >
                      View Report
                    </a>
                  </div>
                </div>

                <div class="card-body">
                  <div class="d-flex">
                    <p class="d-flex flex-column">
                      <span class="fw-bold fs-5">85 Leads</span>
                      <span>Current Sales Pipeline</span>
                    </p>
                    <p class="ms-auto d-flex flex-column text-end">
                      <span class="text-success">
                        <i class="bi bi-arrow-up"></i> 12.5%
                      </span>
                      <span class="text-secondary">Since Last Week</span>
                    </p>
                  </div>

                  <div class="position-relative mb-4"  style="margin-top:-30px">
                    <div id="sales-chart" style="margin-bottom: -15.8px;"></div>
                  </div>
                </div>
                  </div>
              </div>
              <div class="col-lg-4 connectedSortable">
                <div class="card mb-4">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                <h3 class="card-title">Lead Sources</h3>
                <a
                    href="https://app.gohighlevel.com/v2/location/GSxspezlKiWYWE604ot9/dashboard" target="_blank"
                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                >
                    View Report
                </a>
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex">
                <p class="d-flex flex-column">
                    <span class="fw-bold fs-5">42 Leads</span>
                    <span>Lead Sources Overview</span>
                </p>
                <p class="ms-auto d-flex flex-column text-end">
                    <span class="text-success">
                    <i class="bi bi-arrow-up"></i> 8.4%
                    </span>
                    <span class="text-secondary">Since Last Week</span>
                </p>
                </div>

                <div class="position-relative mb-4" style="margin-top: -25px;">
                <div id="lead-sources-chart"></div>
                </div>
            </div>
            </div>
              </div>
              <!-- /.Start col -->
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
    
    <!-- KEEP ALL YOUR ORIGINAL HTML ABOVE (no changes needed) -->

<!-- ================= REQUIRED SCRIPTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
let chartTrend, chartStages, chartSources;
let isLoading = false;

async function loadDashboardData() {
  if (isLoading) return;
  isLoading = true;

  try {
    const res = await fetch('api/get_pipeline.php?_=' + Date.now());
    const data = await res.json();

    console.log("API DATA:", data);

    // ================= KPI =================
    document.getElementById('kpi-leads').innerText = data.weekly_leads ?? 0;
    document.getElementById('kpi-quotes').innerText = data.quotes_issued ?? 0;
    document.getElementById('kpi-closed').innerText = data.closed_sales ?? 0;
    document.getElementById('kpi-pipeline').innerText =
      "£" + Number(data.pipeline_value ?? 0).toLocaleString();

    // ================= 1. LEADS TREND =================
    const weeks = Object.keys(data.weekly_trend || {});
    const leads = Object.values(data.weekly_trend || {});

    if (chartTrend) chartTrend.destroy();

    chartTrend = new ApexCharts(document.querySelector("#revenue-chart"), {
      chart: {
        type: "area",
        height: 250,
        toolbar: { show: false }
      },
      series: [{
        name: "Leads",
        data: leads
      }],
      xaxis: {
        categories: weeks
      },
      stroke: {
        curve: "smooth"
      },
      dataLabels: {
        enabled: false
      }
    });

    chartTrend.render();

    // ================= 2. PIPELINE BY STAGE =================
    const stages = Object.keys(data.stages || {});
    const stageValues = Object.values(data.stages || {});

    if (chartStages) chartStages.destroy();

    chartStages = new ApexCharts(document.querySelector("#sales-chart"), {
      chart: {
        type: "bar",
        height: 250,
        toolbar: { show: false }
      },
      series: [{
        name: "Leads",
        data: stageValues
      }],
      xaxis: {
        categories: stages
      },
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: "45%"
        }
      },
      dataLabels: {
        enabled: true
      }
    });

    chartStages.render();

    // ================= 3. LEAD SOURCES =================
    const sources = Object.keys(data.lead_sources || {});
    const sourceValues = Object.values(data.lead_sources || {});

    if (chartSources) chartSources.destroy();

    chartSources = new ApexCharts(document.querySelector("#lead-sources-chart"), {
      chart: {
        type: "donut",
        height: 260
      },
      series: sourceValues,
      labels: sources,
      legend: {
        position: "bottom"
      }
    });

    chartSources.render();

  } catch (err) {
    console.error("Dashboard error:", err);
  }

  isLoading = false;
}

// ================= LOAD =================
document.addEventListener("DOMContentLoaded", () => {
  loadDashboardData();
  setInterval(loadDashboardData, 60000);
});
</script>
  </body>
  <!--end::Body-->
</html>
