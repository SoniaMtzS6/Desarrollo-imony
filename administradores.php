<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'functions.php';

// Inicializar variables
$tabla = "";
$totalPages = 0;

// Configuración de paginación
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$size = 10;
$offset = ($page - 1) * $size;

try {
    $conn = getDbConnection();
    
    // Obtener el total de registros
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM administradores WHERE eliminado = 0");
    $totalRow = $totalResult->fetch_assoc();
    $total = $totalRow['total'];
    $totalPages = ceil($total / $size);
    
    // Obtener los registros de la página actual con el nombre de la empresa
    $query = "SELECT a.*, e.NOMBRE_EMPRESA 
              FROM administradores a 
              LEFT JOIN empresas e ON a.idEmpresa = e.ID_EMPRESA
              WHERE a.eliminado = 0 
              ORDER BY a.id DESC 
              LIMIT ? OFFSET ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $size, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($admin = $result->fetch_assoc()) {
        // Formatear el estado del administrador
        $estado = $admin['activo'] ? 
            '<span class="badge bg-success custom-badge hstack justify-content-center p-0 ms-auto">Activo</span>' : 
            '<span class="badge bg-danger custom-badge hstack justify-content-center p-0 ms-auto">Inactivo</span>';
        
        $tabla .= '<tr>
                <td class="ps-0">
                    <form>
                        <div class="hstack gap-2">
                            <input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">
                            <label for="keyword" class="fs-3 fw-semibold text-dark">'.$admin['nombre'].'</label>
                        </div>
                    </form>
                </td>
                <td>
                    <div class="d-flex justify-content-end">'.$admin['codigo_admin'].'</div>
                </td>
                <td>
                    <div class="d-flex justify-content-end">'.$admin['email'].'</div>
                </td>
                <td>'.$admin['NOMBRE_EMPRESA'].'</td>
                <td>'.$admin['perfil'].'</td>
                <td>
                    <p class="mb-0 fw-medium text-dark fs-3 text-end">'.date('d/m/Y', strtotime($admin['fecha_creacion'])).'</p>
                </td>
                <td>'.$estado.'</td>
                <td class="pe-0">
                    <a href="editadministrador.php?id='.$admin['id'].'" class="btn btn-primary d-flex align-items-center gap-1">Editar</a>
                </td>
            </tr>';
    }
} catch (Exception $e) {
    error_log("Error al obtener administradores: " . $e->getMessage());
    $tabla = '<tr><td colspan="7" class="text-center">Error al cargar los administradores</td></tr>';
}

// Código de PRODUCCIÓN
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://9kjot10cte.execute-api.us-east-2.amazonaws.com/dev/admin/list',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_POSTFIELDS =>'{
    "page": '.$page.',
    "size": '.$size.',
    "filter": "idEmpresa"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

// Procesar la respuesta de la API
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $admin) {
            // Formatear el estado del administrador
            $estado = $admin['activo'] ? 
                '<span class="badge bg-success custom-badge hstack justify-content-center p-0 ms-auto">Activo</span>' : 
                '<span class="badge bg-danger custom-badge hstack justify-content-center p-0 ms-auto">Inactivo</span>';
            
            $tabla .= '<tr>
                    <td class="ps-0">
                        <form>
                            <div class="hstack gap-2">
                                <input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">
                                <label for="keyword" class="fs-3 fw-semibold text-dark">'.$admin['nombre'].'</label>
                            </div>
                        </form>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end">'.$admin['codigo_admin'].'</div>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end">'.$admin['email'].'</div>
                    </td>
                    <td>'.$admin['empresa_nombre'].'</td>
                    <td>'.$admin['perfil'].'</td>
                    <td>
                        <p class="mb-0 fw-medium text-dark fs-3 text-end">'.date('d/m/Y', strtotime($admin['fecha_creacion'])).'</p>
                    </td>
                    <td>'.$estado.'</td>
                    <td class="pe-0">
                        <a href="editadministrador.php?id='.$admin['id'].'" class="btn btn-primary d-flex align-items-center gap-1">Editar</a>
                    </td>
                </tr>';
        }
        
        // Obtener información de paginación
        $totalPages = $data['totalPages'] ?? 1;
    } else {
        $tabla = '<tr><td colspan="8" class="text-center">No se encontraron administradores</td></tr>';
    }
} else {
    $tabla = '<tr><td colspan="8" class="text-center">Error al cargar los administradores</td></tr>';
}

