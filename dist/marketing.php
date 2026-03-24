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
            <a href="./sales_pipeline.php" class="nav-link">
              <i class="nav-icon fa-solid fa-filter-circle-dollar"></i>
              <p>Sales & Pipeline</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="./marketing.php" class="nav-link active">
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
              <div class="col-sm-6"><h3 class="mb-0">Marketing</h3></div>
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
              <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-danger shadow-sm">
                  <i class="fa-solid fa-sack-dollar"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Ad Spend</span>
                  <span class="info-box-number" id="kpi-adspend">£0</span>
                </div>
              </div>
            </div>

            <!-- CPL -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-warning shadow-sm">
                  <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">CPL</span>
                  <span class="info-box-number" id="kpi-cpl">£0</span>
                </div>
              </div>
            </div>

            <!-- Organic -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-success shadow-sm">
                  <i class="fa-brands fa-searchengin"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Organic</span>
                  <span class="info-box-number" id="kpi-organic">0</span>
                </div>
              </div>
            </div>

            <!-- Meta Ads -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-primary shadow-sm">
                  <i class="fa-brands fa-meta"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Meta Ads</span>
                  <span class="info-box-number" id="kpi-meta">0</span>
                </div>
              </div>
            </div>

            <!-- Google Ads -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-info shadow-sm">
                  <i class="fa-brands fa-google"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Google Ads</span>
                  <span class="info-box-number" id="kpi-google">0</span>
                </div>
              </div>
            </div>

            <!-- TikTok -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-dark shadow-sm">
                  <i class="fa-brands fa-tiktok"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">TikTok</span>
                  <span class="info-box-number" id="kpi-tiktok">0</span>
                </div>
              </div>
            </div>
              <!--begin::Col-->
              <div class="col-md-12">
                <div class="card mb-4">
                  
                  <!-- /.card-header -->
                  <div class="card-body">
                    <!--begin::Row-->
                    <div class="row">
                      <div class="col-md-12" >
                        <p class="text-center">
                          <strong>Leads :</strong>
                        </p>
                        <div id="sales-chart" ></div>
                      </div>
                    </div>
                    <!--end::Row-->
                  </div>
                </div>
                <!-- /.card -->
              </div>
            
              <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
              <div class="col-md-6">
                <div class="card mb-4">
                  <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                      <h3 class="card-title">Weekly Marketing Performance</h3>
                      <a
                        href="javascript:void(0);"
                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                      >
                        View Report
                      </a>
                    </div>
                  </div>

                  <div class="card-body">
                    <div class="d-flex">
                      <p class="d-flex flex-column">
                        <span class="fw-bold fs-5" id="kpi-leads-week"></span>
                        <span>Total Leads This Week</span>
                      </p>
                      <p class="ms-auto d-flex flex-column text-end">
                        <span class="text-success">
                          <i class="bi bi-arrow-up" id="kpi-leads-percent"></i>
                        </span>
                        <span class="text-secondary">Since Last Week</span>
                      </p>
                    </div>

                    <div class="position-relative mb-4">
                      <div id="weekly-marketing-chart"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-6 connectedSortable">
                <div class="card mb-4">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                <h3 class="card-title">Traffic Sources</h3>
                <a
                    href="javascript:void(0);"
                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                >
                    View Report
                </a>
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex">
                <p class="d-flex flex-column">
                    <span class="fw-bold fs-5" id="kpi-traffic"></span>
                    <span>Website Traffic</span>
                </p>
                <p class="ms-auto d-flex flex-column text-end">
                    <span class="text-success">
                    <i class="bi bi-arrow-up" id="kpi-traffic-percent"></i>
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
    <!-- ChartJS -->
   <script>
let chartMain, chartWeekly, chartSources;
let isLoading = false;

async function loadMarketingData() {
  if (isLoading) return;
  isLoading = true;

  try {
    const res = await fetch('api/get_pipeline.php?_=' + Date.now());
    const data = await res.json();

    console.log("MARKETING DATA:", data);

    // ================= KPI CARDS =================
    const sources = data.lead_sources || {};

    const meta = sources["Meta Ads"] || 0;
    const google = sources["Google"] || 0;
    const tiktok = sources["TikTok"] || 0;
    const organic = sources["Organic"] || 0;

    const totalLeads = meta + google + tiktok + organic;

    // Fake spend logic (replace later with real API if available)
    const adSpend = totalLeads * 15;
    const cpl = totalLeads > 0 ? (adSpend / totalLeads) : 0;

    document.getElementById("kpi-adspend").innerText = "£" + adSpend.toLocaleString();
    document.getElementById("kpi-cpl").innerText = "£" + cpl.toFixed(2);
    document.getElementById("kpi-meta").innerText = meta;
    document.getElementById("kpi-google").innerText = google;
    document.getElementById("kpi-tiktok").innerText = tiktok;
    document.getElementById("kpi-organic").innerText = organic;

    // ================= MAIN BAR CHART (FIXED) =================
    if (chartMain) chartMain.destroy();

    chartMain = new ApexCharts(document.querySelector("#sales-chart"), {
      chart: { type: "bar", height: 300 },
      series: [{
        name: "Leads",
        data: [meta, google, organic, tiktok]
      }],
      xaxis: {
        categories: ["Meta Ads", "Google", "Organic", "TikTok"]
      },
      plotOptions: {
        bar: {
          distributed: true
        }
      }
    });

    chartMain.render();

    // ================= WEEKLY KPI =================
    const weeklyLeads = data.weekly_leads || 0;
    const lastWeek = data.last_week_leads || 0;

    let percent = lastWeek > 0
      ? ((weeklyLeads - lastWeek) / lastWeek) * 100
      : 0;

    percent = percent.toFixed(1);

    const leadsText = document.querySelector(".card-body .fw-bold");
    const percentText = document.querySelector(".card-body .text-success, .card-body .text-danger");

    if (leadsText) leadsText.innerText = weeklyLeads;
    if (percentText) {
      percentText.innerText = (percent >= 0 ? "↑ " : "↓ ") + Math.abs(percent) + "%";
      percentText.className = percent >= 0 ? "text-success" : "text-danger";
    }

    // ================= WEEKLY CHART (USE FULL DATA) =================
    if (chartWeekly) chartWeekly.destroy();

    chartWeekly = new ApexCharts(document.querySelector("#weekly-marketing-chart"), {
      chart: { type: "line", height: 300 },
      series: [{
        name: "Leads",
        data: data.weekly_trend || []
      }],
      xaxis: {
        categories: data.weekly_labels || []
      }
    });

    chartWeekly.render();

    // ================= DONUT CHART =================
    if (chartSources) chartSources.destroy();

    const total = Object.values(sources).reduce((a, b) => a + b, 0);

    chartSources = new ApexCharts(document.querySelector("#lead-sources-chart"), {
      chart: { type: "donut", height: 280 },
      series: Object.values(sources),
      labels: Object.keys(sources),
      legend: { position: "bottom" },
      dataLabels: {
        formatter: function (val) {
          return val.toFixed(1) + "%";
        }
      }
    });

    chartSources.render();

  } catch (err) {
    console.error("MARKETING ERROR:", err);
  }

  isLoading = false;
}

document.addEventListener("DOMContentLoaded", () => {
  loadMarketingData();
  setInterval(loadMarketingData, 60000);
});
</script>

  </body>
  <!--end::Body-->
</html>
