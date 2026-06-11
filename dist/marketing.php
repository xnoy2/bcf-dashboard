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
    <style>
      .card-body {
        min-height: 350px;
      }

      #weekly-marketing-chart,
      #lead-sources-chart {
        width: 100%;
        height: 100%;
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
          <a href="./index.php" class="brand-link">
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
                          <strong>Leads by Source</strong>
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
            <div class="row g-3 align-items-stretch">
              <div class="col-md-6 d-flex">
                <div class="card mb-4 w-100 h-100">
                  <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                      <h3 class="card-title">Weekly Marketing Performance</h3>
                      <a
                      href="https://app.gohighlevel.com/v2/location/GSxspezlKiWYWE604ot9/dashboard"
                      target="_blank"
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
                        <span id="kpi-leads-percent"></span>
                        
                        <span class="text-secondary">Since Last Week</span>
                      </p>
                    </div>

                    <div class="position-relative mb-4">
                      <div id="weekly-marketing-chart"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 d-flex">
                <div class="card mb-4 w-100 h-100">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                <h3 class="card-title">Traffic Sources</h3>
                <a
                      href="https://app.gohighlevel.com/v2/location/GSxspezlKiWYWE604ot9/dashboard"
                      target="_blank"
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
                    <span id="kpi-traffic-percent"></span>
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
    <!-- ChartJS -->
    <script src="./js/account_ui.js"></script>
   <script>
// ==============================
// 🔥 MAIN REFRESH FUNCTION
// ==============================
function refreshPageData() {
  loadMarketingData();
}

// ==============================
// ✅ ACCOUNT HELPER
// ==============================
function getAccount() {
  return localStorage.getItem('account') || 'bcf';
}

const SOURCE_COLORS = {
  "Meta Ads": "#0d6efd",     // Blue
  "Google": "#198754",       // Green
  "Organic": "#f59e0b",      // Orange
  "TikTok": "#ef4444",       // Red
  "Referral": "#8b5cf6"      // Purple
};

// ==============================
// 📊 MARKETING LOGIC
// ==============================
let chartMain, chartWeekly, chartSources;
let isLoading = false;

