<?php
session_start();
require_once 'functions.php';

// Validar que el usuario está en sesión.
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// --- Lógica de carga de datos ---
$admin = null;
$empresa_options = ''; // Renombrado para evitar confusión
$idAdmin = $_GET['id'] ?? null;
$id_empresa_session = $_SESSION['usuario']['id_empresa'] ?? null; // Estandarizar

if (!$idAdmin) {
    die("No se ha especificado un ID de administrador.");
}

// Determinar el entorno
$is_production = (strpos($_SERVER['HTTP_HOST'], 'elasticbeanstalk.com') !== false);

if ($is_production) {
    // --- Código de PRODUCCIÓN (API) ---
    // 1. Obtener detalles del administrador usando el endpoint de LISTA con el ID
    $curl_admin = curl_init();
    curl_setopt_array($curl_admin, array(
        CURLOPT_URL => 'https://9kjot10cte.execute-api.us-east-2.amazonaws.com/dev/admin/list?id=' . urlencode($idAdmin),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_TIMEOUT => 30
    ));
    $response_admin = curl_exec($curl_admin);
    curl_close($curl_admin);
    if ($response_admin) {
        $admin_data = json_decode($response_admin, true);
        $admin = $admin_data['data'][0] ?? null; // El resultado es un array, tomamos el primer elemento
    }

    // 2. Obtener lista de empresas
    $curl_empresas = curl_init();
    curl_setopt_array($curl_empresas, array(
        CURLOPT_URL => 'https://9kjot10cte.execute-api.us-east-2.amazonaws.com/dev/empresa/list', // Asumiendo este endpoint
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET', // Asumiendo GET para listas
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_TIMEOUT => 30
    ));
    $response_empresas = curl_exec($curl_empresas);
    curl_close($curl_empresas);
    if ($response_empresas) {
        $empresas_data = json_decode($response_empresas, true);
        if (isset($empresas_data['data'])) {
            foreach ($empresas_data['data'] as $row) {
                $selected = ($admin && $admin['idEmpresa'] == $row['ID_EMPRESA']) ? 'selected' : '';
                $empresa_options .= '<option value="' . htmlspecialchars($row['ID_EMPRESA']) . '" ' . $selected . '>' . htmlspecialchars($row['NOMBRE_EMPRESA']) . '</option>';
            }
        }
    }

} else {
    // --- Código LOCAL (Base de Datos Directa) ---
$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

    // 1. Obtener detalles del administrador
    $stmt = $conn->prepare("SELECT * FROM administradores WHERE id = ?");
    $stmt->bind_param("i", $idAdmin);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    // 2. Obtener lista de empresas
$resultEmp = $conn->query("SELECT ID_EMPRESA, NOMBRE_EMPRESA FROM empresas");
while ($row = $resultEmp->fetch_assoc()) {
        $selected = ($admin && ($admin['idEmpresa'] ?? '') == $row['ID_EMPRESA']) ? 'selected' : '';
        $empresa_options .= '<option value="' . htmlspecialchars($row['ID_EMPRESA']) . '" ' . $selected . '>' . htmlspecialchars($row['NOMBRE_EMPRESA']) . '</option>';
}
    $conn->close();
}

