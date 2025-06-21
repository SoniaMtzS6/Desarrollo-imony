<?php


$curl = curl_init();

curl_setopt_array($curl, array(
  // URL de PRODUCCIÓN
  CURLOPT_URL => 'https://vdn0w81bc0.execute-api.us-east-2.amazonaws.com/dev/card/api/v1/?page[number]=1&page[size]=10',
  
  // URL LOCAL comentada
  // CURLOPT_URL => 'http://localhost:3000/api/card/v1/?page[number]=1&page[size]=10',
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
// echo $response;

  if ($response) {
    // Decodificar la respuesta JSON
    $data = json_decode($response, true); // `true` para convertir JSON a un array asociativo
   
    // Acceder a los elementos de la respuesta
    // Ejemplo: Asignar el primer usuario a variables (ajusta esto según la estructura JSON)
    echo "Antes del if"; 
    if (isset($data['data'][0])) {  // Suponiendo que la respuesta tiene una clave `data`
        $user = $data['data'][0];
        $tabla="";
        foreach ($data['data'] as $user) {
          
          $userId = $user['id'];
          $status = $user['status'];
          $start_date = $user['start_date'];
          $last_four = $user['last_four'];
          $provider = $user['provider'];
          $affinity_group_name = $user['affinity_group_name'];

          $tabla.='<tr>'.
                  '<td class="ps-0">'.
                      '<form>'.
                      '<div class="hstack gap-2">'.
                          '<input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">'.           
                          '<label for="keyword" class="fs-3 fw-semibold text-dark">**** **** **** '.$last_four.'</label>'.
                      '</div>'.
                      '</form>'.
                  '</td>'.
                  '<td>'.
                      '<div class="d-flex justify-content-end">'.
                      $affinity_group_name.
                      '</div>'.
                  '</td>'.
                  '<td> Sin asignar'.
                                       
                  '</td>'.
                  '<td>'.
                  $provider. 
                  '</td>'.
                  '<td>'.
                  $start_date.
                  '</td>'.
                                      
                  '<td>'.
                  $status.
                  '</td>'.
                  '<td>'.
                      '<a href="javascript:void(0)" class="btn btn-primary d-flex align-items-center gap-1">Cargar saldo   </a>'.
                  '</td>'.
                  '<td class="pe-0">'.
                      '<a href="javascript:void(0)" class="btn btn-primary d-flex align-items-center gap-1">Asociar usuario </a>'.
                  '</td>'.
                  '</tr>';
        }
        // echo "ID del usuario: " . $userId . "\n";
        // echo "Nombre del usuario: " . $userName . "\n";
        // echo "Correo electrónico del usuario: " . $userEmail . "\n";
    } else {
        echo "No se encontraron datos de usuario.";
    }
} else {
    echo "Error en la solicitud CURL.";
}

// Código local
require_once 'functions.php';
$conn = getDbConnection();
$tabla = "";

$sql = "SELECT t.*, u.NOMBRE as NOMBRE_USUARIO 
        FROM tarjetas t 
        LEFT JOIN usuarios u ON t.ID_USUARIO = u.ID_USUARIO 
        ORDER BY t.FECHA_ASIGNACION DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tabla .= '<tr>'.
                '<td class="ps-0">'.
                    '<form>'.
                    '<div class="hstack gap-2">'.
                        '<input class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input" name="keyword" id="templates">'.           
                        '<label for="keyword" class="fs-3 fw-semibold text-dark">**** **** **** '.substr($row['NUMERO_TARJETA'], -4).'</label>'.
                    '</div>'.
                    '</form>'.
                '</td>'.
                '<td>'.
                    '<div class="d-flex justify-content-end">'.
                    htmlspecialchars($row['NOMBRE_USUARIO'] ?? 'Sin asignar').
                    '</div>'.
                '</td>'.
                '<td>'.
                    htmlspecialchars($row['ESTATUS']).
                '</td>'.
                '<td>'.
                    'Local'. 
                '</td>'.
                '<td>'.
                    $row['FECHA_ASIGNACION'].
                '</td>'.
                '<td>'.
                    '<a href="javascript:void(0)" class="btn btn-primary d-flex align-items-center gap-1">Cargar saldo</a>'.
                '</td>'.
                '<td class="pe-0">'.
                    '<a href="asignartarjeta.php?id='.$row['ID_TARJETA'].'" class="btn btn-primary d-flex align-items-center gap-1">Editar</a>'.
                '</td>'.
                '</tr>';
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="Ligth" data-color-theme="Blue_Theme" data-layout="vertical">


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
                <h4 class="mb-8 breadcrumb-title"> <span class="text-primary">Tarjetas</span>
                </h4>
               
              </div>
              <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap flex-lg-nowrap gap-3 align-items-center flex-row justify-content-start justify-content-md-end">
                  <a href="javascript:void(0)" class="btn bg-white border text-dark d-none d-lg-block fw-normal">Introduce los datos  <span class="text-primary fw-semibold ms-1 link-dark">Buscar</span>
                  </a>
                  <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="bg-primary" data-bs-title="SERP & KD updated 21 hours ago." class="btn bg-white border text-dark d-lg-none">
                    <span class="text-primary fw-semibold ms-1">Buscar</span>
                  </a>
                  <a href="nuevatarjeta.php" class="btn btn-primary d-flex align-items-center gap-2"><iconify-icon icon="solar:add-circle-line-duotone" class="fs-7"></iconify-icon>Agregar Tarjetas</a>
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
                            <label for="keyword" class="fs-11 text-dark fw-medium">Tarjeta</label>
                          </div>
                        </form>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Empresa</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Usuario</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Tipo</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Fecha de creacion</h6>
                      </th>
                     
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Status</h6>
                      </th>
                      <th class="align-top">
                        <h6 class="fs-11 fw-medium mb-0 text-end">Cargar Saldo</h6>
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
              </div>
            </div>
          </div>
          <div class="text-center py-3">
            <p class="mb-0">2024 Finister derechos</p>
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