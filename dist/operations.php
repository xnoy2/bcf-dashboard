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
              <a href="./finance.php" class="nav-link">
                <i class="nav-icon fa-solid fa-sack-dollar"></i>
                <p>Finance</p>
              </a>
            </li>
            <li class="nav-header">OPERATIONS</li>
            <li class="nav-item">
              <a href="./operations.php" class="nav-link active">
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
              <div class="col-sm-6"><h3 class="mb-0">Operations</h3></div>
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

            <!-- Jobs in Progress -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-primary shadow-sm">
                  <i class="fas fa-tools"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Jobs</span>
                  <span class="info-box-number">8</span>
                </div>
              </div>
            </div>

            <!-- Installations -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-success shadow-sm">
                  <i class="fas fa-calendar-check"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Installs</span>
                  <span class="info-box-number">3</span>
                </div>
              </div>
            </div>

            <!-- Delayed Jobs -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-danger shadow-sm">
                  <i class="fas fa-clock"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Delayed</span>
                  <span class="info-box-number">2</span>
                </div>
              </div>
            </div>

            <!-- Warranty -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-warning shadow-sm">
                  <i class="fas fa-hard-hat"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Warranty</span>
                  <span class="info-box-number">1</span>
                </div>
              </div>
            </div>

            <!-- Site Visits -->
            <div class="col-12 col-sm-6 col-md-2">
              <div class="info-box">
                <span class="info-box-icon text-bg-info shadow-sm">
                  <i class="fas fa-map-marker-alt"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text">Site Visits</span>
                  <span class="info-box-number">6</span>
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
                <h3 class="card-title">Project Progress</h3>
              </div>
              <div class="card-body">

                <p>Smith Family Garden Room</p>
                <div class="progress mb-3">
                  <div class="progress-bar bg-success" style="width: 65%">65%</div>
                </div>

                <p>Brown Play Area</p>
                <div class="progress mb-3">
                  <div class="progress-bar bg-warning" style="width: 30%">30%</div>
                </div>

                <p>Wilson Garden Room</p>
                <div class="progress mb-3">
                  <div class="progress-bar bg-primary" style="width: 15%">15%</div>
                </div>

              </div>
            </div>
            </div>
            <div class="col-md-6">
            <div class="card mb-4">
            <div class="card-header">
              <h3 class="card-title">Jobs by Status</h3>
            </div>
            <div class="card-body">
              <div id="jobs-status-chart"></div>
            </div>
          </div>
          </div>
            </div>
            <div class="card">
            <div class="card-header">
              <h3 class="card-title">Upcoming Schedule</h3>
            </div>
            <div class="card-body">

              <ul class="list-group">

                <li class="list-group-item">
                  <i class="fas fa-calendar-check text-success"></i>
                  Smith Family - Installation (12 Mar)
                </li>

                <li class="list-group-item">
                  <i class="fas fa-tools text-primary"></i>
                  Brown Play Area - Site Prep (13 Mar)
                </li>

                <li class="list-group-item">
                  <i class="fas fa-clock text-warning"></i>
                  Wilson Project - Delay Review (14 Mar)
                </li>

                <li class="list-group-item">
                  <i class="fas fa-map-marker-alt text-info"></i>
                  Site Visit - Johnson Client (15 Mar)
                </li>

              </ul>

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
    <script src="./js/account_ui.js"></script>

    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>
    <!-- sortablejs -->
    

    <script>
      new Sortable(document.querySelector('.connectedSortable'), {
        group: 'shared',
        handle: '.card-header',
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>
    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>
    <!-- ChartJS -->
    <script>
const jobs_status_chart_options = {
  series: [
    {
      name: 'Jobs',
      data: [4, 8, 2, 5],
    }
  ],
  chart: {
    type: 'bar',
    height: 300,
    toolbar: { show: false }
  },
  colors: ['#0d6efd'],
  plotOptions: {
    bar: {
      distributed: true,
      borderRadius: 6,
    }
  },
  xaxis: {
    categories: ['Planned', 'In Progress', 'Delayed', 'Completed']
  },
  colors: ['#0d6efd', '#20c997', '#dc3545', '#198754'],
  tooltip: {
    y: {
      formatter: val => val + ' jobs'
    }
  }
};

new ApexCharts(document.querySelector("#jobs-status-chart"), jobs_status_chart_options).render();
</script>

    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"
    ></script>
    <!-- jsvectormap -->
    <script>
      // World map by jsVectorMap
      new jsVectorMap({
        selector: '#world-map',
        map: 'world',
      });

      // Sparkline charts
      const option_sparkline1 = {
        series: [
          {
            data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
      sparkline1.render();

      const option_sparkline2 = {
        series: [
          {
            data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
      sparkline2.render();

      const option_sparkline3 = {
        series: [
          {
            data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
      sparkline3.render();
    </script>
    <!--end::Script-->
    <script>
  const visitors_chart_options = {
    series: [
      {
        name: 'Visitors',
        data: [100, 120, 170, 167, 180, 177, 160],
      },
    ],
    chart: {
      height: 200,
      type: 'line',
      toolbar: {
        show: false,
      },
    },
    colors: ['#0d6efd'],
    stroke: {
      curve: 'smooth',
    },
    grid: {
      borderColor: '#e7e7e7',
      row: {
        colors: ['#f3f3f3', 'transparent'],
        opacity: 0.5,
      },
    },
    legend: {
      show: false,
    },
    markers: {
      size: 1,
    },
    xaxis: {
      categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    },
  };

  const visitors_chart = new ApexCharts(
    document.querySelector('#visitors-chart'),
    visitors_chart_options
  );
  visitors_chart.render();

  const sales_chart_options = {
  series: [
    {
      name: 'Leads',
      data: [14, 18, 24, 9, 6],
    },
  ],
  chart: {
    type: 'bar',
    height: 250,
    toolbar: {
      show: false,
    },
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '45%',
      borderRadius: 6,
      distributed: true // ✅ important for multiple colors
    },
  },
  legend: {
    show: false,
  },
  colors: ['#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6f42c1'], // ✅ 5 colors
  dataLabels: {
    enabled: false,
  },
  stroke: {
    show: false,
  },
  xaxis: {
    categories: ['Meta Ads', 'Google', 'Organic', 'TikTok', 'Referral'],
  },
  yaxis: {
    title: {
      text: 'Leads by Channel',
    },
  },
  fill: {
    opacity: 1,
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + ' leads';
      },
    },
  },
};

  const sales_chart = new ApexCharts(
    document.querySelector('#sales-chart'),
    sales_chart_options
  );
  sales_chart.render();
</script>
<script>
 const cashflow_chart_options = {
  series: [
    {
      name: 'Cash In',
      data: [12000, 18000, 15000, 23000],
    },
    {
      name: 'Cash Out',
      data: [8000, 12000, 10000, 9500],
    }
  ],
  chart: {
    type: 'line',
    height: 300,
    toolbar: { show: false }
  },
  colors: ['#198754', '#dc3545'],
  stroke: { curve: 'smooth', width: 3 },
  xaxis: {
    categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4']
  },
  tooltip: {
    y: {
      formatter: val => '£' + val
    }
  }
};

new ApexCharts(document.querySelector("#cashflow-chart"), cashflow_chart_options).render();
</script>

  </body>
  <!--end::Body-->
</html>
