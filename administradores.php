<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'functions.php';
// Validar que el usuario está en sesión.
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}


//Código Sonia
// Obtener el ID del usuario y el session_id actual
$idUsuario = $_SESSION["usuario"]["id"];
$session_actual = session_id();

// Establece el tiempo de inactividad permitido (en segundos)
define('SESSION_TIMEOUT', 3600); // 10 minutos

// Conectar a la base de datos
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT session_token FROM administradores WHERE id = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

// Función para generar un token único
function generarSessionToken() {
    return bin2hex(random_bytes(32));
}

// Al iniciar sesión o si no hay token, se genera uno nuevo
if (!isset($_SESSION['session_token'])) {
    $_SESSION['session_token'] = generarSessionToken();
    $_SESSION['last_activity'] = time(); // Marca la última actividad
}

// Verifica la inactividad
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    // Token expirado por inactividad
    // Limpiar token en base
    $stmt = $conn->prepare("UPDATE administradores SET session_token = NULL WHERE id = ?");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Destruye la sesión
    header("Location: index.php?session=expired");
    exit;
}

// Si no expiró, actualiza el tiempo de última actividad
$_SESSION['last_activity'] = time();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();

    if ($row['session_token'] !== $session_actual) {
        // Token no coincide: sesión expirada o iniciada en otro dispositivo
        // Limpiar token en base
        $stmt = $conn->prepare("UPDATE administradores SET session_token = NULL WHERE id = ?");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        session_unset();
        session_destroy();
        header("Location: index.php?error=token_expirado");
        exit;
    }

} else {
    // Usuario no encontrado, cerrar sesión
    session_unset();
    session_destroy();
    header("Location: index.php?error=usuario_no_encontrado");
    exit;
}

$conn->close();


//fin código Sonia
require_once 'functions.php';

// Inicializar variables
$tabla = '<tr><td colspan="8" class="text-center">No se encontraron administradores.</td></tr>';
$totalPages = 1;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$size = 10;
$idEmpresa = $_SESSION["usuario"]["id_empresa"] ?? 0;
$perfil_session = $_SESSION["usuario"]["perfil"] ?? '';

// Determinar el entorno
$is_production = (strpos($_SERVER['HTTP_HOST'], 'elasticbeanstalk.com') !== false);

if ($is_production) {
    // --- Código de PRODUCCIÓN (API) ---
    /*$curl = curl_init();
    $params = [
        'page' => $page,
        'size' => $size,
        'filter[status][ne]' => 'DELETED' // Excluir eliminados
    ];
    if ($perfil_session !== "Superadministrador") {
        $params['filter[idEmpresa]'] = $idEmpresa;
    }
    
    $url = 'https://9kjot10cte.execute-api.us-east-2.amazonaws.com/dev/admin/list?' . http_build_query($params);

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);*/

} else {
    // --- Código LOCAL (Base de Datos Directa) ---
    $conn = getDbConnection();
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $where_clauses = ["a.status != 'DELETED'"];
    if ($perfil_session !== 'Superadministrador') {
        $where_clauses[] = "a.idEmpresa = " . intval($idEmpresa);
    }
    $where_sql = implode(' AND ', $where_clauses);

    $total_query = "SELECT COUNT(*) as total FROM administradores a WHERE {$where_sql}";
    $total_result = $conn->query($total_query);
    $total_rows = $total_result->fetch_assoc()['total'];
    $totalPages = ceil($total_rows / $size);
    $offset = ($page - 1) * $size;

    $query = "SELECT a.*, e.NOMBRE_EMPRESA as nombreEmpresa FROM administradores a LEFT JOIN empresas e ON a.idEmpresa = e.ID_EMPRESA WHERE {$where_sql} ORDER BY a.id DESC LIMIT {$size} OFFSET {$offset}";
    $result = $conn->query($query);
    $response = json_encode(['data' => $result->fetch_all(MYSQLI_ASSOC), 'totalPages' => $totalPages]);
    $conn->close();
    $err = false;
}

