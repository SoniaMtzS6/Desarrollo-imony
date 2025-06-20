<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'functions.php';
session_start();

$status_update = $_GET['status_update'] ?? '';

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    echo '<div style="color:red;">Redirigiendo a index.php por falta de sesión.</div>';
    header("Location: index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    echo '<div style="color:red;">Redirigiendo a authentication-two-steps.php por doble factor.</div>';
    header("Location: authentication-two-steps.php");
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$acc = isset($_GET['acc']) ? $_GET['acc'] : null;

$user = null; // Inicializar la variable $user

// Si no viene acc, lo buscamos en la base de datos
if ($id) {
    $conn = getDbConnection();
    
    // Obtener datos del usuario, incluyendo el estado 'status'
    $stmt_user = $conn->prepare("SELECT *, status FROM user WHERE id_ = ?");
    $stmt_user->bind_param("i", $id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
        if (!$acc && isset($user['id_account'])) {
            $acc = $user['id_account'];
        }
    }
    $stmt_user->close();

    if (!$acc) {
        // Intentar buscar 'id_account' si no se encontró antes
        $acc_found = false;
        try {
            $stmt = $conn->prepare("SELECT id_account FROM user WHERE id_ = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $acc = $row['id_account'];
                $acc_found = true;
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            // Manejar excepción si la tabla o columna no existen
        }
        if (!$acc_found) {
            echo "<div style='color:red;'>No se encontró el campo 'id_account' para el usuario con id $id.</div>";
        }
    }
    $conn->close();
}

$carid = "";
$hidden = "";
$token = "";
$provider = "";
$pan = "";
$expiration = "";
$status = "";
$balance = "";
$currency = "";

if (!$id || !$acc) {
    echo "<div style='color:red;'>Faltan parámetros requeridos en la URL (id y/o acc).</div>";
    exit;
}

// --- LLAMADA A API DE TARJETAS ---
// === INICIO BLOQUE DE PRODUCCIÓN ORIGINAL ===
// Código de producción:
// $curl = curl_init();
// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://vdn0w81bc0.execute-api.us-east-2.amazonaws.com/dev/card/api/v1/?filter[user_id]='.$id,
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'GET',
//   CURLOPT_HTTPHEADER => array(),
// ));
// $response = curl_exec($curl);
// curl_close($curl);
// $tarjetascliente = "";
// if ($response) {
//     $data = json_decode($response, true);
//     // ... procesamiento original ...
// }
// === FIN BLOQUE DE PRODUCCIÓN ORIGINAL ===

// === INICIO BLOQUE NUEVO PARA LOCAL Y PRODUCCIÓN (NO ELIMINAR EL ANTERIOR) ===
$estadoCuenta = '
<div class="card border shadow-none">
  <div class="card-body p-4">
    <h4 class="card-title">Estado de la cuenta : <span class="text-success">ACTIVE</span></h4>
    <p class="card-subtitle">Puedes consultar el estado de la tarjeta.</p>
    <div class="d-flex align-items-center justify-content-between mt-7 mb-3">
      <div class="d-flex align-items-center gap-3">
        <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
          <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
        </div>
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span>Monto asignado</span>
            <!-- Switch de bloqueo -->
            <div class="form-check form-switch ms-2">
              <input class="form-check-input" type="checkbox" id="switch-bloqueo-tarjeta" checked>
              <label class="form-check-label" for="switch-bloqueo-tarjeta"></label>
            </div>
          </div>
          <h5 class="fs-4 fw-semibold">$ 0</h5>
        </div>
      </div>
      <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add"></a>
    </div>
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-primary">Activar</button>
      <button class="btn bg-danger-subtle text-danger">Desactivar</button>
    </div>
  </div>
</div>
';
$cargaSaldo = '
<div class="card border shadow-none">
  <div class="card-body p-4">
    <h4 class="card-title">Carga de saldo</h4>
    <p class="card-subtitle">Introduce el saldo a la tarjeta o retíralo.</p>
    <form>
      <div class="d-flex align-items-center gap-3 mb-3 mt-7">
        <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
          <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
        </div>
        <div>
          <label class="mb-0">Monto</label>
          <input type="text" class="form-control" placeholder="$">
        </div>
      </div>
      <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-primary">Asignar monto</button>
        <button type="button" class="btn bg-danger-subtle text-danger">Retirar monto</button>
      </div>
    </form>
  </div>
</div>
';
$tarjetascliente = $estadoCuenta . $cargaSaldo . '
<div class="card border shadow-none">
  <div class="card-body p-4">
    <h4 class="card-title">Tarjetas asignadas</h4>
    <p class="card-subtitle">2024-11-06</p>
    <br>
    <div class="d-flex align-items-center justify-content-between mt-7">
      <div class="d-flex align-items-center gap-3">
        <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
          <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
        </div>
        <div>
          <h5 class="fs-4 fw-semibold">MASTERCARD</h5>
          <p class="mb-0 text-dark">5366 6929 0638 8450 </p>
          <p class="mb-0 text-dark">12/30</p>
        </div>
      </div>
      <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit"></a>
    </div>
    <div class="d-flex align-items-center gap-3">
      <form method="POST" action="servicios/configuraciontarjeta.php"> 
        <input type="hidden" class="form-control" name="card" value="crd-2oUkkltfZQiziu9CgNnluxGPuFi">
        <input type="hidden" class="form-control" name="usr" value="usr-2wmpryJkInSQhpqv5K0dtZXYq25">
        <input type="hidden" class="form-control" name="acc" value="acc-2wmpry7iA57dIaglkXNJOeoNQ3g">
        <button type="submit" class="btn bg-danger-subtle text-danger" name="action" value="asociar">Asociar cuenta</button>
      </form>
    </div>
    <hr>
    <div class="d-flex align-items-center justify-content-between mt-7">
      <div class="d-flex align-items-center gap-3">
        <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
          <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
        </div>
        <div>
          <h5 class="fs-4 fw-semibold">VISA</h5>
          <p class="mb-0 text-dark">4111 1111 1111 1111 </p>
          <p class="mb-0 text-dark">11/29</p>
        </div>
      </div>
      <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit"></a>
    </div>
    <div class="d-flex align-items-center gap-3">
      <form method="POST" action="servicios/configuraciontarjeta.php"> 
        <input type="hidden" class="form-control" name="card" value="crd-2oUkkltfZQiziu9CgNnluxGPuFj">
        <input type="hidden" class="form-control" name="usr" value="usr-2wmpryJkInSQhpqv5K0dtZXYq26">
        <input type="hidden" class="form-control" name="acc" value="acc-2wmpry7iA57dIaglkXNJOeoNQ3h">
        <button type="submit" class="btn bg-danger-subtle text-danger" name="action" value="asociar">Asociar cuenta</button>
      </form>
    </div>
  </div>
</div>
';
// === FIN BLOQUE NUEVO ===

// --- LLAMADA A API DE ACTIVIDADES ---
// Código de producción:
// $curl = curl_init();
// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://h3epgx14k6.execute-api.us-east-2.amazonaws.com/dev/activities/findByAcc',
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'POST',
//   CURLOPT_POSTFIELDS =>'{"acc":"'.$acc.'"}',
//   CURLOPT_HTTPHEADER => array('Content-type: application/json; charset=UTF-8'),
// ));
// $response = curl_exec($curl);
// curl_close($curl);
// --- FIN PRODUCCIÓN ---
// --- LOCAL ---
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:3000/api/activities/findByAcc',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(["acc" => $acc]),
    CURLOPT_HTTPHEADER => array('Content-type: application/json; charset=UTF-8'),
));
$response = curl_exec($curl);
curl_close($curl);
$compra = "";
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['Data']) && count($data['Data']) > 0) {
        foreach ($data['Data'] as $entry) {
            $totalAmount = $entry['totalAmount'];
            $createdAt = $entry['createdAt'];
            $origin = $entry['origin'];
            $processType = $entry['processType'];
            $merchantName = $entry['merchantName'];
            $compra .= '<div class="d-flex align-items-center justify-content-between mb-4"><div class="d-flex align-items-center gap-3"><div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center"><i class="ti text-dark d-block fs-7" width="22" height="22"></i></div><div><h5 class="fs-4 fw-semibold">'.$merchantName.' '.$processType.' '.$origin.'</h5><p class="mb-0">Realizada: '.$createdAt.'</p></div></div><div class="form-check form-switch mb-0"><p class="mb-0">'.$totalAmount.'</p></div></div>';
        }
    } else {
        // MOCK LOCAL REALISTA
        $notificaciones_mock = [
            [
                'merchantName' => 'OXXO',
                'processType' => 'Compra',
                'origin' => 'Tienda',
                'createdAt' => '2024-06-17',
                'totalAmount' => 50
            ],
            [
                'merchantName' => 'Walmart',
                'processType' => 'Compra',
                'origin' => 'Supermercado',
                'createdAt' => '2024-06-15',
                'totalAmount' => 1200
            ],
            [
                'merchantName' => 'Starbucks',
                'processType' => 'Compra',
                'origin' => 'Café',
                'createdAt' => '2024-06-10',
                'totalAmount' => 85
            ]
        ];
        foreach ($notificaciones_mock as $entry) {
            $compra .= '<div class="d-flex align-items-center justify-content-between mb-4"><div class="d-flex align-items-center gap-3"><div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center"><i class="ti text-dark d-block fs-7" width="22" height="22"></i></div><div><h5 class="fs-4 fw-semibold">'.$entry['merchantName'].' '.$entry['processType'].' '.$entry['origin'].'</h5><p class="mb-0">Realizada: '.$entry['createdAt'].'</p></div></div><div class="form-check form-switch mb-0"><p class="mb-0">'.$entry['totalAmount'].'</p></div></div>';
        }
    }
}

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener ID de usuario
$iduser = $_GET['id'] ?? null;
$user = null;

