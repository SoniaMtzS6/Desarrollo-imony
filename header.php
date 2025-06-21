<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: authentication-two-steps.php");
    exit;
}
?>

<aside class="left-sidebar with-vertical">
      <!-- ---------------------------------- -->
      <!-- Start Vertical Layout Sidebar -->
      <!-- ---------------------------------- -->

      <nav class="sidebar-nav scroll-sidebar" data-simplebar>
        <ul id="sidebarnav">
          <!-- ---------------------------------- -->
          <!-- Gestion -->
          <!-- ---------------------------------- -->
          <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu" id="get-url">Gestión de la plataforma</span>
          </li>
          <!-- ---------------------------------- -->
          <!-- contenido -->
          <!-- ---------------------------------- -->

          <?php if (isset($_SESSION["usuario"]) && $_SESSION["usuario"]["perfil"] === "Superadministrador"): ?>
          <li class="sidebar-item">
            <a href="empresas.php"  class="sidebar-link">
              <span class="aside-icon p-2">
                <iconify-icon icon="solar:chart-bold-duotone" class="fs-6"></iconify-icon>
              </span>
              <span class="hide-menu">Empresas</span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="administradores.php" class="sidebar-link">
              <span class="aside-icon p-2">
                <iconify-icon icon="solar:wallet-bold-duotone" class="fs-6"></iconify-icon>
              </span>
              <span class="hide-menu">Administradores</span>
            </a>
          </li>
          <?php endif; ?>

          <li class="sidebar-item">
            <a href="usuarios.php" class="sidebar-link">
              <span class="aside-icon p-2">
                <iconify-icon icon="solar:graph-up-bold-duotone" class="fs-6"></iconify-icon>
              </span>
              <span class="hide-menu">Usuarios</span>
            </a>
          </li>
          
          <!-- ---------------------------------- -->
          <!-- Tarjetas -->
          <!-- ---------------------------------- -->
          <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">Administracion de empresa</span>
          </li>
          <!-- ---------------------------------- -->
          <!-- Tarjetas -->
          <!-- ---------------------------------- -->

          <li class="sidebar-item">
            <a href="reporteempresa.php" class="sidebar-link">
              <span class="aside-icon p-2">
                <iconify-icon icon="solar:link-bold-duotone" class="fs-6"></iconify-icon>
              </span>
              <span class="hide-menu">Balance de Cuenta</span>
            </a>
          </li>
          
          <li class="sidebar-item">
            <a href="servicios/reporte_settlements_v2.php?id_empresa=<?php echo $_SESSION['usuario']['id_empresa']; ?>" class="sidebar-link">
              <span class="aside-icon p-2">
                <iconify-icon icon="solar:bank-bold-duotone" class="fs-6"></iconify-icon>
              </span>
              <span class="hide-menu">Reporte Settlements</span>
            </a>
          </li>
        </ul>
      </nav>

      <!-- ---------------------------------- -->
      <!-- Start Vertical Layout Sidebar -->
      <!-- ---------------------------------- -->
    </aside>
    <!--  Sidebar End -->
    <div class="page-wrapper">
      <!--  Header Start -->
      <header class="topbar">
        <div class="with-vertical"><!-- ---------------------------------- -->
          <!-- Start Vertical Layout Header -->
          <!-- ---------------------------------- -->
          <nav class="navbar navbar-expand-lg p-0">
            <div class="d-none d-lg-block">
              <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="empresas.php" class="text-nowrap logo-img">
                  <img src="img/logo.png" class="circle-bottom" style="width: 150px;padding-left: 20px;" alt="Logo-white" />
                </a>
              </div>
            </div>
            <ul class="navbar-nav">
              <li class="nav-item nav-icon-hover-bg rounded-circle">
                <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                  <iconify-icon icon="solar:list-bold-duotone" class="fs-6"></iconify-icon>
                </a>
              </li>
            </ul>

          
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between">
               
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                  <li class="nav-item dropdown d-flex d-lg-none nav-icon-hover-bg rounded-circle">
                    <a class="nav-link position-relative" href="javascript:void(0)" id="drop3" aria-expanded="false">
                      <iconify-icon icon="solar:magnifer-linear" class="fs-6"></iconify-icon>
                    </a>
                    <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop3">
                      <!--  Search Bar -->

                      
                      <div class="message-body p-3" data-simplebar="">
                        <h5 class="mb-0 fs-5 p-1">Quick Page Links</h5>
                        <ul class="list mb-0 py-2">
                          
                        </ul>
                      </div>
                    </div>
                  </li>

                  <!-- ------------------------------- -->
                  <!-- end language Dropdown -->
                  <!-- ------------------------------- -->

                  <li class="nav-item">
                   
                   
                  </li>

                  <!-- ------------------------------- -->
                  <!-- start Shortcut Dropdown -->
                  <!-- ------------------------------- -->
                
                  <!-- ------------------------------- -->
                  <!-- end Shortcut Dropdown -->
                  <!-- ------------------------------- -->

                  <!-- ------------------------------- -->
                  <!-- start notification Dropdown -->
                  <!-- ------------------------------- -->
                
                  <!-- ------------------------------- -->
                  <!-- end notification Dropdown -->
                  <!-- ------------------------------- -->

                  <!-- ------------------------------- -->
                  <!-- start profile Dropdown -->
                  <!-- ------------------------------- -->
                  <li class="nav-item dropdown">
                    <a class="nav-link position-relative ms-6" href="javascript:void(0)" id="drop1" aria-expanded="false">
                      <div class="d-flex align-items-center flex-shrink-0 gap-6">
                        <div class="user-profile">
                          <!-- <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/profile/user-1.jpg" width="40" class="rounded-circle" alt="seodash-img" /> -->
                        </div>
                        <span class="d-sm-none d-block"><iconify-icon icon="solar:alt-arrow-down-line-duotone"></iconify-icon></span>

                        <div class="d-none d-sm-block">
                          <h6 class="mb-0 profile-name"><?php echo $_SESSION["usuario"]["nombre"] ?></h6>
                          <p class="fs-3 text-body-color lh-base mb-0">
                          <?php echo $_SESSION["usuario"]["perfil"] ?>
                          </p>
                        </div>
                      </div>
                    </a>
                    <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                      <div class="profile-dropdown position-relative" data-simplebar>
                        <div class="pt-3 px-7">
                          <h3 class="mb-0 fs-5">Perfil</h3>
                        </div>

                        <div class="d-flex align-items-center mx-7 py-9 border-bottom">
                          <!-- <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/profile/user-1.jpg" alt="user" width="90" class="rounded-circle" /> -->
                          <div class="ms-4">
                            <h4 class="mb-0 fs-5"><?php echo $_SESSION["usuario"]["nombre"] ?></h4>
                            <span><?php echo $_SESSION["usuario"]["perfil"] ?></span>
                            <p class="mb-0 mt-1 d-flex align-items-center">
                              <iconify-icon icon="solar:mailbox-line-duotone" class="fs-4 me-1"></iconify-icon>
                              <?php echo $_SESSION["usuario"]["email"] ?>
                            </p>
                          </div>
                        </div>

                      

                        <div class="py-6 px-7 mb-1">
                          <a href="servicios/logout.php" class="btn btn-primary w-100">Salir</a>
                        </div>
                      </div>
                    </div>
                  </li>
                  <!-- ------------------------------- -->
                  <!-- end profile Dropdown -->
                  <!-- ------------------------------- -->
                </ul>
              </div>
            </div>
          </nav>
          <!-- ---------------------------------- -->
          <!-- End Vertical Layout Header -->
          <!-- ---------------------------------- -->

          <!-- ------------------------------- -->
          <!-- apps Dropdown in Small screen -->
          <!-- ------------------------------- -->
          <!--  Mobilenavbar -->
          <div class="offcanvas offcanvas-start dropdown-menu-nav-offcanvas" data-bs-scroll="true" tabindex="-1" id="mobilenavbar" aria-labelledby="offcanvasWithBothOptionsLabel">
            <nav class="sidebar-nav scroll-sidebar">
              <div class="offcanvas-header justify-content-between">
                <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" alt="seodash-img" class="img-fluid" />
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body h-n80" data-simplebar="">
                <ul id="sidebarnav">
                  <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                      <span class="aside-icon p-2">
                        <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                      </span>
                      <span class="hide-menu ps-1">Apps</span>
                    </a>

                    <ul aria-expanded="false" class="collapse my-3 ps-3">
                      <li class="sidebar-item py-2">
                        <a href="https://bootstrapdemos.adminmart.com/seodash/dist/dark/app-chat.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-primary-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:chat-square-call-bold-duotone" class="fs-7 text-primary"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Chat Application</h6>
                            <span class="d-block text-body-color fs-11">New messages arrived</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="https://bootstrapdemos.adminmart.com/seodash/dist/dark/app-invoice.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-secondary-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:checklist-minimalistic-bold-duotone" class="fs-7 text-secondary"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Invoice App</h6>
                            <span class="d-block text-body-color fs-11">Get latest invoice</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="page-backlink.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-warning-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:link-bold-duotone" class="fs-7 text-warning"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Blacklinks</h6>
                            <span class="d-block text-body-color fs-11">Get new Blacklinks</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="page-site-structure.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-danger-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:layers-bold-duotone" class="fs-7 text-danger"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Site Structure</h6>
                            <span class="d-block text-body-color fs-11">Get new Site Structure</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="page-realtime.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-warning-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:graph-up-bold-duotone" class="fs-7 text-warning"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Real Time</h6>
                            <span class="d-block text-body-color fs-11">Get latest Real Time</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="https://bootstrapdemos.adminmart.com/seodash/dist/dark/app-calendar.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-success-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:calendar-mark-bold-duotone" class="fs-7 text-success"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Calendar App</h6>
                            <span class="d-block text-body-color fs-11">Get dates</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="page-organic-competitors.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-primary-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:server-minimalistic-bold-duotone" class="fs-7 text-primary"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Organic Competitors</h6>
                            <span class="d-block text-body-color fs-11">Get latest Competitors</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="https://bootstrapdemos.adminmart.com/seodash/dist/dark/app-notes.php" class="d-flex align-items-center pb-9 position-relative">
                          <div class="bg-secondary-subtle rounded-3 me-3 round-40 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:notes-bold-duotone" class="fs-7 text-secondary"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0 bg-hover-primary">Notes Application</h6>
                            <span class="d-block text-body-color fs-11">To-do and Daily tasks</span>
                          </div>
                        </a>
                      </li>
                      <ul class="px-8 mt-6 mb-4">
                        <li class="sidebar-item mb-3">
                          <h5 class="fs-5 fw-semibold">Quick Links</h5>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fw-semibold text-dark" href="https://bootstrapdemos.adminmart.com/seodash/dist/dark/page-pricing.php">Pricing Page</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fw-semibold text-dark" href="authentication-login.php">Authentication Design</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fw-semibold text-dark" href="authentication-register.php">Register Now</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fw-semibold text-dark" href="authentication-error.php">404 Error Page</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fw-semibold text-dark" href="https://bootstrapdemos.adminmart.com/seodash/dist/dark/app-notes.php">Notes App</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fw-semibold text-dark" href="page-realtime.php">Real Time</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fw-semibold text-dark" href="page-account-settings.php">Account Settings</a>
                        </li>
                      </ul>
                    </ul>
                  </li>
                </ul>
              </div>
            </nav>
          </div>
        </div>
  
      </header>
      <!--  Header End -->

