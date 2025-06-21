<?php require_once __DIR__ . '/../functions.php'; ?>
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
// Conexión a la base de datos





$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Obtener datos del formulario
$idempresa        = $_POST['idempresa'] ?? null;
$nombre           = $_POST['nombre'] ?? '';
$rfc              = $_POST['rfc'] ?? '';
$alias            = $_POST['alias'] ?? '';
$pais             = $_POST['pais'] ?? '';
$estado           = $_POST['estado'] ?? '';
$codigo_postal    = $_POST['codigo'] ?? '';

// Validar ID
if (!$idempresa) {
    die("ID de empresa no proporcionado.");
}

// Actualizar datos
$stmt = $conn->prepare("
    UPDATE empresas SET 
        NOMBRE_EMPRESA = ?, 
        RFC = ?, 
        ALIAS = ?, 
        PAIS = ?, 
        ESTADO = ?, 
        CODIGO_POSTAL = ?
    WHERE ID_EMPRESA = ?
");

$stmt->bind_param(
    "ssssssi", // tipos: s = string, i = integer
    $nombre, 
    $rfc, 
    $alias, 
    $pais, 
    $estado, 
    $codigo_postal, 
    $idempresa
);

if ($stmt->execute()) {
    header("Location: ../empresas.php?status=success");
    exit;
} else {
    echo "Error al actualizar la empresa: " . $stmt->error;
}

$stmt->close();
$conn->close();