if ($iduser) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM user WHERE id_ = ?");
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
}

// Generar lista de empresas
$empresa = '';
$resEmp = $conn->query("SELECT ID_EMPRESA, NOMBRE_EMPRESA FROM empresas");

if($_SESSION["usuario"]["perfil"]==="Superadministrador"){
  while ($row = $resEmp->fetch_assoc()) {
    $selected = ($user && $user['id_empresa'] == $row['ID_EMPRESA']) ? 'selected' : '';
    $empresa .= '<option value="' . $row['ID_EMPRESA'] . '" ' . $selected . '>' . $row['NOMBRE_EMPRESA'] . '</option>';
  }
}else{

  while ($row = $resEmp->fetch_assoc()) {
      if($_SESSION["usuario"]["idEmpresa"]==$row['ID_EMPRESA']){
        $selected = ($user && $user['id_empresa'] == $row['ID_EMPRESA']) ? 'selected' : '';
        $empresa .= '<option value="' . $row['ID_EMPRESA'] . '" ' . $selected . '>' . $row['NOMBRE_EMPRESA'] . '</option>';
      }
    }
}
 

$conn->close();

$isLocal = true; // Forzado para pruebas
// Debug entorno
// echo '<pre>$isLocal: ' . ($isLocal ? 'true' : 'false') . '</pre>';
// echo '<pre>HTTP_HOST: ' . htmlspecialchars($_SERVER['HTTP_HOST']) . '</pre>';

