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
.small-box {
  border-radius: 12px;
}

.small-box:hover {
  transform: translateY(-3px);
  transition: 0.2s ease;
}

.small-box .icon {
  opacity: 0.2;
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
                <a href="./index.php" class="nav-link active">
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
              <div class="col-sm-6"><h3 class="mb-0">CEO Main Dashboard</h3></div>
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
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3 id="kpi-leads">0</h3>
                    <p>Leads This Week</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path
                      d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"
                    ></path>
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 2-->
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3 id="kpi-closed">0</h3>  
                    <p>Closed Sales</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 576 512"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path d="M268.9 85.2L152.3 214.8c-4.6 5.1-4.4 13 .5 17.9 30.5 30.5 80 30.5 110.5 0l31.8-31.8c4.2-4.2 9.5-6.5 14.9-6.9 6.8-.6 13.8 1.7 19 6.9L505.6 376 576 320 576 32 464 96 440.2 80.1C424.4 69.6 405.9 64 386.9 64l-70.4 0c-1.1 0-2.3 0-3.4 .1-16.9 .9-32.8 8.5-44.2 21.1zM116.6 182.7L223.4 64 183.8 64c-25.5 0-49.9 10.1-67.9 28.1L112 96 0 32 0 320 156.4 450.3c23 19.2 52 29.7 81.9 29.7l15.7 0-7-7c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l41 41 9 0c19.1 0 37.8-4.3 54.8-12.3L359 441c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l32 32 17.5-17.5c8.9-8.9 11.5-21.8 7.6-33.1l-137.9-136.8-14.9 14.9c-49.3 49.3-129.1 49.3-178.4 0-23-23-23.9-59.9-2.2-84z"/></path>
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 2-->
              </div>
               <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box" style="background-color:#6f42c1; color:#fff;">
                  <div class="inner">
                    <h3 id="kpi-pipeline">£0</h3>
                    <p>Pipeline Value</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 512 512"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z"/></path>
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                  </a>
                </div>
                <!--end::Small Box Widget 3-->
              </div>
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box" style="background-color:#198754; color:#fff;">
                  <div class="inner">
                    <h3>£123,000</h3>
                    <p>Revenue</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 512 512"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path d="M328 112l-144 0-37.3-74.5c-1.8-3.6-2.7-7.6-2.7-11.6 0-14.3 11.6-25.9 25.9-25.9L342.1 0c14.3 0 25.9 11.6 25.9 25.9 0 4-.9 8-2.7 11.6L328 112zM169.6 160l172.8 0 48.7 40.6C457.6 256 496 338 496 424.5 496 472.8 456.8 512 408.5 512l-305.1 0C55.2 512 16 472.8 16 424.5 16 338 54.4 256 120.9 200.6L169.6 160zM260 224c-11 0-20 9-20 20l0 4c-28.8 .3-52 23.7-52 52.5 0 25.7 18.5 47.6 43.9 51.8l41.7 7c6 1 10.4 6.2 10.4 12.3 0 6.9-5.6 12.5-12.5 12.5L216 384c-11 0-20 9-20 20s9 20 20 20l24 0 0 4c0 11 9 20 20 20s20-9 20-20l0-4.7c25-4.1 44-25.7 44-51.8 0-25.7-18.5-47.6-43.9-51.8l-41.7-7c-6-1-10.4-6.2-10.4-12.3 0-6.9 5.6-12.5 12.5-12.5l47.5 0c11 0 20-9 20-20s-9-20-20-20l-8 0 0-4c0-11-9-20-20-20z"/></path>
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 3-->
              </div>
              <div class="col-lg-4 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3 id="kpi-active">0</h3>
                    <p>Active Projects</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 384 512"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path d="M311.4 32l8.6 0c35.3 0 64 28.7 64 64l0 352c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 96C0 60.7 28.7 32 64 32l8.6 0C83.6 12.9 104.3 0 128 0L256 0c23.7 0 44.4 12.9 55.4 32zM248 112c13.3 0 24-10.7 24-24s-10.7-24-24-24L136 64c-13.3 0-24 10.7-24 24s10.7 24 24 24l112 0zM128 256a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm32 0c0 13.3 10.7 24 24 24l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-112 0c-13.3 0-24 10.7-24 24zm0 128c0 13.3 10.7 24 24 24l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-112 0c-13.3 0-24 10.7-24 24zM96 416a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"/></path>
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 3-->
              </div>
               <div class="col-lg-4 col-6">
                <!--begin::Small Box Widget 4-->
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3>65</h3>
                    <p>Outstanding Invoices</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 384 512"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                  <path d="M0 64C0 28.7 28.7 0 64 0L213.5 0c17 0 33.3 6.7 45.3 18.7L365.3 125.3c12 12 18.7 28.3 18.7 45.3L384 448c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64zm208-5.5l0 93.5c0 13.3 10.7 24 24 24L325.5 176 208 58.5zM64 88c0 13.3 10.7 24 24 24l48 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64C74.7 64 64 74.7 64 88zm0 96c0 13.3 10.7 24 24 24l48 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-48 0c-13.3 0-24 10.7-24 24zm112 76l0 4c-28.8 .3-52 23.7-52 52.5 0 25.7 18.5 47.6 43.9 51.8l41.7 7c6 1 10.4 6.2 10.4 12.3 0 6.9-5.6 12.5-12.5 12.5L152 400c-11 0-20 9-20 20s9 20 20 20l24 0 0 4c0 11 9 20 20 20s20-9 20-20l0-4.7c25-4.1 44-25.7 44-51.8 0-25.7-18.5-47.6-43.9-51.8l-41.7-7c-6-1-10.4-6.2-10.4-12.3 0-6.9 5.6-12.5 12.5-12.5l47.5 0c11 0 20-9 20-20s-9-20-20-20l-8 0 0-4c0-11-9-20-20-20s-20 9-20 20z"/></path>
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 4-->
              </div>
              <div class="col-lg-4 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3 id"kpi-alerts">0</h3>
                    <p>Issues / Alerts</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 512 512"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path d="M256 0c14.7 0 28.2 8.1 35.2 21l216 400c6.7 12.4 6.4 27.4-.8 39.5S486.1 480 472 480L40 480c-14.1 0-27.2-7.4-34.4-19.5s-7.5-27.1-.8-39.5l216-400c7-12.9 20.5-21 35.2-21zm0 352a32 32 0 1 0 0 64 32 32 0 1 0 0-64zm0-192c-18.2 0-32.7 15.5-31.4 33.7l7.4 104c.9 12.5 11.4 22.3 23.9 22.3 12.6 0 23-9.7 23.9-22.3l7.4-104c1.3-18.2-13.1-33.7-31.4-33.7z"/></path>
                  </svg>
                  <a
                    href="#"
                    class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 3-->
              </div>
              <!--end::Col-->
             
             
              
              
              <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
              <!-- Start col -->
              
              <!-- /.Start col -->
              <!-- Start col -->
              
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
    <script>
let isLoading = false;

async function loadDashboardData() {
  if (isLoading) return;
  isLoading = true;

  try {
    // ✅ prevent caching (VERY IMPORTANT)
    const response = await fetch('api/get_pipeline.php?_=' + new Date().getTime());

    const data = await response.json();

    console.log("REFRESHED DATA:", data);

    // Update UI
    document.getElementById('kpi-leads').innerText = data.weekly_leads;
    document.getElementById('kpi-closed').innerText = data.closed_sales;
    document.getElementById('kpi-pipeline').innerText =
      "£" + Number(data.pipeline_value).toLocaleString();

  } catch (error) {
    console.error("Error loading dashboard:", error);
  }

  isLoading = false;
}

// ✅ Initial load
loadDashboardData();

// ✅ Auto refresh every 10s
setInterval(() => {
  console.log("⏱ Refresh triggered...");
  loadDashboardData();
}, 10000);
</script>
 <script>
const SHEET_URL = "https://opensheet.elk.sh/1t-DzS_uln-2JrXvqwawU_g2UVepmVPIZ8sCCO-ng_LU/API";

async function loadProjects() {
  try {
    const res = await fetch(SHEET_URL);
    const data = await res.json();

    console.log("PROJECT DATA:", data);

    if (!Array.isArray(data)) {
      console.error("Invalid API response", data);
      return;
    }

    // ==========================
    // KPI CALCULATION (UPDATED)
    // ==========================
    const total = data.length;

    const active = data.filter(p => 
      (p.status || "").toLowerCase() === "active"
    ).length;

    const completed = data.filter(p => 
      (p.status || "").toLowerCase() === "completed"
    ).length;

    const pending = data.filter(p => 
      (p.status || "").toLowerCase() === "on hold" ||
      (p.status || "").toLowerCase() === "pending"
    ).length;

    // SAFE UPDATE
    const setText = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.innerText = value;
    };

    setText("kpi-total", total);
    setText("kpi-active", active);
    setText("kpi-completed", completed);
    setText("kpi-pending", pending);

    // ==========================
    // TABLE (keep your existing)
    // ==========================
    const table = document.getElementById("projects-table-body");
    if (!table) return;

    table.innerHTML = "";

    data.forEach(p => {
      const progress = parseInt(p.progress) || 0;

      table.innerHTML += `
        <tr class="${p.delay === "Yes" ? 'table-danger' : ''}">
          <td>${p.client}</td>
          <td>${p.project}</td>
          <td><span class="badge bg-primary">${p.stage}</span></td>
          <td>
            <div class="progress">
              <div class="progress-bar ${progress < 40 ? 'bg-danger' : progress < 70 ? 'bg-warning' : 'bg-success'}" 
                   style="width:${progress}%">
                ${progress}%
              </div>
            </div>
          </td>
          <td>${p.next_update || '-'}</td>
          <td>${p.delay === "Yes" ? '<span class="badge bg-danger">Delayed</span>' : '<span class="badge bg-success">No</span>'}</td>
          <td><span class="badge bg-success">${p.site_visit}</span></td>
        </tr>
      `;
    });

  } catch (err) {
    console.error("Projects error:", err);
  }
}

