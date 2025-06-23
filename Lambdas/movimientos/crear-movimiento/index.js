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
        const body = JSON.parse(event.body);
        const { idEmpresa, monto, tipo, idUsuario, comentario } = body;

        // Validaciones
        if (!idEmpresa || !monto || !tipo || !idUsuario) {
            return {
                statusCode: 400,
                headers: {
                    'Access-Control-Allow-Origin': '*',
                    'Access-Control-Allow-Credentials': true,
                },
                body: JSON.stringify({
                    error: 'Faltan campos requeridos'
                })
            };
        }

        // Crear conexión
        connection = await mysql.createConnection(dbConfig);

        // Iniciar transacción
        await connection.beginTransaction();

        // Obtener saldo actual de la empresa
        const [empresaRows] = await connection.execute(
            'SELECT SALDO FROM empresas WHERE ID_EMPRESA = ?',
            [idEmpresa]
        );

        if (empresaRows.length === 0) {
            await connection.rollback();
            return {
                statusCode: 404,
                headers: {
                    'Access-Control-Allow-Origin': '*',
                    'Access-Control-Allow-Credentials': true,
                },
                body: JSON.stringify({
                    error: 'Empresa no encontrada'
                })
            };
        }

        const saldoActual = parseFloat(empresaRows[0].SALDO || 0);
        const nuevoSaldo = tipo.toUpperCase() === 'ASIGNACION' 
            ? saldoActual + parseFloat(monto)
            : Math.max(0, saldoActual - parseFloat(monto));

        // Insertar movimiento
        const [movimientoResult] = await connection.execute(
            'INSERT INTO movimientos_saldo (ID_EMPRESA, MONTO, TIPO, ID_USUARIO, COMENTARIO) VALUES (?, ?, ?, ?, ?)',
            [idEmpresa, monto, tipo.toUpperCase(), idUsuario, comentario || '']
        );

        // Actualizar saldo de la empresa
        await connection.execute(
            'UPDATE empresas SET SALDO = ? WHERE ID_EMPRESA = ?',
            [nuevoSaldo, idEmpresa]
        );

        // Confirmar transacción
        await connection.commit();

        return {
            statusCode: 200,
            headers: {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Credentials': true,
            },
            body: JSON.stringify({
                success: true,
                data: {
                    id: movimientoResult.insertId,
                    idEmpresa,
                    monto,
                    tipo: tipo.toUpperCase(),
                    idUsuario,
                    comentario,
                    fecha: new Date().toISOString()
                }
            })
        };

    } catch (error) {
        if (connection) {
            await connection.rollback();
        }
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