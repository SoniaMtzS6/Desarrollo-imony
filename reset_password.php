<?php
$token = $_GET['token'] ?? '';
if (!$token) {
    die("Token inválido.");
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/authentication-forgot-password.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:30 GMT -->
<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>SeoDash Bootstrap Admin</title>
</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper" class="auth-customizer-none p-0">
    <div class="position-relative overflow-hidden min-vh-100 w-100">
      <div class="position-relative">
        <div class="row gx-0">
          <div class="col-lg-6 col-xl-5 col-xxl-4 d-lg-block d-none">
            <div class="auth-left-bg min-vh-100 bg-body row justify-content-center align-items-center p-5">
              <div class="col-lg-8 bg-icon">
                <div class="text-center">
                <img src="img/logo.png" class="circle-bottom" style="width: 250px;" alt="Logo-white" />
                  
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-7 col-xxl-8 position-relative overflow-hidden bg-white">
            <div class="pt-7 pb-9 px-7 overflow-auto vh-100 bg-body row justify-content-center align-items-center">
              <div class="auth-form mt-0">
                <div class="mb-5">
                  <h2 class="mb-9">Cambia tu password</h2>
                  <p class="mb-0">
                    Por favor introduce una contraseña robusta.
                  </p>
                </div>
                <form action="servicios/update_password.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="mb-3">
                        <label>Nueva contraseña</label>
                        <input type="password" name="nueva_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="confirmar_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <!-- Import Js Files -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.dark.init.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.min.js"></script> -->

  <!-- solar icons -->
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/authentication-forgot-password.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:30 GMT -->
</html>