async function loadMarketingData() {
  if (isLoading) return;
  isLoading = true;

  try {
    const res = await fetch(
      `api/get_pipeline.php?account=${getAccount()}&_=` + Date.now()
    );

    const data = await res.json();

    console.log("MARKETING DATA:", data);

    // ================= KPI CARDS =================
    const sources = data.lead_sources || {};

    const meta = sources["Meta Ads"] || 0;
    const google = sources["Google"] || 0;
    const tiktok = sources["TikTok"] || 0;
    const organic = sources["Organic"] || 0;
    const referral = sources["Referral"] || 0;

    // 🔥 TOTAL (FIXED)
    const totalLeads = meta + google + tiktok + organic + referral;

    const adSpend = totalLeads * 15;
    const cpl = totalLeads > 0 ? (adSpend / totalLeads) : 0;

    const setText = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.innerText = value;
    };

    setText("kpi-adspend", "£" + adSpend.toLocaleString());
    setText("kpi-cpl", "£" + cpl.toFixed(2));
    setText("kpi-meta", meta);
    setText("kpi-google", google);
    setText("kpi-tiktok", tiktok);
    setText("kpi-organic", organic);

    // ================= MAIN BAR CHART =================
const mainEl = document.querySelector("#sales-chart");

if (mainEl) {
  if (chartMain) chartMain.destroy();

  const categories = ["Meta Ads", "Google", "Organic", "TikTok", "Referral"];

  const values = categories.map(c => sources[c] || 0);
  const colors = categories.map(c => SOURCE_COLORS[c]);

  chartMain = new ApexCharts(mainEl, {
    chart: { type: "bar", height: 300 },
    series: [{
      name: "Leads",
      data: values
    }],
    xaxis: {
      categories: categories
    },
    colors: colors, // ✅ FIXED COLOR MAPPING
    plotOptions: {
      bar: { distributed: true }
    }
  });

  chartMain.render();
}

    // ================= WEEKLY KPI =================
   const weeklyLeads = data.weekly_leads || 0;
  const lastWeek = data.last_week_leads || 0;

  let percent = lastWeek > 0
    ? ((weeklyLeads - lastWeek) / lastWeek) * 100
    : 0;

  percent = percent.toFixed(1);

  // ✅ CORRECT ELEMENT TARGETING
  const leadsText = document.getElementById("kpi-leads-week");
  const percentText = document.getElementById("kpi-leads-percent");

  // ✅ UPDATE VALUES
  if (leadsText) {
    leadsText.innerText = weeklyLeads;
  }

  if (percentText) {
    percentText.innerHTML =
      `${percent >= 0 ? "↑" : "↓"} ${Math.abs(percent)}%`;

    percentText.className =
      percent >= 0 ? "text-success" : "text-danger";
  }

    // ================= TRAFFIC KPI =================
    const trafficTotal = totalLeads;

    const trafficEl = document.getElementById("kpi-traffic");
    const trafficPercentEl = document.getElementById("kpi-traffic-percent");

    if (trafficEl) {
      trafficEl.innerText = trafficTotal;
    }

    if (trafficPercentEl) {
      trafficPercentEl.innerHTML =
        `${percent >= 0 ? "↑" : "↓"} ${Math.abs(percent)}%`;

      trafficPercentEl.className =
        percent >= 0 ? "text-success" : "text-danger";
    }

   // ================= WEEKLY CHART (CEO FRIENDLY) =================
const weeklyEl = document.querySelector("#weekly-marketing-chart");

if (weeklyEl) {
  if (chartWeekly) chartWeekly.destroy();

  const rawData = data.weekly_trend || [];

  // 🔥 GROUP DAILY → WEEKLY
  const weeklyGrouped = [];
  for (let i = 0; i < rawData.length; i += 7) {
    const chunk = rawData.slice(i, i + 7);
    const sum = chunk.reduce((a, b) => a + b, 0);
    weeklyGrouped.push(sum);
  }

  // 🔥 LIMIT TO LAST 12 WEEKS
  const lastWeeks = weeklyGrouped.slice(-12);

  // 🔥 LABELS
  const labels = lastWeeks.map((_, i) => `Week ${i + 1}`);

  // 🔥 AVERAGE LINE
  const avg =
    lastWeeks.reduce((a, b) => a + b, 0) / lastWeeks.length;

  chartWeekly = new ApexCharts(weeklyEl, {
    chart: {
      type: "area",
      height: 300,
      toolbar: { show: false }
    },

    series: [{
      name: "Leads",
      data: lastWeeks
    }],

    xaxis: {
      categories: labels
    },

    stroke: {
      curve: "smooth",
      width: 3
    },

    fill: {
      type: "gradient",
      gradient: {
        opacityFrom: 0.4,
        opacityTo: 0.05
      }
    },

    markers: {
      size: 4
    },

    yaxis: {
      labels: {
        formatter: val => Math.round(val)
      }
    },

    annotations: {
      yaxis: [{
        y: avg,
        borderColor: "#dc3545",
        label: {
          text: "Avg",
          style: {
            background: "#dc3545",
            color: "#fff"
          }
        }
      }]
    },

    tooltip: {
      y: {
        formatter: val => `${val} leads`
      }
    }
  });

  chartWeekly.render();
}

    // ================= DONUT CHART =================
    // ================= DONUT CHART =================
const sourceEl = document.querySelector("#lead-sources-chart");

if (sourceEl) {
  if (chartSources) chartSources.destroy();

  const sourceLabels = Object.keys(sources);
  const sourceValues = sourceLabels.map(l => sources[l]);
  const sourceColors = sourceLabels.map(l => SOURCE_COLORS[l] || "#ccc");

  chartSources = new ApexCharts(sourceEl, {
    chart: { type: "donut", height: 280 },
    series: sourceValues,
    labels: sourceLabels,
    colors: sourceColors, // ✅ SAME COLORS AS BAR
    legend: { position: "bottom" },
    dataLabels: {
      formatter: function (val) {
        return val.toFixed(1) + "%";
      }
    }
  });

  chartSources.render();
}

  } catch (err) {
    console.error("MARKETING ERROR:", err);
  }

  isLoading = false;
}

// ==============================
// 🔥 LOAD TRIGGER (IMPORTANT)
// ==============================
document.addEventListener("DOMContentLoaded", () => {
  refreshPageData();
  setInterval(refreshPageData, 60000);
});
</script>
  </body>
  <!--end::Body-->
</html>