if (!$admin) {
    die("No se encontró al administrador con el ID proporcionado.");
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-account-settings.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:21 GMT -->
<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>Interestellar Admin - Editar Administrador</title>
</head>

<body class="link-sidebar">
  <!-- Preloader -->
  <div class="preloader">
    <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
      <?php include 'header.php'; ?>
      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="mb-4">
            <div class="row align-items-center">
              <div class="col-md-6 col-lg-5">
                <h4 class="mb-8 breadcrumb-title"> <span class="text-primary">Administradores</span>
                </h4>
              
              </div>
              <div class="col-md-6 col-lg-7">
                
              </div>
            </div>
          </div>
          <div class="card">
            <ul class="nav nav-pills user-profile-tab" id="pills-tab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 active d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true">
                  <i class="ti ti-user-circle me-2 fs-6"></i>
                  <span class="d-none d-md-block">Cuenta</span>
                </button>
              </li>
              <!-- <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-notifications-tab" data-bs-toggle="pill" data-bs-target="#pills-notifications" type="button" role="tab" aria-controls="pills-notifications" aria-selected="false">
                  <i class="ti ti-bell me-2 fs-6"></i>
                  <span class="d-none d-md-block">Notifications</span>
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-bills-tab" data-bs-toggle="pill" data-bs-target="#pills-bills" type="button" role="tab" aria-controls="pills-bills" aria-selected="false">
                  <i class="ti ti-article me-2 fs-6"></i>
                  <span class="d-none d-md-block">Bills</span>
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" aria-controls="pills-security" aria-selected="false">
                  <i class="ti ti-lock me-2 fs-6"></i>
                  <span class="d-none d-md-block">Security</span>
                </button>
              </li> -->
            </ul>
            <div class="card-body">
              <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">
                  <div class="row">
                  
                    <div class="col-12">
                      <div class="card w-100 border position-relative overflow-hidden mb-0">
                        <div class="card-body p-4">
                          <h4 class="card-title">Detalles de la cuenta</h4>
                          <p class="card-subtitle mb-4">Para cambiar los detalles de la cuenta, edita y guarda los cambios.</p>
                          <form id="editAdminForm" method="POST" action="servicios/editaradmin.php">
                            <input type="hidden" name="idadmin" value="<?php echo htmlspecialchars($admin['id']); ?>">
                            <input type="hidden" name="action" id="formAction" value="">
                            <div class="row">
                                <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($admin['nombre'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Empresa</label>
                                    <select class="form-select" name="idEmpresa">
                                    <?php echo $empresa_options; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>">
                                </div>
                                </div>
                                <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Alias</label>
                                    <input type="text" class="form-control" name="alias" value="<?php echo htmlspecialchars($admin['nombre'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Perfil</label>
                                    <select class="form-select" name="perfil">
                                    <option value="Administrador" <?php if (($admin['perfil'] ?? '') === 'Administrador') echo 'selected'; ?>>Administrador</option>
                                    <option value="Superadministrador" <?php if (($admin['perfil'] ?? '') === 'Superadministrador') echo 'selected'; ?>>Superadmin</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono" value="<?php echo htmlspecialchars($admin['telefono'] ?? ''); ?>">
                                </div>
                                </div>
                                <div class="col-12">
                                <div>
                                    <label class="form-label">Dirección</label>
                                    <input type="text" class="form-control" name="direccion" value="<?php echo htmlspecialchars($admin['direccion'] ?? ''); ?>">
                                </div>
                                </div>
                            </div>
                            
                                <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="d-flex gap-3">
                                        <?php if (isset($admin['status']) && $admin['status'] === 'BLOCKED'): ?>
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmDesbloquearModal">Desbloquear</button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#confirmBloquearModal">Bloquear</button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmEliminarModal">Eliminar</button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-dark" id="guardarBtn">Guardar</button>
                                        <a href="administradores.php" class="btn btn-light-danger ms-2">Cancelar</a>
                                </div>
                                </div>
                            </div>
                            </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
              </div>
            </div>
          </div>
          <div class="text-center py-3">
            <p class="mb-0">2024 © Interstellar derechos</p>
          </div>
        </div>
      </div>
      <script>
  function handleColorTheme(e) {
    document.documentElement.setAttribute("data-color-theme", e);
  }
</script>
      <button class="btn btn-primary p-3 rounded-circle d-flex align-items-center justify-content-center customizer-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
        <i class="icon ti ti-settings fs-7"></i>
      </button>

      <div class="offcanvas customizer offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
          <h4 class="offcanvas-title fw-semibold" id="offcanvasExampleLabel">
            Settings
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body h-n80" data-simplebar>
          <h6 class="fw-semibold fs-4 mb-2">Theme</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check light-layout" name="theme-layout" id="light-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="light-layout">
              <i class="icon ti ti-brightness-up fs-7 me-2"></i>Light
            </label>

            <input type="radio" class="btn-check dark-layout" name="theme-layout" id="dark-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="dark-layout">
              <i class="icon ti ti-moon fs-7 me-2"></i>Dark
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Direction</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="direction-l" id="ltr-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="ltr-layout">
              <i class="icon ti ti-text-direction-ltr fs-7 me-2"></i>LTR
            </label>

            <input type="radio" class="btn-check" name="direction-l" id="rtl-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="rtl-layout">
              <i class="icon ti ti-text-direction-rtl fs-7 me-2"></i>RTL
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Colors</h6>

          <div class="d-flex flex-row flex-wrap gap-3 customizer-box color-pallete" role="group">
            <input type="radio" class="btn-check" name="color-theme-layout" id="Blue_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Blue_Theme')" for="Blue_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="BLUE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-1">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Aqua_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Aqua_Theme')" for="Aqua_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="AQUA_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-2">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Purple_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Purple_Theme')" for="Purple_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="PURPLE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-3">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="green-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Green_Theme')" for="green-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="GREEN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-4">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="cyan-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Cyan_Theme')" for="cyan-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="CYAN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-5">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="orange-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Orange_Theme')" for="orange-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="ORANGE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-6">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Layout Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="vertical-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="vertical-layout">
                <i class="icon ti ti-layout-sidebar-right fs-7 me-2"></i>Vertical
              </label>
            </div>
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="horizontal-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="horizontal-layout">
                <i class="icon ti ti-layout-navbar fs-7 me-2"></i>Horizontal
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Container Option</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="layout" id="boxed-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="boxed-layout">
              <i class="icon ti ti-layout-distribute-vertical fs-7 me-2"></i>Boxed
            </label>

            <input type="radio" class="btn-check" name="layout" id="full-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="full-layout">
              <i class="icon ti ti-layout-distribute-horizontal fs-7 me-2"></i>Full
            </label>
          </div>

          <h6 class="fw-semibold fs-4 mb-2 mt-5">Sidebar Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <a href="javascript:void(0)" class="fullsidebar">
              <input type="radio" class="btn-check" name="sidebar-type" id="full-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="full-sidebar">
                <i class="icon ti ti-layout-sidebar-right fs-7 me-2"></i>Full
              </label>
            </a>
            <div>
              <input type="radio" class="btn-check " name="sidebar-type" id="mini-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="mini-sidebar">
                <i class="icon ti ti-layout-sidebar fs-7 me-2"></i>Collapse
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Card With</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="card-layout" id="card-with-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="card-with-border">
              <i class="icon ti ti-border-outer fs-7 me-2"></i>Border
            </label>

            <input type="radio" class="btn-check" name="card-layout" id="card-without-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="card-without-border">
              <i class="icon ti ti-border-none fs-7 me-2"></i>Shadow
            </label>
          </div>
        </div>
      </div>
    </div>
    <div class="dark-transparent sidebartoggler"></div>
  </div>
  <!-- Import Js Files -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.dark.init.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.min.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/sidebarmenu.js"></script>

  <!-- solar icons -->
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

  <!-- Modals -->
  <!-- Bloquear Modal -->
  <div class="modal fade" id="confirmBloquearModal" tabindex="-1" aria-labelledby="confirmBloquearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmBloquearModalLabel">Confirmar Bloqueo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que quieres bloquear a este administrador?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-warning" id="confirmBloquearBtn">Sí, bloquear</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Desbloquear Modal -->
  <div class="modal fade" id="confirmDesbloquearModal" tabindex="-1" aria-labelledby="confirmDesbloquearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDesbloquearModalLabel">Confirmar Desbloqueo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que quieres desbloquear a este administrador?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmDesbloquearBtn">Sí, desbloquear</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Eliminar Modal -->
  <div class="modal fade" id="confirmEliminarModal" tabindex="-1" aria-labelledby="confirmEliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmEliminarModalLabel">Confirmar Eliminación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de que quieres eliminar a este administrador?</p>
          <p class="text-info">El estado del administrador se cambiará a "Eliminado" y no será visible en la lista principal.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="confirmEliminarBtn">Sí, eliminar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('editAdminForm');
      const actionInput = document.getElementById('formAction');

      document.getElementById('guardarBtn').addEventListener('click', function () {
          actionInput.value = 'guardar';
          form.submit();
      });

      document.getElementById('confirmBloquearBtn').addEventListener('click', function () {
          actionInput.value = 'bloquear';
          form.submit();
      });

      document.getElementById('confirmDesbloquearBtn').addEventListener('click', function () {
          actionInput.value = 'desbloquear';
          form.submit();
      });

      document.getElementById('confirmEliminarBtn').addEventListener('click', function () {
          actionInput.value = 'eliminar';
          form.submit();
      });
    });
  </script>
</body>
<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-account-settings.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:21 GMT -->
</html>