/* Código local comentado
try {
    $conn = getDbConnection();
    
    // Obtener el total de registros
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM administradores WHERE eliminado = 0");
    $totalRow = $totalResult->fetch_assoc();
    $total = $totalRow['total'];
    $totalPages = ceil($total / $size);
    
    // Obtener los registros de la página actual con el nombre de la empresa
    $query = "SELECT a.*, e.NOMBRE_EMPRESA 
              FROM administradores a 
              LEFT JOIN empresas e ON a.idEmpresa = e.ID_EMPRESA
              WHERE a.eliminado = 0 
              ORDER BY a.id DESC 
              LIMIT ? OFFSET ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $size, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($admin = $result->fetch_assoc()) {
        // Formatear el estado del administrador
        $estado = $admin['activo'] ? 
            '<span class="badge bg-success custom-badge hstack justify-content-center p-0 ms-auto">Activo</span>' : 
            '<span class="badge bg-danger custom-badge hstack justify-content-center p-0 ms-auto">Inactivo</span>';
        
        $tabla .= '<tr>
                <td class="ps-0">
                    <form>
                        <div class="hstack gap-2">
                            <input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">
                            <label for="keyword" class="fs-3 fw-semibold text-dark">'.$admin['nombre'].'</label>
                        </div>
                    </form>
                </td>
                <td>
                    <div class="d-flex justify-content-end">'.$admin['codigo_admin'].'</div>
                </td>
                <td>
                    <div class="d-flex justify-content-end">'.$admin['email'].'</div>
                </td>
                <td>'.$admin['NOMBRE_EMPRESA'].'</td>
                <td>'.$admin['perfil'].'</td>
                <td>
                    <p class="mb-0 fw-medium text-dark fs-3 text-end">'.date('d/m/Y', strtotime($admin['fecha_creacion'])).'</p>
                </td>
                <td>'.$estado.'</td>
                <td class="pe-0">
                    <a href="editadministrador.php?id='.$admin['id'].'" class="btn btn-primary d-flex align-items-center gap-1">Editar</a>
                </td>
            </tr>';
    }
} catch (Exception $e) {
    error_log("Error al obtener administradores: " . $e->getMessage());
    $tabla = '<tr><td colspan="7" class="text-center">Error al cargar los administradores</td></tr>';
}
*/
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
  <title>Interestellar Admin</title>
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
    <!-- Sidebar Start -->
    <?php include 'header.php'; ?>

      

      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="mb-4">
            <div class="row align-items-center">
              <div class="col-md-6 col-lg-5">
                <h4 class="mb-8 breadcrumb-title"><span class="text-primary">Administradores</span>
                </h4>
               
              </div>
              <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap flex-lg-nowrap gap-3 align-items-center flex-row justify-content-start justify-content-md-end">
                  <a href="javascript:void(0)" class="btn bg-white border text-dark d-none d-lg-block fw-normal">Introduce los datos  <span class="text-primary fw-semibold ms-1 link-dark">Buscar</span>
                  </a>
                  <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="bg-primary" data-bs-title="SERP & KD updated 21 hours ago." class="btn bg-white border text-dark d-lg-none">
                    <span class="text-primary fw-semibold ms-1">Buscar</span>
                  </a>
                  <a href="nuevoadministrador.php" class="btn btn-primary d-flex align-items-center gap-2"><iconify-icon icon="solar:add-circle-line-duotone" class="fs-7"></iconify-icon>Agregar Administrador</a>
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
                        <h6 class="fs-11 fw-medium mb-0 text-end">Rol</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Fecha de creacion</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Status</h6>
                      </th>
                      <th class="align-top w-30 pe-0">
                        <h6 class="fs-11 fw-medium mb-0">Edicion</h6>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="table-group-divider border-primary">

                  <?php echo $tabla; ?>

                  </tbody>
                </table>

                <div class="d-flex justify-content-center mt-3">
                  <nav>
                    <ul class="pagination">
                      <?php for ($i = 0; $i < $totalPages; $i++): ?>
                        <li class="page-item <?php if (($i+1) == $page) echo 'active'; ?>">
                          <a class="page-link" href="?page=<?php echo $i + 1; ?>"><?php echo $i + 1; ?></a>
                        </li>
                      <?php endfor; ?>
                    </ul>
                  </nav>
                </div>

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
            El administrador se ha creado correctamente.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
          <div class="text-center py-3">
            <p class="mb-0">2024 Interestellar derechos</p>
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