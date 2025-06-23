<?php include 'functions.php'; ?>
<?php

session_start();

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

$id="";
$acc="";
$carid="";
$hidden="";
$token="";
$provider = "";
$pan = "";
$expiration = "";
$status = "";
$balance ="";
$status ="";
$currency ="";
if (isset($_GET['id'])) {
  // Obtiene el valor del parámetro 'id'
  $id = $_GET['id'];
  $acc = $_GET['acc'];
} else {
  echo "No se proporcionó un ID en la URL.";
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://vdn0w81bc0.execute-api.us-east-2.amazonaws.com/dev/card/api/v1/?filter[user_id]='.$id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
  ),
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;
$tarjetascliente="";

if ($response) {

    // Decodificar la respuesta JSON
    $data = json_decode($response, true); 

    
   
    if (isset($data['data'][0]['id'])) { 
        $cards = [];
        foreach ($data['data'] as $card) {
        //$cards[] = $card['id'];
        //$carid=$data['data'][0]['id'];
        $carid=$card['id'];
        echo $carid;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.pomelo.la/oauth/token',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "client_id": "cxq8Yq6wFgE53FxOzgHAGrCzRe5n4KgL",
            "client_secret": "hfGy54beNj08H6v7AnvTgo2g5zYGXZ9kyTdtpEHs5b_Vzgv1WDypnyFUz6273YO9",
            "audience": "https://auth-prod.pomelo.la",
            "grant_type": "client_credentials"
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        //  echo $response;
        
          if ($response) {
            $data = json_decode($response, true); 
            $token=$data['access_token'];
            //echo $token;
            if (isset($data['access_token'])) {
                $curl = curl_init();

                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.pomelo.la/cards/v1/'.$carid.'?extend=cvv%2Cexpiration_date%2Cpan%2Cpin',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer '.$token
                      ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    //echo $response;

                    if ($response) {
                        $data = json_decode($response, true); 
                        if (isset($data['data']['id'])) {
                            $provider = $data['data']['provider'];
                            $pan = chunk_split($data['data']['pan'], 4, ' ');
                            $expiration = $data['data']['expiration'];
                            $status = $data['data']['status'];
                            $start_date = $data['data']['start_date'];

                            $tarjetascliente.='
    <br>
    <div class="d-flex align-items-center justify-content-between mt-7">
      <div class="d-flex align-items-center gap-3">
        <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                <i class="ti  text-dark d-block fs-7" width="22" height="22"></i>
        </div>
        <div>
                                <h5 class="fs-4 fw-semibold">'.$provider.'</h5>
                                <p class="mb-0 text-dark">'.$pan.'</p>
                                <p class="mb-0 text-dark">'.$expiration.'</p>
        </div>
      </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                              <!-- <i class="ti ti-pencil-minus"></i> -->
                            </a>
</div>
';


                        
                        }
                    }
                
$curl = curl_init();

curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://api.pomelo.la/core/accounts/v1/'.$acc,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',
                      CURLOPT_HTTPHEADER => array(
                        'Content-type: application/json; charset=UTF-8',
                        'Authorization: Bearer '.$token
                      ),
                    ));
                    
$response = curl_exec($curl);
                    
curl_close($curl);
                    //echo $response;
if ($response) {
    $data = json_decode($response, true);
                        if (isset($data['data']['id'])) {
                            $balance = $data['data']['balance'];
                            $status = $data['data']['status'];
                            $currency = $data['data']['currency'];

                            //<button type="submit" class="btn bg-danger-subtle text-danger" name="action" value="eliminar">Eliminar tarjeta</button>

                            $tarjetascliente.='
                            
                            <div class="d-flex align-items-center gap-3">
                              <form method="POST" action="servicios/configuraciontarjeta.php" > 
                                  <input type="hidden" class="form-control" name="card" value="'.$carid.'">
                                  <input type="hidden" class="form-control" name="usr" value="'.$id.'">
                                  <input type="hidden" class="form-control" name="acc" value="'.$acc.'">
                                  <button type="submit" class="btn bg-danger-subtle text-danger" name="action" value="asociar">Asociar cuenta</button>
                              <form>
                            </div>';
                        
                        }
                    }


            }
          }
        }
    }else{
        $hidden="hidden";
    }
}

  
$curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://h3epgx14k6.execute-api.us-east-2.amazonaws.com/dev/activities/findByAcc',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_POSTFIELDS =>'{
      "acc":"'.$acc.'"
  }',
    CURLOPT_HTTPHEADER => array(
      'Content-type: application/json; charset=UTF-8'
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  $compra="";
  if ($response) {
    $data = json_decode($response, true); 

      foreach ($data['Data'] as $entry) {
        $totalAmount= $entry['totalAmount'] ?? '';
        $createdAt= $entry['createdAt'] ?? '';
        $origin= $entry['origin'] ?? '';
        $processType= $entry['processType'] ?? '';
        $merchantName= $entry['merchantName'] ?? 'Sin nombre de comercio';

      $compra.='<div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
              <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
              </div>
              <div>
                <h5 class="fs-4 fw-semibold">'.$merchantName.' '.$processType.' '.$origin.'</h5>
                <p class="mb-0">Realizada: '.$createdAt.'</p>
              </div>
            </div>
            <div class="form-check form-switch mb-0">
              <p class="mb-0">'.$totalAmount.'</p>
            </div>
          </div>';

      }  
    
    
    }


    
    
    
    

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener ID de usuario
    $iduser = $_GET['iduser'] ?? null;
$user = null;

if ($iduser) {
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
 



    // while ($row = $resEmp->fetch_assoc()) {
    //     $selected = ($user && $user['id_empresa'] == $row['ID_EMPRESA']) ? 'selected' : '';
    //     $empresa .= '<option value="' . $row['ID_EMPRESA'] . '" ' . $selected . '>' . $row['NOMBRE_EMPRESA'] . '</option>';
    // }

    $conn->close();


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
                <button class="nav-link position-relative rounded-0 active d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true">
                  <i class="ti ti-user-circle me-2 fs-6"></i>
                  <span class="d-none d-md-block">Cuenta</span>
                </button>
              </li>
              <li class="nav-item" role="presentation" <?php echo $hidden?>>
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-notifications-tab" data-bs-toggle="pill" data-bs-target="#pills-notifications" type="button" role="tab" aria-controls="pills-notifications" aria-selected="false">
                  <i class="ti ti-bell me-2 fs-6"></i>
                  <span class="d-none d-md-block">Notificaciones</span>
                </button>
              </li>
              <li class="nav-item" role="presentation" <?php echo $hidden?>>
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-bills-tab" data-bs-toggle="pill" data-bs-target="#pills-bills" type="button" role="tab" aria-controls="pills-bills" aria-selected="false">
                  <i class="ti ti-article me-2 fs-6"></i>
                  <span class="d-none d-md-block">Tarjetas</span>
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
                <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">
                  <div class="row">
                   
                    
                    <div class="col-12">
                      <div class="card w-100 border position-relative overflow-hidden mb-0">
                        <div class="card-body p-4">
                          <h4 class="card-title">Detalles personales</h4>
                          <p class="card-subtitle mb-4">Para cambiar los detalles de la cuenta, edita y guarda los cambios.</p>
                          <form id="editUserForm" action="servicios/editaruser.php" method="post">
                            <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($iduser); ?>">
                            <input type="hidden" name="action" id="formAction" value="">

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
                            </div> <!-- cierre correcto de row -->

                            <div class="col-12">
                              <div class="d-flex justify-content-between align-items-center mt-4">
                                  <div class="d-flex gap-3">
                                      <?php if (isset($user['status']) && $user['status'] === 'BLOCKED'): ?>
                                          <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmDesbloquearModal">Desbloquear Usuario</button>
                                      <?php else: ?>
                                          <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#confirmBloquearModal">Bloquear Usuario</button>
                                      <?php endif; ?>
                                      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmEliminarModal">Eliminar Usuario</button>
                                </div>
                                  <div>
                                      <button type="submit" name="action" value="guardar" class="btn btn-dark">Guardar</button>
                                      <a href="usuarios.php" class="btn btn-light-danger ms-2">Cancelar</a>
                                </div>
                            </div>
                        </div>
                                </form>

                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <div class="tab-pane fade" id="pills-notifications" role="tabpanel" aria-labelledby="pills-notifications-tab" tabindex="0">
                  <div class="row justify-content-center">
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Notificaciones</h4>
                          <p class="card-subtitle mb-4">
                            En este apartado podras ver las notificaciones de compra de cada usuario.
                          </p>
                         
                          <div>
                            
                            <?php echo $compra; ?>
                            
                          </div>
                        </div>
                      </div>
                    </div>
               
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Saldo</h4>
                          <p class="card-subtitle">Consulta el saldo asociado</p>
                          <div class="d-flex align-items-center justify-content-between mt-7">
                            <div class="d-flex align-items-center gap-3">
                              <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
                  </div>
                              <div>
                                <p class="mb-0">Pesos Mexicanos <?php echo $currency; ?></p>
                                <h5 class="fs-4 fw-semibold">$ <?php echo $balance; ?></h5>
                </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download">
                              <!-- <i class="ti ti-download"></i> -->
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    
                  </div>
                </div>
                <div class="tab-pane fade" id="pills-bills" role="tabpanel" aria-labelledby="pills-bills-tab" tabindex="0">
                  <div class="row justify-content-center">
                    
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Estado de la cuenta : <span class="text-success"><?php echo $status; ?></span>
                          </h4>
                          <p class="card-subtitle">Puedes consultar el estado de la tarjeta.</p>
                          <div class="d-flex align-items-center justify-content-between mt-7 mb-3">
                            <div class="d-flex align-items-center gap-3">
                              <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
                              </div>
                          <div>
                                <p class="mb-0">Monto asignado</p>
                                <h5 class="fs-4 fw-semibold">$ <?php echo number_format($balance); ?></h5>
                          </div>
                        </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add">
                              <!-- <i class="ti ti-circle-plus"></i> -->
                            </a>
                      </div>
                          <div class="d-flex align-items-center gap-3">
                            <form action="servicios/bloquear_tarjeta.php" method="POST" class="d-flex align-items-center gap-3">
                                <input type="hidden" name="card_id" value="<?php echo htmlspecialchars($carid); ?>">
                                <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($iduser); ?>">
                                <input type="hidden" name="action" value="<?php echo ($status === 'ACTIVE') ? 'desactivar' : 'activar'; ?>">
                                <div class="form-check form-switch">
                                  <input class="form-check-input" type="checkbox" role="switch" id="cardStatusSwitch" onchange="this.form.submit()" <?php echo ($status === 'ACTIVE') ? 'checked' : ''; ?>>
                                  <label class="form-check-label" for="cardStatusSwitch"><?php echo ($status === 'ACTIVE') ? 'Activa' : 'Inactiva'; ?></label>
                    </div>
                            </form>
                            <form action="servicios/bloquear_tarjeta.php" method="POST">
                                <input type="hidden" name="card_id" value="<?php echo htmlspecialchars($carid); ?>">
                                <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($iduser); ?>">
                                <button type="submit" name="action" value="bloquear" class="btn btn-warning">Bloquear Tarjeta</button>
                            </form>
                  </div>
                </div>
                </div>
                    </div>
                    <div class="col-lg-9">
                        <form method="post" action="servicios/procesarmonto.php">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                            <h4 class="card-title">Carga de saldo</h4>
                            <p class="card-subtitle mb-4">
                              Introduce el saldo a la tarjeta o retiralo.
                            </p>
                           
                          <div>
                                <div class="d-flex align-items-center justify-content-between mt-7 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                      <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                        <i class="ti text-dark d-block fs-7" width="22" height="22"></i>
                                      </div>
                                      <div>
                                        <p class="mb-0">Monto</p>
                                        <input type="text" class="form-control" name="monto" placeholder="$" placeholder="$0.0" required="">
                                        <input type="hidden" class="form-control" name="usr" value="<?php echo $id;?>">
                                        <input type="hidden" class="form-control" name="acc" value="<?php echo $acc;?>">
                                      </div>
                                    </div>
                                    <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add">
                                      <!-- <i class="ti ti-circle-plus"></i> -->
                                    </a>
                                  </div>
                                  <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="btn btn-primary" name="action" value="asignar">Asignar monto</button>
                                    <button type="submit" class="btn btn-danger" name="action" value="retirar">Retirar monto</button>
                                  </div>
                              
                              
                            </div>
                          </div>
                        </div>
                        </form>
                      </div>
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Tarjetas asignadas</h4>
                          <p class="card-subtitle"><?php echo $start_date; ?></p>
                         
                          <?php echo $tarjetascliente; ?>
                          </div>
                        </div>
                      </div>
                    <div class="col-12">
                      <div class="d-flex align-items-center justify-content-end gap-6">
                       
                    </div>
                  </div>
                </div>
              </div>
                <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab" tabindex="0">
                  <div class="row">
                    <div class="col-lg-8">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title mb-3">Two-factor Authentication</h4>
                          <div class="d-flex align-items-center justify-content-between pb-7">
                            <p class="card-subtitle mb-0">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Corporis sapiente
                              sunt earum officiis laboriosam ut.</p>
                            <button class="btn btn-primary">Enable</button>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3 border-top">
                            <div>
                              <h5 class="fs-4 fw-semibold mb-0">Authentication App</h5>
                              <p class="mb-0">Google auth app</p>
                            </div>
                            <button class="btn bg-primary-subtle text-primary">Setup</button>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3 border-top">
                            <div>
                              <h5 class="fs-4 fw-semibold mb-0">Another e-mail</h5>
                              <p class="mb-0">E-mail to send verification link</p>
                            </div>
                            <button class="btn bg-primary-subtle text-primary">Setup</button>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3 border-top">
                            <div>
                              <h5 class="fs-4 fw-semibold mb-0">SMS Recovery</h5>
                              <p class="mb-0">Your phone number or something</p>
                            </div>
                            <button class="btn bg-primary-subtle text-primary">Setup</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="card">
                        <div class="card-body p-4">
                          <div class="text-bg-light rounded-1 p-6 d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="ti ti-device-laptop text-primary d-block fs-7" width="22" height="22"></i>
                          </div>
                          <h4 class="card-title mb-0">Devices</h4>
                          <p class="mb-3">Lorem ipsum dolor sit amet consectetur adipisicing elit Rem.</p>
                          <button class="btn btn-primary mb-4">Sign out from all devices</button>
                          <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                              <i class="ti ti-device-mobile text-dark d-block fs-7" width="26" height="26"></i>
                              <div>
                                <h5 class="fs-4 fw-semibold mb-0">iPhone 14</h5>
                                <p class="mb-0">London UK, Oct 23 at 1:15 AM</p>
                              </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)">
                              <i class="ti ti-dots-vertical"></i>
                            </a>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center gap-3">
                              <i class="ti ti-device-laptop text-dark d-block fs-7" width="26" height="26"></i>
                              <div>
                                <h5 class="fs-4 fw-semibold mb-0">Macbook Air</h5>
                                <p class="mb-0">Gujarat India, Oct 24 at 3:15 AM</p>
                              </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)">
                              <i class="ti ti-dots-vertical"></i>
                            </a>
                          </div>
                          <button class="btn bg-primary-subtle text-primary w-100 py-1">Need Help ?</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="d-flex align-items-center justify-content-end gap-6">
                        <button class="btn btn-primary">Save</button>
                        <button class="btn bg-danger-subtle text-danger">Cancel</button>
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
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.dark.init.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.min.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/sidebarmenu.js"></script>

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
          ¿Estás seguro de que quieres bloquear a este usuario?
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
          ¿Estás seguro de que quieres desbloquear a este usuario?
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
          <p>¿Estás seguro de que quieres eliminar a este usuario?</p>
          <!-- <p class="text-info">El estado del usuario se cambiará a "Eliminado" y podrá ser reactivado en el futuro.</p> -->
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
      const form = document.getElementById('editUserForm');
      const actionInput = document.getElementById('formAction');

      const confirmBloquearBtn = document.getElementById('confirmBloquearBtn');
      if (confirmBloquearBtn) {
        confirmBloquearBtn.addEventListener('click', function () {
          actionInput.value = 'bloquear';
          form.submit();
        });
      }

      const confirmDesbloquearBtn = document.getElementById('confirmDesbloquearBtn');
      if (confirmDesbloquearBtn) {
        confirmDesbloquearBtn.addEventListener('click', function () {
          actionInput.value = 'desbloquear';
          form.submit();
        });
      }

      const confirmEliminarBtn = document.getElementById('confirmEliminarBtn');
      if (confirmEliminarBtn) {
        confirmEliminarBtn.addEventListener('click', function () {
          actionInput.value = 'eliminar';
          form.submit();
          });
      }
  });
</script>

  <!-- solar icons -->
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-account-settings.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:21 GMT -->
</html>