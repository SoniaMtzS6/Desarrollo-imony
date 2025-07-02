<?php 
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
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM empresas");
    $totalRow = $totalResult->fetch_assoc();
    $total = $totalRow['total'];
    $totalPages = ceil($total / $size);
    
    // Obtener los registros de la página actual
    $query = "SELECT * FROM empresas ORDER BY ID_EMPRESA DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $size, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($empresa = $result->fetch_assoc()) {
        $status = $empresa['ESTATUS'] === 'ACTIVE' ? 
            '<span class="badge bg-success custom-badge hstack justify-content-center p-0 ms-auto">1</span>' : 
            '<span class="badge bg-danger custom-badge hstack justify-content-center p-0 ms-auto">0</span>';
        
        $tabla .= '<tr>
                <td class="ps-0">
                    <form>
                        <div class="hstack gap-2">
                            <input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">
                            <label for="keyword" class="fs-3 fw-semibold text-dark">'.$empresa['NOMBRE_EMPRESA'].'</label>
                        </div>
                    </form>
                </td>
                <td>
                    <!--Codigo Sonia-->
                    <div class="d-flex justify-content-end"><span>$ </span> '.number_format($empresa['MONTO_MAXIMO'], 2).'</div>
                    <!--fin codigo Sonia-->
                </td>
                <!--Codigo Sonia-->
                <td><span>$</span>'.number_format(0, 2).'</td>
                <td>'.number_format($empresa['NUMERO_TARJETAS']).'</td>
                <td>
                    <p class="mb-0 fw-medium text-dark fs-3 text-end">'.number_format(0).'</p>
                </td>
                <!--fin codigo Sonia-->
                <td>'.$status.'</td>
                <td class="pe-0">
                    <a href="editempresa.php?idempresa='.$empresa['ID_EMPRESA'].'" class="btn btn-primary d-flex align-items-center gap-1">Editar</a>
                </td>
            </tr>';
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Error al obtener empresas: " . $e->getMessage());
    $tabla = '<tr><td colspan="7" class="text-center">Error al cargar las empresas</td></tr>';
}

/* Código de PRODUCCIÓN comentado
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://kywnsmcz46.execute-api.us-east-2.amazonaws.com/Etapa1/fisinter_listarempresas',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_POSTFIELDS =>'{
    "paginationKey": "'.$page.'",
    "paginationSize": "'.$size.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

if ($response) {
    $data = json_decode($response, true);
    $totalPages = $data['pagination']['totalPages'] ?? 1;
    if (isset($data['data'][0])) {
        foreach ($data['data'] as $user) {
            // ... código de producción para mostrar empresas ...
        }
    }
}
*/
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-organic-keywords.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:19 GMT -->
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
    <!-- Sidebar Start -->
   
          <?php include 'header.php'; ?>
      

      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="mb-4">
            <div class="row align-items-center">
              <div class="col-md-6 col-lg-5">
                <h4 class="mb-8 breadcrumb-title"><span class="text-primary">Empresas</span>
                </h4>
               
              </div>
              <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap flex-lg-nowrap gap-3 align-items-center flex-row justify-content-start justify-content-md-end">
                    <!--<a href="" class="btn bg-white border text-dark d-lg-block fw-normal">Introduce los datos  <span class="text-primary fw-semibold ms-1 link-dark">Buscar</span>
                  </a>-->
                    <!--<a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="bg-primary" data-bs-title="SERP & KD updated 21 hours ago." class="btn bg-white border text-dark d-lg-none">
                        <span class="text-primary fw-semibold ms-1">Buscar</span>
                    </a>-->
                    <!--Código Sonia-->
                    <div class="form-group">
                        <label for="buscador">Buscar</label><iconify-icon icon="i-solar:magnifer-bold" class="fs-6"></iconify-icon>
                        <input type="text" id="buscador" placeholder="Introduce los datos" class="btn btn-sm bg-white border text-dark d-lg-block fw-normal" />
                    </div>
                    <!--Fin codigo Sonia-->
                  <a href="nuevaempresa.php" class="btn btn-primary d-flex align-items-center gap-2"><iconify-icon icon="solar:add-circle-line-duotone" class="fs-7"></iconify-icon>Agregar Empresa</a>
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
                <table class="table mb-0 align-middle text-nowrap" id="tablaEmp">
                  <thead class="text-dark fs-4">
                    <tr>
                      <th class="align-top ps-0 w-30">
                        <form>
                          <div class="d-flex align-items-center gap-2">
                            <label for="keyword" class="fs-11 text-dark fw-medium">Empresa</label>
                          </div>
                        </form>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Monto Depositado</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Monto Utilizado</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Tarjetas asignadas</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Tarjetas utilizadas</h6>
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
                   
                  <?php echo $tabla;?>  

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
          <div class="text-center py-3">
            <p class="mb-0">2024 Interestellar derechos</p>
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
            La empresa se ha creado correctamente.
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

  //Codigo Sonia
  document.getElementById('buscador').addEventListener('input', function() {
      var texto = document.getElementById('buscador').value.toLowerCase();
      var filas = document.querySelectorAll('#tablaEmp tbody tr');

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

  //Fin codigo Sonia
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


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-organic-keywords.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:20 GMT -->
</html>