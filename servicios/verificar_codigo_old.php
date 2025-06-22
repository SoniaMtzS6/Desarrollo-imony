<?php
session_start();
require_once __DIR__ . '/../functions.php';

// Validar que el usuario está en sesión y que el token de 2FA fue generado
if (!isset($_SESSION["usuario"]) || !isset($_SESSION['token'])) {
    header("Location: ../index.php"); // Redirigir si no hay sesión o token
    exit;
}

// Verificar que venga por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Si no es POST, redirigir de vuelta a la página del formulario
    header("Location: ../authentication-two-steps.php");
    exit;
}

// Obtener el código ingresado por el usuario
$codigo_ingresado = $_POST['digit1'] . $_POST['digit2'] . $_POST['digit3'] .
                    $_POST['digit4'] . $_POST['digit5'] . $_POST['digit6'];

// Limpiar el código para evitar espacios extra
$codigo_ingresado = trim($codigo_ingresado);

// Obtener el token guardado en la sesión
$token_guardado = $_SESSION['token'];

// Comparar el código ingresado con el token de la sesión
if ($codigo_ingresado === $token_guardado) {
    // --- Código correcto ---
    
    // 1. Marcar el segundo factor como verificado en la sesión
    $_SESSION["usuario"]["doblefactor"] = "1";
    
    // 2. Limpiar el token de la sesión para que no pueda ser reutilizado
    unset($_SESSION['token']); 

    // 3. Redirigir al panel principal (dashboard)
    header("Location: ../usuarios.php");
    exit;

} else {
    // --- Código incorrecto ---

    // 1. Guardar un mensaje de error en la sesión para mostrarlo al usuario
    $_SESSION['error_2fa'] = "El código de verificación es incorrecto. Por favor, inténtalo de nuevo.";
    
    // 2. Redirigir de vuelta a la página de ingreso del código
    header("Location: ../authentication-two-steps.php");
    exit;
}
?>