// LOAD
document.addEventListener("DOMContentLoaded", function () {
  loadProjects();
  setInterval(loadProjects, 60000);
});
</script>
<script>
let systemChart;
let complianceChart;

async function loadSecurityData() {
  try {
    const res = await fetch('api/get_security.php');
    const data = await res.json();

    console.log("SECURITY DATA:", data);

    // ==========================
    // KPI CARDS
    // ==========================
    const setText = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.innerText = value;
    };

    setText('kpi-domains', data.domains);
    setText('kpi-ssl', data.ssl_healthy);
    setText('kpi-mfa', data.mfa);
    setText('kpi-email', data.email_health);
    setText('kpi-backups', data.backups);
    setText('kpi-alerts', data.alerts);

    // ==========================
    // SYSTEM HEALTH CHART
    // ==========================
    const systemEl = document.querySelector("#system-health-chart");

    if (systemEl) {
      const systemData = [
        data.system_health.healthy,
        data.system_health.warning,
        data.system_health.critical
      ];

      if (systemChart) {
        systemChart.updateSeries(systemData);
      } else {
        systemChart = new ApexCharts(systemEl, {
          series: systemData,
          chart: { type: 'donut', height: 280 },
          labels: ['Healthy', 'Warning', 'Critical'],
          colors: ['#198754', '#ffc107', '#dc3545']
        });

        systemChart.render();
      }
    }

    // ==========================
    // COMPLIANCE CHART
    // ==========================
    const complianceEl = document.querySelector("#security-chart");

    if (complianceEl) {
      const complianceData = [
        data.compliance.mfa,
        data.compliance.backups,
        data.compliance.dns,
        data.compliance.ssl
      ];

      if (complianceChart) {
        complianceChart.updateSeries([{ data: complianceData }]);
      } else {
        complianceChart = new ApexCharts(complianceEl, {
          series: [{ data: complianceData }],
          chart: { type: 'bar', height: 300 },
          xaxis: {
            categories: ['MFA', 'Backups', 'DNS', 'SSL']
          },
          colors: ['#ffc107', '#198754', '#0d6efd', '#20c997']
        });

        complianceChart.render();
      }
    }

    // ==========================
    // TABLE (🔥 THIS WAS MISSING)
    // ==========================
    const table = document.getElementById('security-table-body');

    if (table && data.domains_list) {
      table.innerHTML = '';

      data.domains_list.forEach(domain => {

        const badge = (ok) =>
          ok
            ? '<span class="badge bg-success">OK</span>'
            : '<span class="badge bg-danger">Missing</span>';

        const statusBadge = domain.ssl
          ? '<span class="badge bg-success">Healthy</span>'
          : '<span class="badge bg-danger">Issue</span>';

        table.innerHTML += `
          <tr>
            <td>${domain.name}</td>
            <td>Domain</td>
            <td>${statusBadge}</td>
            <td>${badge(domain.spf)}</td>
            <td>${badge(domain.dkim)}</td>
            <td>${badge(domain.dmarc)}</td>
            <td>Today</td>
            <td>${domain.ssl ? 'Secure' : 'SSL Issue'}</td>
          </tr>
        `;
      });
    }

  } catch (error) {
    console.error("Dashboard Error:", error);
  }
}

// ==========================
// LOAD + AUTO REFRESH
// ==========================
loadSecurityData();
setInterval(loadSecurityData, 60000);
</script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
