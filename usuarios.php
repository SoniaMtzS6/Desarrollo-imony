<?php 
require_once 'functions.php';

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

// Inicializar variables
$tabla = "";
$totalPages = 0;

// Configuración de paginación
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$size = 10;

// Código de PRODUCCIÓN
// Inicializar CURL
$curl = curl_init();

// Determinar la URL según el perfil del usuario
if($_SESSION["usuario"]["perfil"] === "Superadministrador") {
    $url = "https://37kylcuth7.execute-api.us-east-2.amazonaws.com/DEV/user/api/v1?sort=-idL&size=$size&page=$page";
} else {
    $url = "https://37kylcuth7.execute-api.us-east-2.amazonaws.com/DEV/user/api/v1?sort=-idL&size=$size&page=$page&filter[idEmpresa]=".$_SESSION["usuario"]["idEmpresa"];
}

// Configuración de CURL
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET'
));

// Ejecutar CURL
$response = curl_exec($curl);
curl_close($curl);

// Procesar la respuesta de la API
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $user) {
            $userId = $user['id'] ?? '';
            $userName = ($user['name'] ?? '') . ' ' . ($user['surname'] ?? '');
            $userEmail = $user['email'] ?? '';
            $userdate = $user['create_time'] ?? '';
            $id_empresa = $user['id_empresa'] ?? null;
            
            $status = $user['status'] ?? '';
            $statusBadge = '';
            switch ($status) {
                case 'ACTIVE':
                    $statusBadge = '<span class="badge bg-success custom-badge hstack justify-content-center p-0 ms-auto">Activo</span>';
                    break;
                case 'BLOCKED':
                    $statusBadge = '<span class="badge bg-warning custom-badge hstack justify-content-center p-0 ms-auto">Bloqueado</span>';
                    break;
                case 'DELETED':
                    $statusBadge = '<span class="badge bg-danger custom-badge hstack justify-content-center p-0 ms-auto">Eliminado</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge bg-secondary custom-badge hstack justify-content-center p-0 ms-auto">Inactivo</span>';
                    break;
            }

            $nombreEmpresa = $user['empresa_nombre'] ?? 'Empresa no asignada';

            $tabla .= '<tr>
                        <td class="ps-0">
                            <form>
                                <div class="hstack gap-2">
                                    <input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">
                                    <label for="keyword" class="fs-3 fw-semibold text-dark">'.$userName.'</label>
                                </div>
                            </form>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">'.$user['codigo_user'].'</div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">'.$userEmail.'</div>
                        </td>
                        <td>'.$nombreEmpresa.'</td>
                        <td>'.date('d/m/Y', strtotime($userdate)).'</td>
                        <td>'.$statusBadge.'</td>
                        <td>
                            <a href="asignartarjeta.php?id='.$userId.'" class="btn btn-primary d-flex align-items-center gap-1">Asignar</a>
                        </td>
                        <td class="pe-0">
                            <a href="edituser.php?id='.$userId.'" class="btn btn-primary d-flex align-items-center gap-1">Ver</a>
                        </td>
                    </tr>';
        }
        
        // Obtener información de paginación
        $totalPages = $data['meta']['totalPages'] ?? 1;
    } else {
        $tabla = '<tr><td colspan="8" class="text-center">No se encontraron usuarios</td></tr>';
    }
} else {
    $tabla = '<tr><td colspan="8" class="text-center">Error al cargar los usuarios</td></tr>';
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-organic-keywords.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:19 GMT -->
<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>Finister Admin</title>
</head>

<body class="link-sidebar">

<?php if (isset($_GET['creada']) && $_GET['creada'] == 1): ?>
  <script>
    window.addEventListener('DOMContentLoaded', function () {
      var modal = new bootstrap.Modal(document.getElementById('empresaCreadaModal'));
      modal.show();
    });
  </script>
<?php endif; ?>
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
                <h4 class="mb-8 breadcrumb-title"> <span class="text-primary">Usuarios</span>
                </h4>
               
              </div>
              <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap flex-lg-nowrap gap-3 align-items-center flex-row justify-content-start justify-content-md-end">
                  <a href="javascript:void(0)" class="btn bg-white border text-dark d-none d-lg-block fw-normal">Introduce los datos  <span class="text-primary fw-semibold ms-1 link-dark">Buscar</span>
                  </a>
                  <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="bg-primary" data-bs-title="SERP & KD updated 21 hours ago." class="btn bg-white border text-dark d-lg-none">
                    <span class="text-primary fw-semibold ms-1">Buscar</span>
                  </a>
                  <a href="nuevouser.php" class="btn btn-primary d-flex align-items-center gap-2"><iconify-icon icon="solar:add-circle-line-duotone" class="fs-7"></iconify-icon>Agregar Usuarios</a>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-7">
            <div class="card-body">
              
              <div class="py-4">
                <div class="d-flex gap-6 fw-bold align-items-center flex-lg-nowrap flex-wrap">
                  <p class="fs-3 fw-bold text-dark mb-0">Acciones</p>
                  <div class="dropdown">
                    <button class="btn bg-light dropdown-toggle text-body-color d-flex align-items-center gap-2 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <iconify-icon icon="solar:calendar-linear" class="fs-6"></iconify-icon>
                     Seleccione
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)">Activar/desactivar</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)">Eliminar</a>
                      </li>
                    </ul>
                  </div>
                  
                  <button class="btn bg-light d-flex align-items-center gap-2">
                    <span>Aplicar</span>
                    <iconify-icon icon="solar:filter-line-duotone" class="fs-6"></iconify-icon>
                  </button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table mb-0 align-middle text-nowrap">
                  <thead class="text-dark fs-4">
                    <tr>
                      <th class="align-top ps-0 w-30">
                        <form>
                          <div class="d-flex align-items-center gap-2">
                            <label for="keyword" class="fs-11 text-dark fw-medium">Nombre</label>
                          </div>
                        </form>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Código</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Correo</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Empresa</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Fecha de creacion</h6>
                      </th>
                     
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Status</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Asignar tarjeta</h6>
                      </th>
                      <th class="align-top w-30 pe-0">
                        <h6 class="fs-11 fw-medium mb-0">Edicion</h6>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="table-group-divider border-primary">
                    <!-- <tr>
                      <td class="ps-0">
                        <form>
                          <div class="hstack gap-2">
                            <input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">
                            
                            <label for="keyword" class="fs-3 fw-semibold text-dark">Enrique Velasco</label>
                          </div>
                        </form>
                      </td>
                      <td>
                        <div class="d-flex justify-content-end">
                          enrique@fisinter.com
                        </div>
                      </td>
                      <td>
                       1 
                      </td>
                      <td>
                        01/01/2024
                      </td>
                     
                      <td>
                        <span class="badge bg-success custom-badge hstack justify-content-center p-0 ms-auto">1</span>
                      </td>
                      <td>
                        <a href="javascript:void(0)" class="btn btn-primary d-flex align-items-center gap-1">Asignar</a>
                      </td>
                      <td class="pe-0">
                        <a href="javascript:void(0)" class="btn btn-primary d-flex align-items-center gap-1">Editar</a>
                      </td>
                    </tr> -->
                    <?php echo $tabla; ?>
                  </tbody>
                </table>

                <div class="d-flex justify-content-center mt-3">
                  <nav>
                    <ul class="pagination">
                      <?php for ($i = 0; $i < $totalPages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                        </li>
                      <?php endfor; ?>
                    </ul>
                  </nav>
                </div>


              </div>
            </div>
          </div>
          <div class="text-center py-3">
            <p class="mb-0">2024 Finister derechos</p>
          </div>
        </div>
      </div>

      <div class="modal fade" id="empresaCreadaModal" tabindex="-1" aria-labelledby="empresaCreadaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header text-white">
            <h5 class="modal-title" id="empresaCreadaModalLabel">¡Éxito!</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            El usuario se ha creado correctamente.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
      <script>
  function handleColorTheme(e) {
    document.documentElement.setAttribute("data-color-theme", e);
  }
</script>
    
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
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-organic-keywords.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:20 GMT -->
</html>
</html>