if ($isLocal) {
    $conn = getDbConnection();
    $userId = $id; // o la variable que corresponda
    $sql = "SELECT * FROM tarjetas_local WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // echo '<pre>Buscando tarjetas para user_id: ' . htmlspecialchars($userId) . '</pre>';
    // echo '<pre>Filas encontradas: ' . $result->num_rows . '</pre>';

    $tarjetasHTML = '';
    while ($row = $result->fetch_assoc()) {
        $tarjetasHTML .= '
        <div class="d-flex align-items-center justify-content-between mt-7">
          <div class="d-flex align-items-center gap-3">
            <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
              <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
            </div>
            <div>
              <h5 class="fs-4 fw-semibold">'.htmlspecialchars($row['provider']).'</h5>
              <p class="mb-0 text-dark">**** **** **** '.htmlspecialchars($row['last_four']).'</p>
              <p class="mb-0 text-dark">'.htmlspecialchars($row['start_date']).'</p>
            </div>
          </div>
          <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit"></a>
        </div>
        <div class="d-flex align-items-center gap-3">
          <form method="POST" action="servicios/configuraciontarjeta.php"> 
            <input type="hidden" class="form-control" name="card" value="'.htmlspecialchars($row['card_id']).'">
            <input type="hidden" class="form-control" name="usr" value="'.htmlspecialchars($userId).'">
            <button type="submit" class="btn bg-danger-subtle text-danger" name="action" value="asociar">Asociar cuenta</button>
          </form>
        </div>
        <hr>
        ';
    }
    $stmt->close();
    $conn->close();

    if ($tarjetasHTML === '') {
        // Si no hay tarjetas, mostrar el mock visual aprobado
        $tarjetasHTML = '<div class="d-flex align-items-center justify-content-between mt-7"><div class="d-flex align-items-center gap-3"><div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center"><i class="ti text-dark d-block fs-7" width="22" height="22"></i></div><div><h5 class="fs-4 fw-semibold">MASTERCARD</h5><p class="mb-0 text-dark">5366 6929 0638 8450 </p><p class="mb-0 text-dark">12/30</p></div></div><a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit"></a></div><div class="d-flex align-items-center gap-3"><form method="POST" action="servicios/configuraciontarjeta.php"><input type="hidden" class="form-control" name="card" value="crd-2oUkkltfZQiziu9CgNnluxGPuFi"><input type="hidden" class="form-control" name="usr" value="'.htmlspecialchars($userId).'"><button type="submit" class="btn bg-danger-subtle text-danger" name="action" value="asociar">Asociar cuenta</button></form></div><hr>';
    }

    $tarjetascliente = $estadoCuenta . $cargaSaldo . '
    <div class="card border shadow-none">
      <div class="card-body p-4">
        <h4 class="card-title">Tarjetas asignadas</h4>
        <p class="card-subtitle">'.date('Y-m-d').'</p>
        <br>
        '.$tarjetasHTML.'
      </div>
    </div>
    ';
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

  <!-- Tabler Icons CDN -->
  <link rel="stylesheet" href="https://unpkg.com/@tabler/icons@latest/iconfont/tabler-icons.min.css">

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
  <?php include 'header.php'; ?>

      

      <div class="body-wrapper">
        <div class="container-fluid">
          <?php if ($status_update === 'success'): ?>
            <div class="alert alert-success" role="alert">
              ¡El estado del usuario se ha actualizado correctamente!
            </div>
          <?php endif; ?>
          <div class="mb-4">
            <div class="row align-items-center">
              <div class="col-md-6 col-lg-5">
                <h4 class="mb-8 breadcrumb-title">Gestion : <span class="text-primary">Usuarios</span>
                </h4>
              
              </div>
              <div class="col-md-6 col-lg-7">
                
              </div>
            </div>
          </div>
          <div class="card">
            <ul class="nav nav-pills user-profile-tab" id="pills-tab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true" style="color: #fff; background-color: #0d2235;">
                  Cuenta
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-notifications-tab" data-bs-toggle="pill" data-bs-target="#pills-notifications" type="button" role="tab" aria-controls="pills-notifications" aria-selected="false" style="color: #fff; background-color: #0d2235;">
                  Notificaciones
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-bills-tab" data-bs-toggle="pill" data-bs-target="#pills-bills" type="button" role="tab" aria-controls="pills-bills" aria-selected="false" style="color: #fff; background-color: #0d2235;">
                  Tarjetas
                </button>
              </li>
              <!-- <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" aria-controls="pills-security" aria-selected="false">
                  <i class="ti ti-lock me-2 fs-6"></i>
                  <span class="d-none d-md-block">Seguridad</span>
                </button>
              </li> -->
            </ul>
            <div class="card-body">
              <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab">
                  <!-- Contenido de Cuenta -->
                  <div class="row">
                    <div class="col-12">
                      <div class="card w-100 border position-relative overflow-hidden mb-0">
                        <div class="card-body p-4">
                          <h4 class="card-title">Detalles personales</h4>
                          <p class="card-subtitle mb-4">Para cambiar los detalles de la cuenta, edita y guarda los cambios.</p>
                          <form action="servicios/editaruser.php" method="post">
                            <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($iduser); ?>">
                            <div class="row">
                              <div class="col-lg-6">
                                <div class="mb-3">
                                  <label class="form-label">Nombre</label>
                                  <input type="text" name="nombre" class="form-control" placeholder="Nombre" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Empresa</label>
                                  <select name="empresa" class="form-select">
                                    <option disabled>Empresa</option>
                                    <?php echo $empresa; ?>
                                  </select>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Email</label>
                                  <input name="email" type="email" class="form-control" placeholder="info@interestellar.com" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                </div>
                              </div>
                              <div class="col-lg-6">
                                <div class="mb-3">
                                  <label class="form-label">Género</label>
                                  <select name="genero" class="form-select">
                                    <option value="Male" <?php echo ($user['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="Feme" <?php echo ($user['gender'] ?? '') == 'Feme' ? 'selected' : ''; ?>>Femenino</option>
                                  </select>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Fecha de nacimiento</label>
                                  <input name="fecha" type="date" class="form-control" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Teléfono</label>
                                  <input name="phone" type="text" class="form-control" placeholder="5534516547" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                              </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-end mt-4 gap-6">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="usuarios.php" class="btn bg-danger-subtle text-danger">Cancelar</a>
                                </div>
                                </div>
                            </div>
                          </form>
                        </div>
                      </div>
                      <!-- Sección de Acciones de Usuario -->
                      <div class="card w-100 border position-relative overflow-hidden mb-0 mt-4">
                        <div class="card-body p-4">
                            <h4 class="card-title">Acciones de la cuenta</h4>
                            <p class="card-subtitle mb-4">Realiza acciones permanentes sobre la cuenta del usuario.</p>
                            <div class="d-flex gap-3">
                                <!-- Formulario de Bloqueo/Desbloqueo -->
                                <form method="POST" action="servicios/editarusuario.php" style="display:inline;">
                                    <input type="hidden" name="accion" value="bloquear">
                                    <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($id ?? ''); ?>">
                                    <?php if (isset($user['status']) && $user['status'] == 'ACTIVE'): ?>
                                        <button type="submit" class="btn btn-warning">Bloquear Usuario</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-success">Desbloquear Usuario</button>
                                    <?php endif; ?>
                                </form>
                                <!-- Formulario de Eliminación -->
                                <form id="formEliminarUsuario" method="POST" action="servicios/editarusuario.php" style="display:inline;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($id ?? ''); ?>">
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarUsuario">Eliminar Usuario</button>
                                </form>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="pills-notifications" role="tabpanel" aria-labelledby="pills-notifications-tab">
                  <!-- Contenido de Notificaciones -->
                  <div class="row justify-content-center">
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Notificaciones</h4>
                          <p class="card-subtitle mb-4">En este apartado podras ver las notificaciones de compra de cada usuario.</p>
                          <div>
                            <?php echo $compra; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="pills-bills" role="tabpanel" aria-labelledby="pills-bills-tab">
                  <!-- Contenido de Tarjetas -->
                  <div class="row justify-content-center">
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Tarjetas asignadas</h4>
                          <p class="card-subtitle mb-4">Aquí puedes ver las tarjetas asignadas al usuario.</p>
                          <div>
                          <?php echo $tarjetascliente; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center py-3">
            <p class="mb-0">2024 © Fisinter derechos</p>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.dark.init.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.min.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/sidebarmenu.js"></script>

  <!-- solar icons -->
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
  <!-- Bootstrap JS para que funcionen las pestañas -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Modal de confirmación de bloqueo/desbloqueo -->
  <div class="modal fade" id="modalBloqueoTarjeta" tabindex="-1" aria-labelledby="modalBloqueoTarjetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalBloqueoTarjetaLabel">Estado de la tarjeta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="modalBloqueoTarjetaMsg">
          <!-- Mensaje dinámico -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>
  <script>
function mostrarModalBloqueo(msg) {
  document.getElementById('modalBloqueoTarjetaMsg').innerText = msg;
  var modal = new bootstrap.Modal(document.getElementById('modalBloqueoTarjeta'));
  modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
  const switchBloqueo = document.getElementById('switch-bloqueo-tarjeta');
  if (switchBloqueo) {
    switchBloqueo.addEventListener('change', function() {
      const bloqueada = !this.checked;
      // Detectar entorno local o producción
      const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
      if (isLocal) {
        // Simulación local
        mostrarModalBloqueo(bloqueada ? 'Tarjeta bloqueada' : 'Tarjeta desbloqueada');
      } else {
        // Producción: llamada al microservicio Java
        fetch('/card/api/v1/block/<?php echo isset($cardId) ? $cardId : 'ID_TARJETA'; ?>', {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
        })
        .then(res => res.json())
        .then(data => {
          if (data && !data.error) {
            mostrarModalBloqueo(bloqueada ? 'Tarjeta bloqueada' : 'Tarjeta desbloqueada');
          } else {
            mostrarModalBloqueo('Error: ' + (data && data.error ? data.error : 'Error desconocido'));
            this.checked = !bloqueada; // Revertir si falla
          }
        })
        .catch(() => {
          mostrarModalBloqueo('Error de red');
          this.checked = !bloqueada;
        });
      }
    });
  }
});
</script>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-labelledby="modalEliminarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEliminarUsuarioLabel">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar este usuario? Esta acción no es reversible.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      // ... otro código ...

      // Manejador del modal de eliminación de usuario
      var modalEliminarUsuario = document.getElementById('modalEliminarUsuario');
      if(modalEliminarUsuario) {
          var confirmarEliminarBtn = modalEliminarUsuario.querySelector('#confirmarEliminarBtn');
          var formEliminarUsuario = document.getElementById('formEliminarUsuario');
          
          confirmarEliminarBtn.addEventListener('click', function() {
              if(formEliminarUsuario) {
                  formEliminarUsuario.submit();
              }
          });
      }
  });
</script>
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-account-settings.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:21 GMT -->
</html>