<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../functions.php';

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Manejar las diferentes rutas
if ($method === 'POST') {
    // Obtener el contenido JSON del body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        exit;
    }

    // Validar campos requeridos
    $requiredFields = ['nombre', 'rfc', 'montoMaximo', 'numeroTarjetas'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "El campo $field es requerido"]);
            exit;
        }
    }

    try {
        $conn = getDbConnection();
        
        $nombre = $data['nombre'];
        $rfc = $data['rfc'];
        $alias = $data['alias'] ?? '';
        $pais = $data['pais'] ?? '';
        $estado = $data['estado'] ?? '';
        $codigoPostal = $data['codigoPostal'] ?? '';
        $montoMaximo = $data['montoMaximo'];
        $numeroTarjetas = $data['numeroTarjetas'];
        $estatus = $data['estatus'] ?? 'ACTIVE';

        $stmt = $conn->prepare("INSERT INTO empresas (NOMBRE_EMPRESA, RFC, ALIAS, PAIS, ESTADO, CODIGO_POSTAL, MONTO_MAXIMO, NUMERO_TARJETAS, ESTATUS) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssdis", 
            $nombre,
            $rfc,
            $alias,
            $pais,
            $estado,
            $codigoPostal,
            $montoMaximo,
            $numeroTarjetas,
            $estatus
        );
        
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Empresa creada exitosamente',
                'data' => ['idEmpresa' => $id]
            ]);
        } else {
            throw new Exception("Error al guardar en la base de datos");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Error al crear la empresa',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
} 