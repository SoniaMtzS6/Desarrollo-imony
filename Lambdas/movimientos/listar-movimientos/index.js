const mysql = require('mysql2/promise');

// Configuración de la conexión a MySQL
const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: 'finister'
};

exports.handler = async (event) => {
    let connection;
    try {
        const { page = 1, size = 10 } = event.queryStringParameters || {};
        const limit = parseInt(size);
        const offset = (parseInt(page) - 1) * limit;

        // Crear conexión
        connection = await mysql.createConnection(dbConfig);

        // Obtener total de registros
        const [countResult] = await connection.execute(
            'SELECT COUNT(*) as total FROM movimientos_saldo'
        );
        const total = countResult[0].total;

        // Obtener movimientos con paginación
        const [movimientos] = await connection.execute(
            `SELECT m.*, e.NOMBRE_EMPRESA, u.name as nombre_usuario 
             FROM movimientos_saldo m 
             LEFT JOIN empresas e ON m.ID_EMPRESA = e.ID_EMPRESA 
             LEFT JOIN users u ON m.ID_USUARIO = u.ID 
             ORDER BY m.FECHA DESC 
             LIMIT ? OFFSET ?`,
            [limit, offset]
        );

        return {
            statusCode: 200,
            headers: {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Credentials': true,
            },
            body: JSON.stringify({
                data: movimientos,
                pagination: {
                    total,
                    page: parseInt(page),
                    size: limit,
                    totalPages: Math.ceil(total / limit)
                }
            })
        };

    } catch (error) {
        console.error('Error:', error);
        return {
            statusCode: 500,
            headers: {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Credentials': true,
            },
            body: JSON.stringify({
                error: 'Error interno del servidor'
            })
        };
    } finally {
        if (connection) {
            await connection.end();
        }
    }
}; 