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
                  <h2 class="mb-9">Olvidaste tu password?</h2>
                  <p class="mb-0">
                    No te preocupes, por favor introduce tu email, para hacerte llegar el cambio de contraseña.
                  </p>
                </div>
                <form method="POST" action="servicios/recover_password.php">
                    <div class="mb-3">
                      <label for="exampleInputEmail1" class="form-label">Email</label>
                      <input type="email" class="form-control" id="exampleInputEmail1" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-8 mb-3">Enviar</button>
                    <a href="index.php" class="btn bg-primary-subtle text-primary w-100 py-8">Regresar al login</a>
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