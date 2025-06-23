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
$totalPages = 1;
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$size = 10;
$offset = $page * $size;

// Determinar el entorno
$is_production = (strpos($_SERVER['HTTP_HOST'], 'elasticbeanstalk.com') !== false);

if ($is_production) {
    // --- CÓDIGO DE PRODUCCIÓN (API) ---
$curl = curl_init();

    $base_url = "https://37kylcuth7.execute-api.us-east-2.amazonaws.com/DEV/user/api/v1";
    $params = [
        'sort' => '-idL',
        'size' => $size,
        'page' => $page,
        'filter[status][ne]' => 'DELETED'
    ];

    if ($_SESSION["usuario"]["perfil"] !== "Superadministrador") {
        $params['filter[idEmpresa]'] = $_SESSION["usuario"]["idEmpresa"];
}

    $url = $base_url . '?' . http_build_query($params);

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
$response = curl_exec($curl);
curl_close($curl);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['data']['content']) && is_array($data['data']['content'])) {
            $empresas = [];
    $conn = getDbConnection();
            if (!$conn->connect_error) {
                $query = "SELECT ID_EMPRESA, NOMBRE_EMPRESA FROM empresas";
    $result = $conn->query($query);
                if ($result) {
    while ($row = $result->fetch_assoc()) {
                        $empresas[$row['ID_EMPRESA']] = $row['NOMBRE_EMPRESA'];
    }
                }
                $conn->close();
            }

            foreach ($data['data']['content'] as $user) {
                // ... (mismo bucle que antes para construir la tabla) ...
                $userId = $user['id'] ?? '';
                $iduser = $user['id_'] ?? ($user['idL'] ?? ''); // Compatibilidad con local y API
                $idAccount = $user['id_account'] ?? ($user['idAccount'] ?? '');
                $userName = ($user['name'] ?? '') . ' ' . ($user['surname'] ?? '');
                $userEmail = $user['email'] ?? '';
                $userdate = $user['birthdate'] ?? '';
                $id_empresa = $user['id_empresa'] ?? ($user['idEmpresa'] ?? null);
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
                $nombreEmpresa = $empresas[$id_empresa] ?? 'Empresa no asignada';
                $codigo_user = $user['codigo_user'] ?? 'N/A';
                
                $tabla .= '<tr>
                            <td class="ps-0"><div class="hstack gap-2"><input class="form-check-input mt-0" type="checkbox"><label class="fs-3 fw-semibold text-dark">'.$userName.'</label></div></td>
                            <td><div class="d-flex justify-content-end">'.$codigo_user.'</div></td>
                            <td><div class="d-flex justify-content-end">'.$userEmail.'</div></td>
                            <td>'.$nombreEmpresa.'</td>
                            <td>'.date('d/m/Y', strtotime($userdate)).'</td>
                            <td>'.$statusBadge.'</td>
                            <td><a href="asignartarjeta.php?id='.$userId.'" class="btn btn-primary d-flex align-items-center gap-1">Asignar</a></td>
                            <td class="pe-0"><a href="edituser.php?iduser='.$iduser.'&id='.$userId.'&acc='.$idAccount.'" class="btn btn-primary d-flex align-items-center gap-1">Ver</a></td>
                        </tr>';
            }
            $totalPages = $data['data']['totalPages'] ?? 1;
        } else {
            $tabla = '<tr><td colspan="8" class="text-center">No se encontraron usuarios</td></tr>';
        }
    } else {
        $tabla = '<tr><td colspan="8" class="text-center">Error al cargar los usuarios</td></tr>';
    }

} else {
    // --- CÓDIGO LOCAL (Base de Datos Directa) ---
    $conn = getDbConnection();
    if ($conn->connect_error) {
        $tabla = '<tr><td colspan="8" class="text-center">Error de conexión a la base de datos.</td></tr>';
    } else {
        $idEmpresa_session = $_SESSION["usuario"]["id_empresa"] ?? 0;
        $perfil_session = $_SESSION["usuario"]["perfil"] ?? '';

        // --- Construcción de la consulta ---
        $where_clauses = ["u.status != 'DELETED'"];
        if (isset($_SESSION["usuario"]["id_empresa"]) && $_SESSION["usuario"]["perfil"] !== 'Superadministrador') {
            $idEmpresa_session = intval($_SESSION["usuario"]["id_empresa"]);
            $where_clauses[] = "u.id_empresa = {$idEmpresa_session}";
    }

        $where_sql = implode(' AND ', $where_clauses);

        // Contar total de registros para paginación
        $count_query = "SELECT COUNT(*) as total FROM `user` u WHERE {$where_sql}";
        $count_result = $conn->query($count_query);
        $total_rows = $count_result->fetch_assoc()['total'];
        $totalPages = ceil($total_rows / $size);

        // Consulta principal con paginación
        $query = "SELECT u.*, e.NOMBRE_EMPRESA 
                  FROM `user` u 
                  LEFT JOIN `empresas` e ON u.id_empresa = e.ID_EMPRESA
                  WHERE {$where_sql}
                  ORDER BY u.id_ DESC 
                  LIMIT {$size} OFFSET {$offset}";
        
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($user = $result->fetch_assoc()) {
                 // ... (mismo bucle que antes para construir la tabla) ...
                 $userId = $user['id'] ?? '';
                 $iduser = $user['id_'] ?? ($user['idL'] ?? '');
                 $idAccount = $user['id_account'] ?? ($user['idAccount'] ?? '');
                 $userName = ($user['name'] ?? '') . ' ' . ($user['surname'] ?? '');
                 $userEmail = $user['email'] ?? '';
                 $userdate = $user['birthdate'] ?? ''; 
                 $id_empresa = $user['id_empresa'] ?? ($user['idEmpresa'] ?? null);
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
                 $nombreEmpresa = $user['NOMBRE_EMPRESA'] ?? 'Empresa no asignada';
                 $codigo_user = $user['codigo_user'] ?? 'N/A'; // Asumiendo que esta columna existe

        $tabla .= '<tr>
                            <td class="ps-0"><div class="hstack gap-2"><input class="form-check-input mt-0" type="checkbox"><label class="fs-3 fw-semibold text-dark">'.$userName.'</label></div></td>
                            <td><div class="d-flex justify-content-end">'.$codigo_user.'</div></td>
                            <td><div class="d-flex justify-content-end">'.$userEmail.'</div></td>
                    <td>'.$nombreEmpresa.'</td>
                    <td>'.date('d/m/Y', strtotime($userdate)).'</td>
                    <td>'.$statusBadge.'</td>
                            <td><a href="asignartarjeta.php?id='.$userId.'" class="btn btn-primary d-flex align-items-center gap-1">Asignar</a></td>
                            <td class="pe-0"><a href="edituser.php?iduser='.$iduser.'&id='.$userId.'&acc='.$idAccount.'" class="btn btn-primary d-flex align-items-center gap-1">Ver</a></td>
                </tr>';
    }
        } else {
            $tabla = '<tr><td colspan="8" class="text-center">No se encontraron usuarios en la base de datos.</td></tr>';
        }
        $conn->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />
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
                <h4 class="mb-8 breadcrumb-title"> <span class="text-primary">Usuarios</span></h4>
              </div>
              <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap flex-lg-nowrap gap-3 align-items-center flex-row justify-content-start justify-content-md-end">
                  <a href="javascript:void(0)" class="btn bg-white border text-dark d-none d-lg-block fw-normal">Introduce los datos  <span class="text-primary fw-semibold ms-1 link-dark">Buscar</span></a>
                  <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="bg-primary" data-bs-title="SERP & KD updated 21 hours ago." class="btn bg-white border text-dark d-lg-none"><span class="text-primary fw-semibold ms-1">Buscar</span></a>
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
                      <li><a class="dropdown-item" href="javascript:void(0)">Activar/desactivar</a></li>
                      <li><a class="dropdown-item" href="javascript:void(0)">Eliminar</a></li>
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
                      <th class="align-top ps-0 w-30"><div class="d-flex align-items-center gap-2"><label for="keyword" class="fs-11 text-dark fw-medium">Nombre</label></div></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Código</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Correo</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Empresa</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Fecha de creacion</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Status</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Asignar tarjeta</h6></th>
                      <th class="align-top w-30 pe-0"><h6 class="fs-11 fw-medium mb-0">Edicion</h6></th>
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
            <div class="modal-header text-white"><h5 class="modal-title" id="empresaCreadaModalLabel">¡Éxito!</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">El usuario se ha creado correctamente.</div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button></div>
          </div>
        </div>
      </div>
    </div>
    <div class="dark-transparent sidebartoggler"></div>
  </div>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/sidebarmenu.js"></script>
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</body>
</html>