if ($err) {
    error_log("cURL Error #:" . $err);
    $tabla = '<tr><td colspan="8" class="text-center">Error de comunicación con el servicio.</td></tr>';
} elseif ($response) {
    $data = json_decode($response, true);

    if (isset($data['data']) && !empty($data['data'])) {
        $tabla = ""; // Limpiar la tabla antes de llenarla
        $totalPages = $data['totalPages'] ?? 1;

        foreach ($data['data'] as $admin) {
            $status = $admin['status'] ?? 'INACTIVE'; // Default a INACTIVE si no está definido
            $statusBadge = '';
            switch ($status) {
                case 'ACTIVE':
                    $statusBadge = '<span class="badge bg-success custom-badge">Activo</span>';
                    break;
                case 'BLOCKED':
                    $statusBadge = '<span class="badge bg-warning custom-badge">Bloqueado</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge bg-secondary custom-badge">Inactivo</span>';
                    break;
            }

            // Validar la fecha de creación antes de usarla
            $fecha_formateada = 'N/A';
            if (!empty($admin['fecha_creacion'])) {
                $fecha_formateada = date('d/m/Y', strtotime($admin['fecha_creacion']));
            }
        
        $tabla .= '<tr>
                <td class="ps-0">
                        <div class="hstack gap-2">
                            <input class="form-check-input mt-0" type="checkbox">
                            <label class="fs-3 fw-semibold text-dark">'.htmlspecialchars($admin['nombre']).'</label>
                        </div>
                </td>
                    <td><div class="d-flex justify-content-end">'.htmlspecialchars($admin['codigo_admin'] ?? 'N/A').'</div></td>
                    <td><div class="d-flex justify-content-end">'.htmlspecialchars($admin['email']).'</div></td>
                    <td>'.htmlspecialchars($admin['nombreEmpresa'] ?? 'N/A').'</td>
                    <td>'.htmlspecialchars($admin['perfil']).'</td>
                    <td>'.$fecha_formateada.'</td>
                    <td><div class="d-flex justify-content-end">'.$statusBadge.'</div></td>
                <td class="pe-0">
                        <a href="editadministrador.php?id='.htmlspecialchars($admin['id']).'" class="btn btn-primary d-flex align-items-center gap-1">Editar</a>
                </td>
                <!--<td class="pe-0">
                        <a href="asignarempresa.php?id='.htmlspecialchars($admin['id']).'" class="btn btn-primary d-flex align-items-center gap-1">Asignar</a>
                </td>-->
            </tr>';
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

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
                <h4 class="mb-8 breadcrumb-title"><span class="text-primary">Administradores</span>
                </h4>
               
              </div>
              <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap flex-lg-nowrap gap-3 align-items-center flex-row justify-content-start justify-content-md-end">
                  <!--<a href="javascript:void(0)" class="btn bg-white border text-dark d-none d-lg-block fw-normal">Introduce los datos  <span class="text-primary fw-semibold ms-1 link-dark">Buscar</span>
                  </a>
                  <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="bg-primary" data-bs-title="SERP & KD updated 21 hours ago." class="btn bg-white border text-dark d-lg-none">
                    <span class="text-primary fw-semibold ms-1">Buscar</span>
                  </a>-->
                    <!--Código Sonia-->
                    <div class="form-group">
                        <label for="buscadorAdm">Buscar</label><iconify-icon icon="i-solar:magnifer-bold" class="fs-6"></iconify-icon>
                        <input type="text" id="buscadorAdm" placeholder="Introduce los datos" class="btn btn-sm bg-white border text-dark d-lg-block fw-normal" />
                    </div>
                    <!--Fin codigo Sonia-->
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
                <table class="table mb-0 align-middle text-nowrap" id="tableAdm">
                  <thead class="text-dark fs-4">
                    <tr>
                      <th class="align-top ps-0 w-30">
                          <div class="d-flex align-items-center gap-2">
                          <label class="fs-11 text-dark fw-medium">Nombre</label>
                          </div>
                      </th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Código</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Correo</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Empresa</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Rol</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Fecha de creacion</h6></th>
                      <th class="align-top"><h6 class="fs-11 fw-medium mb-0 text-end">Status</h6></th>
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
                      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
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
                <div class="modal-header text-white"><h5 class="modal-title" id="empresaCreadaModalLabel">¡Éxito!</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body">El administrador se ha creado correctamente.</div>
                <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button></div>
          </div>
          </div>
          </div>
          <div class="text-center py-3"><p class="mb-0">2024 Interestellar derechos</p></div>
        </div>
      </div>
    </div>
    <div class="dark-transparent sidebartoggler"></div>

<!--Código Sonia-->
    <script>
        document.getElementById('buscadorAdm').addEventListener('input', function() {
            var texto = document.getElementById('buscadorAdm').value.toLowerCase();
            var filas = document.querySelectorAll('#tableAdm tbody tr');

            filas.forEach(fila => {
                var contenidoFila = fila.textContent.toLowerCase();
                if (texto === '') {
                    // Si el input está vacío, mostrar todo
                    fila.style.display = '';
                } else if (contenidoFila.includes(texto)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    </script>
<!--Fin codigo Sonia-->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/sidebarmenu.js"></script>
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</body>
</html>